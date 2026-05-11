<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * PackagesShipRequestsFixture
 */
class PackagesShipRequestsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'package_id' => 1,
                'ship_request_id' => 1,
                'created' => '2026-05-07 22:43:01',
            ],
        ];
        parent::init();
    }
}
