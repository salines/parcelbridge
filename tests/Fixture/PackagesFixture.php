<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * PackagesFixture
 */
class PackagesFixture extends TestFixture
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
                'client_id' => 1,
                'tracking_number' => 'Lorem ipsum dolor sit amet',
                'width' => 1.5,
                'height' => 1.5,
                'length' => 1.5,
                'weight' => 1.5,
                'dimension_unit' => 'in',
                'weight_unit' => 'lb',
                'contents_description' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'status' => 'ready_to_send',
                'received_at' => '2026-05-07 22:42:44',
                'shipped_at' => '2026-05-07 22:42:44',
                'ready_for_pickup_at' => '2026-05-07 22:42:44',
                'delivered_at' => '2026-05-07 22:42:44',
                'created_by_user_id' => 1,
                'created' => 1778193764,
                'modified' => 1778193764,
            ],
        ];
        parent::init();
    }
}
