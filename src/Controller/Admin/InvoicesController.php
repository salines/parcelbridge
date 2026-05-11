<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Model\Entity\Invoice;
use App\Model\Enum\InvoiceReviewStatus;
use App\Model\Enum\PackageStatus;
use App\Model\Enum\UserRole;
use Cake\I18n\DateTime;

/**
 * Invoices Controller
 *
 * @property \App\Model\Table\InvoicesTable $Invoices
 * @property \Search\Controller\Component\SearchComponent $Search
 */
class InvoicesController extends AppController
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
            'modelClass' => 'Invoices',
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
        $this->Authorization->authorize($this->Invoices);
        $query = $this->Invoices->find('search', search: $this->request->getQueryParams())
            ->contain(['Packages', 'UploadedByUsers', 'ReviewedByUsers'])
            ->where(['Invoices.review_status' => InvoiceReviewStatus::Pending->value])
            ->orderBy(['Invoices.uploaded_at' => 'ASC']);
        $search = trim((string)$this->request->getQuery('q', ''));
        $invoices = $this->paginate($query);

        $this->set(compact('invoices', 'search'));
    }

    /**
     * View method
     *
     * @param string $id Invoice id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(string $id)
    {
        $invoice = $this->Invoices->get($id, contain: ['Packages', 'UploadedByUsers', 'ReviewedByUsers']);
        $this->Authorization->authorize($invoice);
        $this->set(compact('invoice'));
    }

    /**
     * Display an uploaded invoice file.
     *
     * @param string $id Invoice id.
     * @return \Cake\Http\Response|null
     */
    public function viewFile(string $id)
    {
        $invoice = $this->Invoices->get($id, contain: ['Packages']);
        $this->Authorization->authorize($invoice, 'view');

        return $this->invoiceFileResponse($invoice, false);
    }

    /**
     * Download an uploaded invoice file.
     *
     * @param string $id Invoice id.
     * @return \Cake\Http\Response|null
     */
    public function downloadFile(string $id)
    {
        $invoice = $this->Invoices->get($id, contain: ['Packages']);
        $this->Authorization->authorize($invoice, 'view');

        return $this->invoiceFileResponse($invoice, true);
    }

    /**
     * Approve an invoice and move the package to invoice approved.
     *
     * @param string $id Invoice id.
     * @return \Cake\Http\Response|null
     */
    public function approve(string $id)
    {
        $this->request->allowMethod(['post']);

        $invoice = $this->Invoices->get($id, contain: ['Packages']);
        $this->Authorization->authorize($invoice);
        if ($invoice->package->status !== PackageStatus::PendingInvoiceReview) {
            $this->Flash->error(__('Only packages pending invoice review can be approved.'));

            return $this->redirect(['action' => 'view', $id]);
        }

        $invoice = $this->Invoices->patchEntity($invoice, [
            'review_status' => InvoiceReviewStatus::Approved->value,
            'reviewed_by_user_id' => $this->currentUserId(),
            'reviewed_at' => DateTime::now(),
            'admin_notes' => null,
        ]);

        $approved = $this->Invoices->getConnection()->transactional(function () use ($invoice): bool {
            return (bool)(
                $this->Invoices->save($invoice) && $this->Invoices->Packages->transitionStatus(
                    $invoice->package,
                    PackageStatus::InvoiceApproved,
                    $this->currentUserId(),
                    UserRole::Admin,
                    __('Invoice approved.'),
                )
            );
        });

        if ($approved) {
            $this->Flash->success(__('The invoice has been approved.'));

            return $this->redirect(['action' => 'index']);
        }

        $this->Flash->error(__('The invoice could not be approved. Please try again.'));

        return $this->redirect(['action' => 'view', $id]);
    }

    /**
     * Flag an invoice as needing review and move the package accordingly.
     *
     * @param string $id Invoice id.
     * @return \Cake\Http\Response|null
     */
    public function needsReview(string $id)
    {
        $this->request->allowMethod(['post']);

        $note = trim((string)$this->request->getData('admin_notes'));
        if ($note === '') {
            $this->Flash->error(__('Enter a review note before marking the invoice for review.'));

            return $this->redirect(['action' => 'view', $id]);
        }

        $invoice = $this->Invoices->get($id, contain: ['Packages']);
        $this->Authorization->authorize($invoice);
        if ($invoice->package->status !== PackageStatus::PendingInvoiceReview) {
            $this->Flash->error(__('Only packages pending invoice review can be marked for review.'));

            return $this->redirect(['action' => 'view', $id]);
        }

        $invoice = $this->Invoices->patchEntity($invoice, [
            'review_status' => InvoiceReviewStatus::NeedsReview->value,
            'reviewed_by_user_id' => $this->currentUserId(),
            'reviewed_at' => DateTime::now(),
            'admin_notes' => $note,
        ]);

        $flagged = $this->Invoices->getConnection()->transactional(function () use ($invoice, $note): bool {
            return (bool)(
                $this->Invoices->save($invoice) && $this->Invoices->Packages->transitionStatus(
                    $invoice->package,
                    PackageStatus::NeedsReview,
                    $this->currentUserId(),
                    UserRole::Admin,
                    $note,
                )
            );
        });

        if ($flagged) {
            $this->Flash->success(__('The invoice has been marked for review.'));

            return $this->redirect(['action' => 'index']);
        }

        $this->Flash->error(__('The invoice could not be marked for review. Please try again.'));

        return $this->redirect(['action' => 'view', $id]);
    }

    /**
     * Build a file response for an uploaded invoice.
     *
     * @param \App\Model\Entity\Invoice $invoice Invoice
     * @param bool $download Whether to force download
     * @return \Cake\Http\Response|null
     */
    private function invoiceFileResponse(Invoice $invoice, bool $download)
    {
        $path = RESOURCES . str_replace('/', DS, $invoice->file_path);
        if (!is_file($path)) {
            $this->Flash->error(__('The invoice file could not be found.'));

            return $this->redirect(['action' => 'view', $invoice->id]);
        }

        return $this->response
            ->withFile($path, [
                'download' => $download,
                'name' => $invoice->original_filename,
            ]);
    }
}
