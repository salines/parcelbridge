<?php
declare(strict_types=1);

namespace App\Controller\Client;

use App\Controller\AppController;
use App\Model\Enum\InvoiceReviewStatus;
use App\Model\Enum\PackageStatus;
use App\Model\Enum\UserRole;
use App\Service\InvoiceUploadValidator;
use Cake\Datasource\EntityInterface;
use Cake\I18n\DateTime;
use Cake\ORM\Table;
use Josegonzalez\Upload\File\Writer\DefaultWriter;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Packages Controller
 *
 * @property \App\Model\Table\PackagesTable $Packages
 * @property \Search\Controller\Component\SearchComponent $Search
 */
class PackagesController extends AppController
{
    /**
     * Initialization hook method.
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Search.Search', [
            'actions' => ['index'],
            'modelClass' => 'Packages',
            'strictMode' => true,
        ]);
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $this->Authorization->authorize($this->Packages);
        $query = $this->Authorization->applyScope(
            $this->Packages->find('search', search: $this->request->getQueryParams())
                ->contain(['Clients', 'CreatedByUsers']),
        );
        $search = trim((string)$this->request->getQuery('q', ''));
        $packages = $this->paginate($query);

        $this->set(compact('packages', 'search'));
    }

    /**
     * View method
     *
     * @param string $id Package id.
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function view(string $id)
    {
        $package = $this->Packages->get($id, contain: ['Clients', 'CreatedByUsers', 'PackagesShipRequests.ShipRequests', 'Invoices', 'PackageStatusHistories']);
        $this->Authorization->authorize($package);
        $this->set(compact('package'));
    }

    /**
     * Render a dynamic package PDF document.
     *
     * @param string $id Package id.
     * @return void
     */
    public function document(string $id): void
    {
        $package = $this->Packages->get($id, contain: ['Clients', 'CreatedByUsers', 'PackagesShipRequests.ShipRequests', 'Invoices', 'PackageStatusHistories']);
        $this->Authorization->authorize($package, 'view');

        $this->viewBuilder()
            ->setClassName('CakePdf.Pdf')
            ->setOption('pdfConfig', [
                'download' => true,
                'filename' => sprintf('package-%s.pdf', $package->tracking_number),
            ]);
        $this->set(compact('package'));
    }

    /**
     * Export scoped package list as CSV.
     *
     * @return void
     */
    public function exportCsv(): void
    {
        $this->Authorization->authorize($this->Packages, 'index');
        $query = $this->Authorization->applyScope(
            $this->Packages->find('search', search: $this->request->getQueryParams())
                ->contain(['Clients', 'CreatedByUsers'])
                ->orderBy(['Packages.received_at' => 'DESC']),
        );

        $packages = [];
        foreach ($query as $package) {
            $packages[] = $this->packageCsvRow($package);
        }

        $this->set(compact('packages'));
        $this->setResponse($this->getResponse()->withDownload('my-packages.csv'));
        $this->viewBuilder()
            ->setClassName('CsvView.Csv')
            ->setOptions([
                'serialize' => 'packages',
                'header' => $this->packageCsvHeader(),
            ]);
    }

