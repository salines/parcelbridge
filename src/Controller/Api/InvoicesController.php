<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Model\Enum\InvoiceReviewStatus;
use App\Model\Enum\PackageStatus;
use App\Model\Enum\UserRole;
use Cake\I18n\DateTime;

/**
 * API Invoices Controller.
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
     * Invoice list.
     *
     * @return void
     */
    public function index(): void
    {
        $this->Authorization->authorize($this->Invoices);
        $query = $this->Authorization->applyScope(
            $this->Invoices->find('search', search: $this->request->getQueryParams())
                ->contain(['Packages.Clients'])
                ->orderBy(['Invoices.uploaded_at' => 'DESC']),
        );

        $invoices = [];
        foreach ($this->paginate($query) as $invoice) {
            $invoices[] = $this->invoiceResource($invoice);
        }

        $this->json(['invoices' => $invoices]);
    }

    /**
     * Invoice detail.
     *
     * @param string $id Invoice id
     * @return void
     */
    public function view(string $id): void
    {
        $invoice = $this->Invoices->get($id, contain: ['Packages.Clients']);
        $this->Authorization->authorize($invoice);

        $this->json(['invoice' => $this->invoiceResource($invoice)]);
    }

    /**
     * Approve an invoice.
     *
     * @param string $id Invoice id
     * @return void
     */
    public function approve(string $id): void
    {
        $this->request->allowMethod(['post']);

        $invoice = $this->Invoices->get($id, contain: ['Packages']);
        $this->Authorization->authorize($invoice);
        if ($invoice->package->status !== PackageStatus::PendingInvoiceReview) {
            $this->jsonError(__('Only packages pending invoice review can be approved.'));

            return;
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

        if (!$approved) {
            $this->jsonError(__('The invoice could not be approved. Please try again.'));

            return;
        }

        $invoice = $this->Invoices->get($invoice->id, contain: ['Packages.Clients']);
        $this->json(['invoice' => $this->invoiceResource($invoice)]);
    }

    /**
     * Flag an invoice as needing review.
     *
     * @param string $id Invoice id
     * @return void
     */
    public function needsReview(string $id): void
    {
        $this->request->allowMethod(['post']);

        $note = trim((string)$this->request->getData('admin_notes'));
        if ($note === '') {
            $this->jsonError(__('Enter a review note before marking the invoice for review.'));

            return;
        }

        $invoice = $this->Invoices->get($id, contain: ['Packages']);
        $this->Authorization->authorize($invoice);
        if ($invoice->package->status !== PackageStatus::PendingInvoiceReview) {
            $this->jsonError(__('Only packages pending invoice review can be marked for review.'));

            return;
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

        if (!$flagged) {
            $this->jsonError(__('The invoice could not be marked for review. Please try again.'));

            return;
        }

        $invoice = $this->Invoices->get($invoice->id, contain: ['Packages.Clients']);
        $this->json(['invoice' => $this->invoiceResource($invoice)]);
    }
}
