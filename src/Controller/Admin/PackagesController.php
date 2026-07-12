<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Model\Enum\DimensionUnit;
use App\Model\Enum\PackageStatus;
use App\Model\Enum\UserRole;
use App\Model\Enum\WeightUnit;
use Cake\Database\Exception\QueryException;
use Cake\Datasource\EntityInterface;
use Cake\I18n\DateTime;
use InvalidArgumentException;

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
        $query = $this->Packages->find('search', search: $this->request->getQueryParams())
            ->contain(['Clients', 'CreatedByUsers']);
        $search = trim((string)$this->request->getQuery('q', ''));
        $packages = $this->paginate($query);

        $this->set(compact('packages', 'search'));
    }

    /**
     * View method
     *
     * @param string $id Package id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
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
     * Export package list as CSV.
     *
     * @return void
     */
    public function exportCsv(): void
    {
        $this->Authorization->authorize($this->Packages, 'index');
        $query = $this->Packages->find('search', search: $this->request->getQueryParams())
            ->contain(['Clients', 'CreatedByUsers'])
            ->orderBy(['Packages.received_at' => 'DESC']);

        $packages = [];
        foreach ($query as $package) {
            if (!$package instanceof EntityInterface) {
                continue;
            }
            $packages[] = $this->packageCsvRow($package);
        }

        $this->set(compact('packages'));
        $this->setResponse($this->getResponse()->withDownload('packages.csv'));
        $this->viewBuilder()
            ->setClassName('CsvView.Csv')
            ->setOptions([
                'serialize' => 'packages',
                'header' => $this->packageCsvHeader(),
            ]);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $this->Authorization->authorize($this->Packages);
        $package = $this->Packages->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data['status'] = PackageStatus::ReadyToSend->value;
            $data['dimension_unit'] = $data['dimension_unit'] ?? DimensionUnit::Inch->value;
            $data['weight_unit'] = $data['weight_unit'] ?? WeightUnit::Pound->value;
            $data['received_at'] = $data['received_at'] ?? DateTime::now();
            if ($data['received_at'] === '') {
                $data['received_at'] = DateTime::now();
            }
            $data['created_by_user_id'] = $this->currentUserId();

            $package = $this->Packages->patchEntity($package, $data);
            $saved = $this->Packages->getConnection()->transactional(function () use ($package): bool {
                $package = $this->Packages->save($package);
                if (!$package) {
                    return false;
                }

                return $this->Packages->recordStatusHistory(
                    $package,
                    null,
                    PackageStatus::ReadyToSend,
                    $this->currentUserId(),
                    UserRole::Admin,
                    __('Package received at warehouse.'),
                );
            });

            if ($saved) {
                $this->Flash->success(__('The record has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The record could not be saved. Please check the form and try again.'));
        }
        $clients = $this->Packages->Clients->find('list', limit: 200)->all();
        $dimensionUnits = $this->dimensionUnitOptions();
        $weightUnits = $this->weightUnitOptions();
        $this->set(compact('package', 'clients', 'dimensionUnits', 'weightUnits'));
    }

    /**
     * Edit method
     *
     * @param string $id Package id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(string $id)
    {
        $package = $this->Packages->get($id, contain: ['PackagesShipRequests.ShipRequests']);
        $this->Authorization->authorize($package);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            unset($data['status'], $data['shipped_at'], $data['ready_for_pickup_at'], $data['delivered_at']);
            $package = $this->Packages->patchEntity($package, $data);
            if ($this->Packages->save($package)) {
                $this->Flash->success(__('The record has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The record could not be saved. Please check the form and try again.'));
        }
        $clients = $this->Packages->Clients->find('list', limit: 200)->all();
        $dimensionUnits = $this->dimensionUnitOptions();
        $weightUnits = $this->weightUnitOptions();
        $this->set(compact('package', 'clients', 'dimensionUnits', 'weightUnits'));
    }

    /**
     * Delete method
     *
     * @param string $id Package id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(string $id)
    {
        $this->request->allowMethod(['post', 'delete']);
        $package = $this->Packages->get($id);
        $this->Authorization->authorize($package);
        try {
            $deleted = $this->Packages->delete($package);
        } catch (QueryException) {
            $deleted = false;
        }
        if (!$deleted) {
            $this->Flash->error(__('This package cannot be deleted because it has related operational records.'));
        } else {
            $this->Flash->success(__('The record has been deleted.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Mark a shipped package as ready for pickup at destination.
     *
     * @param string $id Package id.
     * @return \Cake\Http\Response|null
     */
    public function readyForPickup(string $id)
    {
        $this->request->allowMethod(['post']);

        return $this->transitionPackage($id, PackageStatus::ReadyForPickup, __('Package arrived at destination and is ready for pickup.'));
    }

    /**
     * Mark a shipped or ready-for-pickup package as delivered.
     *
     * @param string $id Package id.
     * @return \Cake\Http\Response|null
     */
    public function deliver(string $id)
    {
        $this->request->allowMethod(['post']);

        return $this->transitionPackage($id, PackageStatus::Delivered, __('Package delivered to the client.'));
    }

    /**
     * Apply an admin package status transition.
     *
     * @param string $id Package id
     * @param \App\Model\Enum\PackageStatus $status New status
     * @param string $note Audit note
     * @return \Cake\Http\Response|null
     */
    private function transitionPackage(string $id, PackageStatus $status, string $note)
    {
        $package = $this->Packages->get($id);
        $this->Authorization->authorize($package);

        try {
            if ($this->Packages->transitionStatus($package, $status, $this->currentUserId(), UserRole::Admin, $note)) {
                $this->Flash->success(__('The package status has been updated.'));

                return $this->redirect(['action' => 'view', $package->id]);
            }
        } catch (InvalidArgumentException $exception) {
            $this->Flash->error($exception->getMessage());

            return $this->redirect(['action' => 'view', $id]);
        }

        $this->Flash->error(__('The package status could not be updated.'));

        return $this->redirect(['action' => 'view', $id]);
    }

    /**
     * @return array<string, string>
     */
    private function dimensionUnitOptions(): array
    {
        return [
            DimensionUnit::Inch->value => DimensionUnit::Inch->label(),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function weightUnitOptions(): array
    {
        return [
            WeightUnit::Pound->value => WeightUnit::Pound->label(),
        ];
    }

    /**
     * @return array<string>
     */
    private function packageCsvHeader(): array
    {
        return [
            'ID',
            'Client Suite',
            'Tracking Number',
            'Contents',
            'Status',
            'Weight',
            'Weight Unit',
            'Width',
            'Height',
            'Length',
            'Dimension Unit',
            'Received At',
            'Shipped At',
            'Ready For Pickup At',
            'Delivered At',
            'Created By',
        ];
    }

    /**
     * @param \Cake\Datasource\EntityInterface $package Package entity
     * @return array<string, mixed>
     */
    private function packageCsvRow(EntityInterface $package): array
    {
        return [
            'id' => $package->get('id'),
            'client_suite' => $package->get('client')?->suite_number,
            'tracking_number' => $package->get('tracking_number'),
            'contents_description' => $package->get('contents_description'),
            'status' => $package->get('status')?->label(),
            'weight' => $package->get('weight'),
            'weight_unit' => $package->get('weight_unit')?->label(),
            'width' => $package->get('width'),
            'height' => $package->get('height'),
            'length' => $package->get('length'),
            'dimension_unit' => $package->get('dimension_unit')?->label(),
            'received_at' => $package->get('received_at'),
            'shipped_at' => $package->get('shipped_at'),
            'ready_for_pickup_at' => $package->get('ready_for_pickup_at'),
            'delivered_at' => $package->get('delivered_at'),
            'created_by' => $package->get('created_by_user')?->name,
        ];
    }
}
