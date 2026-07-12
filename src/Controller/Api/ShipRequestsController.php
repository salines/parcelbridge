<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Model\Entity\ShipRequest;
use App\Model\Enum\PackageStatus;
use App\Model\Enum\ShipRequestStatus;
use App\Model\Enum\UserRole;
use Cake\I18n\DateTime;
use InvalidArgumentException;

/**
 * API Ship Requests Controller.
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
     * Ship request list.
     *
     * @return void
     */
    public function index(): void
    {
        $this->Authorization->authorize($this->ShipRequests);
        $query = $this->Authorization->applyScope(
            $this->ShipRequests->find('search', search: $this->request->getQueryParams())
                ->contain(['Clients', 'PackagesShipRequests.Packages'])
                ->orderBy(['ShipRequests.submitted_at' => 'DESC']),
        );

        $shipRequests = [];
        foreach ($this->paginate($query) as $shipRequest) {
            $shipRequests[] = $this->shipRequestResource($shipRequest);
        }

        $this->json(['ship_requests' => $shipRequests]);
    }

    /**
     * Ship request detail.
     *
     * @param string $id Ship request id
     * @return void
     */
    public function view(string $id): void
    {
        $shipRequest = $this->ShipRequests->get($id, contain: ['Clients', 'PackagesShipRequests.Packages']);
        $this->Authorization->authorize($shipRequest);

        $this->json(['ship_request' => $this->shipRequestResource($shipRequest)]);
    }

    /**
     * Submit a ship request for approved packages.
     *
     * @return void
     */
    public function add(): void
    {
        $this->request->allowMethod(['post']);
        $this->Authorization->authorize($this->ShipRequests);

        $clientId = $this->currentClientId();
        if ($clientId === null) {
            $this->jsonError(__('Your account is not linked to a client.'), 403);

            return;
        }

        $packageIds = (array)$this->request->getData('package_ids', []);
        if ($packageIds === [] && $this->request->getData('packages._ids') !== null) {
            $packageIds = (array)$this->request->getData('packages._ids');
        }

        try {
            $shipRequest = $this->ShipRequests->submitForPackages($clientId, (int)$this->currentUserId(), $packageIds);
        } catch (InvalidArgumentException $exception) {
            $this->jsonError($exception->getMessage());

            return;
        }

        if (!$shipRequest) {
            $this->jsonError(__('The ship request could not be submitted. Please try again.'));

            return;
        }

        $shipRequest = $this->ShipRequests->get($shipRequest->id, contain: ['Clients', 'PackagesShipRequests.Packages']);
        $this->json(['ship_request' => $this->shipRequestResource($shipRequest)], 201);
    }

    /**
     * Mark a submitted ship request as processed.
     *
     * @param string $id Ship request id
     * @return void
     */
    public function process(string $id): void
    {
        $this->request->allowMethod(['post']);

        $shipRequest = $this->ShipRequests->get($id, contain: ['PackagesShipRequests.Packages']);
        $this->Authorization->authorize($shipRequest);
        if (!$shipRequest->isSubmitted()) {
            $this->jsonError(__('Only submitted ship requests can be processed.'));

            return;
        }
        foreach ($shipRequest->packages_ship_requests as $join) {
            if ($join->package->status !== PackageStatus::ShipRequested) {
                $this->jsonError(__('All packages must be ready for shipment before processing.'));

                return;
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
            $this->jsonError(__('The ship request could not be processed. Please try again.'));

            return;
        }

        $processed = $this->ShipRequests->get($processed->id, contain: ['Clients', 'PackagesShipRequests.Packages']);
        $this->json(['ship_request' => $this->shipRequestResource($processed)]);
    }
}
