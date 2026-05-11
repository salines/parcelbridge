<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Model\Enum\InvoiceReviewStatus;

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
        $packageCountsQuery = $packages->find();
        $packageCounts = $packageCountsQuery
            ->select([
                'status',
                'count' => $packageCountsQuery->func()->count('*'),
            ])
            ->groupBy('status')
            ->all()
            ->combine('status', 'count')
            ->toArray();

        $clientCount = $this->fetchTable('Clients')->find()->count();
        $pendingInvoiceCount = $this->fetchTable('Invoices')
            ->find()
            ->where(['review_status' => InvoiceReviewStatus::Pending->value])
            ->count();

        $this->set(compact('packageCounts', 'clientCount', 'pendingInvoiceCount'));
    }
}
