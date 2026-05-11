<?php
declare(strict_types=1);

namespace App\Controller\Client;

use App\Controller\AppController;
use App\Model\Enum\PackageStatus;

/**
 * Shipments Controller
 *
 * @property \Search\Controller\Component\SearchComponent $Search
 */
class ShipmentsController extends AppController
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
        $packages = $this->fetchTable('Packages');
        $this->Authorization->authorize($packages, 'index');
        $query = $this->Authorization->applyScope(
            $packages->find('search', search: $this->request->getQueryParams())
                ->contain(['Clients', 'PackagesShipRequests.ShipRequests']),
            'shipments',
        )
            ->where([
                'Packages.status IN' => [
                    PackageStatus::Shipped->value,
                    PackageStatus::ReadyForPickup->value,
                    PackageStatus::Delivered->value,
                ],
            ])
            ->orderBy(['Packages.modified' => 'DESC']);
        $search = trim((string)$this->request->getQuery('q', ''));

        $shipments = $this->paginate($query);

        $this->set(compact('shipments', 'search'));
    }
}
