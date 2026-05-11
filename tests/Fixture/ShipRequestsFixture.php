<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ShipRequestsFixture
 */
class ShipRequestsFixture extends TestFixture
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
                'submitted_by_user_id' => 1,
                'processed_by_user_id' => 1,
                'status' => 'submitted',
                'processing_reference' => 'Lorem ipsum dolor sit amet',
                'submitted_at' => '2026-05-07 22:42:52',
                'processed_at' => '2026-05-07 22:42:52',
                'notes' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'created' => 1778193772,
                'modified' => 1778193772,
            ],
        ];
        parent::init();
    }
}
