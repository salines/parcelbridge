<?php
declare(strict_types=1);

namespace App\Controller\Client;

use App\Controller\AppController;
use App\Model\Enum\PackageStatus;
use Cake\Datasource\EntityInterface;
use InvalidArgumentException;

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
        $query = $this->Authorization->applyScope(
            $this->ShipRequests->find('search', search: $this->request->getQueryParams())
                ->contain(['Clients', 'SubmittedByUsers', 'ProcessedByUsers']),
        );
        $search = trim((string)$this->request->getQuery('q', ''));
        $shipRequests = $this->paginate($query);

        $this->set(compact('shipRequests', 'search'));
    }

    /**
     * View method
     *
     * @param string $id Ship Request id.
     * @return \Cake\Http\Response|null|void Renders view
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
     * Export scoped ship requests as CSV.
     *
     * @return void
     */
    public function exportCsv(): void
    {
        $this->Authorization->authorize($this->ShipRequests, 'index');
        $query = $this->Authorization->applyScope(
            $this->ShipRequests->find('search', search: $this->request->getQueryParams())
                ->contain(['Clients', 'SubmittedByUsers', 'ProcessedByUsers'])
                ->orderBy(['ShipRequests.submitted_at' => 'DESC']),
        );

        $shipRequests = [];
        foreach ($query as $shipRequest) {
            $shipRequests[] = $this->shipRequestCsvRow($shipRequest);
        }

        $this->set(compact('shipRequests'));
        $this->setResponse($this->getResponse()->withDownload('my-ship-requests.csv'));
        $this->viewBuilder()
            ->setClassName('CsvView.Csv')
            ->setOptions([
                'serialize' => 'shipRequests',
                'header' => $this->shipRequestCsvHeader(),
            ]);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $this->Authorization->authorize($this->ShipRequests);
        $clientId = $this->currentClientId();
        if ($clientId === null) {
            $this->Flash->error(__('Your account is not linked to a client.'));

            return $this->redirect(['controller' => 'Dashboard', 'action' => 'index']);
        }

        $packagesQuery = $this->ShipRequests->PackagesShipRequests->Packages->find()
            ->where([
                'Packages.client_id' => $clientId,
                'Packages.status' => PackageStatus::InvoiceApproved->value,
            ])
            ->orderBy(['Packages.received_at' => 'ASC']);

        $packages = $packagesQuery->all()->toList();
        $shipRequest = $this->ShipRequests->newEmptyEntity();

        if ($this->request->is('post')) {
            $packageIds = array_values(array_filter((array)$this->request->getData('packages._ids')));

            try {
                $shipRequest = $this->ShipRequests->submitForPackages($clientId, (int)$this->currentUserId(), $packageIds);
            } catch (InvalidArgumentException $exception) {
                $this->Flash->error($exception->getMessage());

                return $this->redirect(['action' => 'add']);
            }

            if ($shipRequest) {
                $this->Flash->success(__('The ship request has been submitted.'));

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(__('The ship request could not be submitted. Please try again.'));
        }

        $this->set(compact('shipRequest', 'packages'));
    }

    /**
     * @return array<string>
     */
    private function shipRequestCsvHeader(): array
    {
        return [
            'ID',
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
            'status' => $shipRequest->get('status')?->label(),
            'processing_reference' => $shipRequest->get('processing_reference'),
            'submitted_at' => $shipRequest->get('submitted_at'),
            'processed_at' => $shipRequest->get('processed_at'),
            'notes' => $shipRequest->get('notes'),
        ];
    }
}
