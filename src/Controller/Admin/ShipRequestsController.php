<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Model\Entity\ShipRequest;
use App\Model\Enum\PackageStatus;
use App\Model\Enum\ShipRequestStatus;
use App\Model\Enum\UserRole;
use Cake\Datasource\EntityInterface;
use Cake\I18n\DateTime;

/**
 * ShipRequests Controller
 *
 * @property \App\Model\Table\ShipRequestsTable $ShipRequests
 * @property \Search\Controller\Component\SearchComponent $Search
 */
class ShipRequestsController extends AppController
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
            'modelClass' => 'ShipRequests',
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
        $this->Authorization->authorize($this->ShipRequests);
        $query = $this->ShipRequests->find('search', search: $this->request->getQueryParams())
            ->contain(['Clients', 'SubmittedByUsers', 'ProcessedByUsers']);
        $search = trim((string)$this->request->getQuery('q', ''));
        $shipRequests = $this->paginate($query);

        $this->set(compact('shipRequests', 'search'));
    }

    /**
     * View method
     *
     * @param string $id Ship Request id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(string $id)
    {
        $shipRequest = $this->ShipRequests->get($id, contain: ['Clients', 'SubmittedByUsers', 'ProcessedByUsers', 'PackagesShipRequests.Packages']);
        $this->Authorization->authorize($shipRequest);
        $this->set(compact('shipRequest'));
    }

    /**
     * Render a dynamic ship request manifest PDF.
     *
     * @param string $id Ship request id.
     * @return void
     */
    public function manifest(string $id): void
    {
        $shipRequest = $this->ShipRequests->get($id, contain: ['Clients', 'SubmittedByUsers', 'ProcessedByUsers', 'PackagesShipRequests.Packages']);
        $this->Authorization->authorize($shipRequest, 'view');

        $this->viewBuilder()
            ->setClassName('CakePdf.Pdf')
            ->setOption('pdfConfig', [
                'download' => true,
                'filename' => sprintf('ship-request-%d-manifest.pdf', $shipRequest->id),
            ]);
        $this->set(compact('shipRequest'));
    }

    /**
     * Export ship requests as CSV.
     *
     * @return void
     */
    public function exportCsv(): void
    {
        $this->Authorization->authorize($this->ShipRequests, 'index');
        $query = $this->ShipRequests->find('search', search: $this->request->getQueryParams())
            ->contain(['Clients', 'SubmittedByUsers', 'ProcessedByUsers'])
            ->orderBy(['ShipRequests.submitted_at' => 'DESC']);

        $shipRequests = [];
        foreach ($query as $shipRequest) {
            if (!$shipRequest instanceof EntityInterface) {
                continue;
            }
            $shipRequests[] = $this->shipRequestCsvRow($shipRequest);
        }

        $this->set(compact('shipRequests'));
        $this->setResponse($this->getResponse()->withDownload('ship-requests.csv'));
        $this->viewBuilder()
            ->setClassName('CsvView.Csv')
            ->setOptions([
                'serialize' => 'shipRequests',
                'header' => $this->shipRequestCsvHeader(),
            ]);
    }

    /**
     * Mark a ship request as processed and packages as shipped.
     *
     * @param string $id Ship Request id.
     * @return \Cake\Http\Response|null
     */
    public function process(string $id)
    {
        $this->request->allowMethod(['post']);

        $shipRequest = $this->ShipRequests->get($id, contain: ['PackagesShipRequests.Packages']);
        $this->Authorization->authorize($shipRequest);
        if (!$shipRequest->isSubmitted()) {
            $this->Flash->error(__('Only submitted ship requests can be processed.'));

            return $this->redirect(['action' => 'view', $id]);
        }
        foreach ($shipRequest->packages_ship_requests as $join) {
            if ($join->package->status !== PackageStatus::ShipRequested) {
                $this->Flash->error(__('All packages must be ready for shipment before processing.'));

                return $this->redirect(['action' => 'view', $id]);
            }
        }

        $processed = $this->ShipRequests->getConnection()->transactional(function () use ($id): ShipRequest|false {
            $now = DateTime::now();
            $updated = $this->ShipRequests->updateAll(
                [
                    'status' => ShipRequestStatus::Processed->value,
                    'processed_by_user_id' => $this->currentUserId(),
                    'processed_at' => $now,
                    'processing_reference' => $this->request->getData('processing_reference'),
                    'modified' => $now,
                ],
                [
                    'id' => $id,
                    'status' => ShipRequestStatus::Submitted->value,
                ],
            );
            if ($updated !== 1) {
                return false;
            }

            $shipRequest = $this->ShipRequests->get($id, contain: ['PackagesShipRequests.Packages']);
            foreach ($shipRequest->packages_ship_requests as $join) {
                if ($join->package->status !== PackageStatus::ShipRequested) {
                    return false;
                }
            }

            foreach ($shipRequest->packages_ship_requests as $join) {
                if (
                    !$this->ShipRequests->PackagesShipRequests->Packages->transitionStatus(
                        $join->package,
                        PackageStatus::Shipped,
                        $this->currentUserId(),
                        UserRole::Admin,
                        __('Ship request processed.'),
                    )
                ) {
                    return false;
                }
            }

            return $shipRequest;
        });

        if (!$processed) {
            $this->Flash->error(__('The ship request could not be processed. Please try again.'));

            return $this->redirect(['action' => 'view', $id]);
        }

        $this->Flash->success(__('The ship request has been processed.'));

        return $this->redirect(['action' => 'view', $processed->id]);
    }

    /**
     * @return array<string>
     */
    private function shipRequestCsvHeader(): array
    {
        return [
            'ID',
            'Client Suite',
            'Submitted By',
            'Processed By',
            'Status',
            'Processing Reference',
            'Submitted At',
            'Processed At',
            'Notes',
        ];
    }

    /**
     * @param \Cake\Datasource\EntityInterface $shipRequest Ship request entity
     * @return array<string, mixed>
     */
    private function shipRequestCsvRow(EntityInterface $shipRequest): array
    {
        return [
            'id' => $shipRequest->get('id'),
            'client_suite' => $shipRequest->get('client')?->suite_number,
            'submitted_by' => $shipRequest->get('submitted_by_user')?->name,
            'processed_by' => $shipRequest->get('processed_by_user')?->name,
            'status' => $shipRequest->get('status')?->label(),
            'processing_reference' => $shipRequest->get('processing_reference'),
            'submitted_at' => $shipRequest->get('submitted_at'),
            'processed_at' => $shipRequest->get('processed_at'),
            'notes' => $shipRequest->get('notes'),
        ];
    }
}
