<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Model\Enum\PackageStatus;

/**
 * API Shipments Controller.
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
     * Shipment list.
     *
     * @return void
     */
    public function index(): void
    {
        $packages = $this->fetchTable('Packages');
        $this->Authorization->authorize($packages, 'index');
        $query = $this->Authorization->applyScope(
            $packages->find('search', search: $this->request->getQueryParams())
                ->contain(['Clients', 'Invoices', 'PackagesShipRequests.ShipRequests']),
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

        $shipments = [];
        foreach ($this->paginate($query) as $shipment) {
            $shipments[] = $this->packageResource($shipment);
        }

        $this->json(['shipments' => $shipments]);
    }
}
