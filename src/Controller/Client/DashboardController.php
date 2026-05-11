<?php
declare(strict_types=1);

namespace App\Controller\Client;

use App\Controller\AppController;

/**
 * Dashboard Controller
 */
class DashboardController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $packages = $this->fetchTable('Packages');
        $this->Authorization->authorize($packages);
        $query = $packages->find();
        $packageCounts = $query
            ->select([
                'status',
                'count' => $query->func()->count('*'),
            ])
            ->where(['Packages.client_id' => $this->currentClientId()])
            ->groupBy('status')
            ->all()
            ->combine('status', 'count')
            ->toArray();

        $this->set(compact('packageCounts'));
    }
}
