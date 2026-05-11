<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * InvoicesFixture
 */
class InvoicesFixture extends TestFixture
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
                'uploaded_by_user_id' => 1,
                'reviewed_by_user_id' => 1,
                'file_path' => 'Lorem ipsum dolor sit amet',
                'original_filename' => 'Lorem ipsum dolor sit amet',
                'mime_type' => 'Lorem ipsum dolor sit amet',
                'file_size' => 1,
                'review_status' => 'pending',
                'admin_notes' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'uploaded_at' => '2026-05-07 22:42:48',
                'reviewed_at' => '2026-05-07 22:42:48',
                'created' => 1778193768,
                'modified' => 1778193768,
            ],
        ];
        parent::init();
    }
}