    /**
     * Upload or replace an invoice for a package.
     *
     * @param string $id Package id.
     * @return \Cake\Http\Response|null|void
     */
    public function uploadInvoice(string $id)
    {
        $package = $this->Packages->get($id, contain: ['Invoices']);
        $this->Authorization->authorize($package);

        if ($this->request->is(['post', 'put'])) {
            $file = $this->request->getData('invoice_file');
            if ($file === null || $file->getError() !== UPLOAD_ERR_OK) {
                $this->Flash->error(__('Please upload a valid invoice file.'));

                return $this->redirect(['action' => 'uploadInvoice', $package->id]);
            }

            if ($file->getSize() > 5 * 1024 * 1024) {
                $this->Flash->error(__('The invoice file must be 5 MB or smaller.'));

                return $this->redirect(['action' => 'uploadInvoice', $package->id]);
            }

            $fileInfo = (new InvoiceUploadValidator())->inspect($file);
            if ($fileInfo === null) {
                $this->Flash->error(__('The invoice must be a PDF, JPG, or PNG file.'));

                return $this->redirect(['action' => 'uploadInvoice', $package->id]);
            }

            $originalFilename = (string)$file->getClientFilename();
            $filename = sprintf('package-%d-%s.%s', $package->id, bin2hex(random_bytes(8)), $fileInfo['extension']);
            $relativePath = 'pdf/invoices/' . $filename;

            $invoices = $this->fetchTable('Invoices');
            $invoice = $package->invoice ?? $invoices->newEmptyEntity();
            $previousFilePath = $invoice->file_path;
            $invoice = $invoices->patchEntity($invoice, [
                'package_id' => $package->id,
                'uploaded_by_user_id' => $this->currentUserId(),
                'reviewed_by_user_id' => null,
                'file_path' => $relativePath,
                'original_filename' => $originalFilename,
                'mime_type' => $fileInfo['mime_type'],
                'file_size' => $file->getSize(),
                'review_status' => InvoiceReviewStatus::Pending->value,
                'admin_notes' => null,
                'uploaded_at' => DateTime::now(),
                'reviewed_at' => null,
            ]);

            if (!$this->storeInvoiceFile($invoices, $invoice, $file, $filename)) {
                $this->Flash->error(__('The invoice could not be uploaded. Please try again.'));

                return $this->redirect(['action' => 'uploadInvoice', $package->id]);
            }

            $uploaded = $invoices->getConnection()->transactional(function () use ($invoices, $invoice, $package): bool {
                return (bool)(
                    $invoices->save($invoice) && $this->Packages->transitionStatus(
                        $package,
                        PackageStatus::PendingInvoiceReview,
                        $this->currentUserId(),
                        UserRole::Client,
                        __('Client uploaded invoice.'),
                    )
                );
            });

            if ($uploaded) {
                $this->deleteReplacedInvoiceFile($previousFilePath, $relativePath);
                $this->Flash->success(__('The invoice has been uploaded.'));

                return $this->redirect(['action' => 'view', $package->id]);
            }

            $storedPath = RESOURCES . 'pdf' . DS . 'invoices' . DS . $filename;
            if (is_file($storedPath)) {
                unlink($storedPath);
            }

            $this->Flash->error(__('The invoice could not be uploaded. Please try again.'));
        }

        $this->set(compact('package'));
    }

    /**
     * Store an invoice upload in private resources storage.
     *
     * @param \Cake\ORM\Table $invoices Invoices table
     * @param \Cake\Datasource\EntityInterface $invoice Invoice entity
     * @param \Psr\Http\Message\UploadedFileInterface $file Uploaded invoice
     * @param string $filename Private storage filename
     * @return bool
     */
    private function storeInvoiceFile(
        Table $invoices,
        EntityInterface $invoice,
        UploadedFileInterface $file,
        string $filename,
    ): bool {
        $source = $file->getStream()->getMetadata('uri');
        if (!is_string($source) || $source === '') {
            return false;
        }

        $writer = new DefaultWriter($invoices, $invoice, $file, 'invoice_file', [
            'filesystem' => [
                'root' => RESOURCES . 'pdf' . DS,
            ],
        ]);

        return !in_array(false, $writer->write([$source => 'invoices/' . $filename]), true);
    }

    /**
     * Remove the previous generated invoice file after a successful replacement.
     *
     * @param string|null $previousPath Previous relative file path
     * @param string $currentPath Current relative file path
     * @return void
     */
    private function deleteReplacedInvoiceFile(?string $previousPath, string $currentPath): void
    {
        if ($previousPath === null || $previousPath === $currentPath) {
            return;
        }
        if (!preg_match('#^pdf/invoices/package-\d+-[a-f0-9]{16}\.(?:pdf|jpg|png)$#', $previousPath)) {
            return;
        }

        $path = RESOURCES . str_replace('/', DS, $previousPath);
        if (is_file($path)) {
            unlink($path);
        }
    }

    /**
     * @return array<string>
     */
    private function packageCsvHeader(): array
    {
        return [
            'Tracking Number',
            'Contents',
            'Status',
            'Weight',
            'Weight Unit',
            'Received At',
            'Shipped At',
            'Ready For Pickup At',
            'Delivered At',
        ];
    }

    /**
     * @param \Cake\Datasource\EntityInterface $package Package entity
     * @return array<string, mixed>
     */
    private function packageCsvRow(EntityInterface $package): array
    {
        return [
            'tracking_number' => $package->get('tracking_number'),
            'contents_description' => $package->get('contents_description'),
            'status' => $package->get('status')?->label(),
            'weight' => $package->get('weight'),
            'weight_unit' => $package->get('weight_unit')?->label(),
            'received_at' => $package->get('received_at'),
            'shipped_at' => $package->get('shipped_at'),
            'ready_for_pickup_at' => $package->get('ready_for_pickup_at'),
            'delivered_at' => $package->get('delivered_at'),
        ];
    }
}
