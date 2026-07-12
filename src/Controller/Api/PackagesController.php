<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Model\Enum\InvoiceReviewStatus;
use App\Model\Enum\PackageStatus;
use App\Model\Enum\UserRole;
use App\Service\InvoiceUploadValidator;
use Cake\Datasource\EntityInterface;
use Cake\I18n\DateTime;
use Cake\ORM\Table;
use InvalidArgumentException;
use Josegonzalez\Upload\File\Writer\DefaultWriter;
use Psr\Http\Message\UploadedFileInterface;

/**
 * API Packages Controller.
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
     * Package list.
     *
     * @return void
     */
    public function index(): void
    {
        $this->Authorization->authorize($this->Packages);
        $query = $this->Authorization->applyScope(
            $this->Packages->find('search', search: $this->request->getQueryParams())
                ->contain(['Clients', 'Invoices'])
                ->orderBy(['Packages.received_at' => 'DESC']),
        );

        $packages = [];
        foreach ($this->paginate($query) as $package) {
            $packages[] = $this->packageResource($package);
        }

        $this->json(['packages' => $packages]);
    }

    /**
     * Package detail.
     *
     * @param string $id Package id
     * @return void
     */
    public function view(string $id): void
    {
        $package = $this->Packages->get($id, contain: ['Clients', 'Invoices', 'PackagesShipRequests.ShipRequests']);
        $this->Authorization->authorize($package);

        $this->json(['package' => $this->packageResource($package)]);
    }

    /**
     * Upload or replace invoice file for a package.
     *
     * @param string $id Package id
     * @return void
     */
    public function uploadInvoice(string $id): void
    {
        $this->request->allowMethod(['post', 'put']);

        $package = $this->Packages->get($id, contain: ['Invoices']);
        $this->Authorization->authorize($package);

        $file = $this->request->getData('invoice_file');
        if ($file === null || $file->getError() !== UPLOAD_ERR_OK) {
            $this->jsonError(__('Please upload a valid invoice file.'));

            return;
        }

        if ($file->getSize() > 5 * 1024 * 1024) {
            $this->jsonError(__('The invoice file must be 5 MB or smaller.'));

            return;
        }

        $fileInfo = (new InvoiceUploadValidator())->inspect($file);
        if ($fileInfo === null) {
            $this->jsonError(__('The invoice must be a PDF, JPG, or PNG file.'));

            return;
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
            $this->jsonError(__('The invoice could not be uploaded. Please try again.'));

            return;
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

        if (!$uploaded) {
            $storedPath = RESOURCES . 'pdf' . DS . 'invoices' . DS . $filename;
            if (is_file($storedPath)) {
                unlink($storedPath);
            }

            $this->jsonError(__('The invoice could not be uploaded. Please try again.'));

            return;
        }

        $this->deleteReplacedInvoiceFile($previousFilePath, $relativePath);

        $invoice = $invoices->get($invoice->id, contain: ['Packages']);
        $this->json(['invoice' => $this->invoiceResource($invoice)], 201);
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
     * Mark package ready for pickup.
     *
     * @param string $id Package id
     * @return void
     */
    public function readyForPickup(string $id): void
    {
        $this->request->allowMethod(['post']);
        $this->transitionPackage($id, PackageStatus::ReadyForPickup, __('Package arrived at destination and is ready for pickup.'));
    }

    /**
     * Mark package delivered.
     *
     * @param string $id Package id
     * @return void
     */
    public function deliver(string $id): void
    {
        $this->request->allowMethod(['post']);
        $this->transitionPackage($id, PackageStatus::Delivered, __('Package delivered to the client.'));
    }

    /**
     * Apply an admin package status transition.
     *
     * @param string $id Package id
     * @param \App\Model\Enum\PackageStatus $status New status
     * @param string $note Audit note
     * @return void
     */
    private function transitionPackage(string $id, PackageStatus $status, string $note): void
    {
        $package = $this->Packages->get($id);
        $this->Authorization->authorize($package);

        try {
            $updated = $this->Packages->transitionStatus($package, $status, $this->currentUserId(), UserRole::Admin, $note);
        } catch (InvalidArgumentException $exception) {
            $this->jsonError($exception->getMessage());

            return;
        }

        if (!$updated) {
            $this->jsonError(__('The package status could not be updated.'));

            return;
        }

        $package = $this->Packages->get($id, contain: ['Clients', 'Invoices']);
        $this->json(['package' => $this->packageResource($package)]);
    }
}
