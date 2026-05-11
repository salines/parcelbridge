<?php
declare(strict_types=1);

use App\Model\Enum\DimensionUnit;
use App\Model\Enum\InvoiceReviewStatus;
use App\Model\Enum\PackageStatus;
use App\Model\Enum\ShipRequestStatus;
use App\Model\Enum\UserRole;
use App\Model\Enum\WeightUnit;
use Authentication\PasswordHasher\DefaultPasswordHasher;
use Migrations\BaseSeed;

class InitSeed extends BaseSeed
{
    public function run(): void
    {
        $this->clearTables();

        $now = date('Y-m-d H:i:s');
        $hasher = new DefaultPasswordHasher();

        $this->table('users')->insert([
            [
                'id' => 1,
                'name' => 'ParcelBridge Admin',
                'email' => 'admin@parcelbridge.test',
                'password' => $hasher->hash('password123'),
                'role' => UserRole::Admin->value,
                'active' => true,
                'last_login' => null,
                'created' => $now,
                'modified' => $now,
            ],
            [
                'id' => 2,
                'name' => 'Test Client',
                'email' => 'client@parcelbridge.test',
                'password' => $hasher->hash('password123'),
                'role' => UserRole::Client->value,
                'active' => true,
                'last_login' => null,
                'created' => $now,
                'modified' => $now,
            ],
        ])->saveData();

        $this->table('clients')->insert([
            [
                'id' => 1,
                'user_id' => 2,
                'suite_number' => 'S2A-1001',
                'phone' => '+297 555 0101',
                'address_line_1' => 'L.G. Smith Boulevard 1',
                'address_line_2' => null,
                'city' => 'Oranjestad',
                'country' => 'Destination',
                'created' => $now,
                'modified' => $now,
            ],
        ])->saveData();

        $this->table('packages')->insert([
            [
                'id' => 1,
                'client_id' => 1,
                'tracking_number' => 'S2A-DEMO-READY',
                'width' => 12.00,
                'height' => 8.00,
                'length' => 16.00,
                'weight' => 4.25,
                'dimension_unit' => DimensionUnit::Inch->value,
                'weight_unit' => WeightUnit::Pound->value,
                'contents_description' => 'Clothing order',
                'status' => PackageStatus::ReadyToSend->value,
                'received_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
                'shipped_at' => null,
                'ready_for_pickup_at' => null,
                'delivered_at' => null,
                'created_by_user_id' => 1,
                'created' => $now,
                'modified' => $now,
            ],
            [
                'id' => 2,
                'client_id' => 1,
                'tracking_number' => 'S2A-DEMO-PENDING-INVOICE',
                'width' => 10.00,
                'height' => 10.00,
                'length' => 14.00,
                'weight' => 6.50,
                'dimension_unit' => DimensionUnit::Inch->value,
                'weight_unit' => WeightUnit::Pound->value,
                'contents_description' => 'Small kitchen appliance',
                'status' => PackageStatus::PendingInvoiceReview->value,
                'received_at' => date('Y-m-d H:i:s', strtotime('-4 days')),
                'shipped_at' => null,
                'ready_for_pickup_at' => null,
                'delivered_at' => null,
                'created_by_user_id' => 1,
                'created' => $now,
                'modified' => $now,
            ],
            [
                'id' => 3,
                'client_id' => 1,
                'tracking_number' => 'S2A-DEMO-APPROVED',
                'width' => 9.00,
                'height' => 6.00,
                'length' => 12.00,
                'weight' => 3.75,
                'dimension_unit' => DimensionUnit::Inch->value,
                'weight_unit' => WeightUnit::Pound->value,
                'contents_description' => 'Electronics accessories',
                'status' => PackageStatus::InvoiceApproved->value,
                'received_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'shipped_at' => null,
                'ready_for_pickup_at' => null,
                'delivered_at' => null,
                'created_by_user_id' => 1,
                'created' => $now,
                'modified' => $now,
            ],
            [
                'id' => 4,
                'client_id' => 1,
                'tracking_number' => 'S2A-DEMO-SHIPPED',
                'width' => 18.00,
                'height' => 12.00,
                'length' => 20.00,
                'weight' => 12.10,
                'dimension_unit' => DimensionUnit::Inch->value,
                'weight_unit' => WeightUnit::Pound->value,
                'contents_description' => 'Home goods',
                'status' => PackageStatus::Shipped->value,
                'received_at' => date('Y-m-d H:i:s', strtotime('-10 days')),
                'shipped_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'ready_for_pickup_at' => null,
                'delivered_at' => null,
                'created_by_user_id' => 1,
                'created' => $now,
                'modified' => $now,
            ],
        ])->saveData();

        $this->table('invoices')->insert([
            [
                'id' => 1,
                'package_id' => 2,
                'uploaded_by_user_id' => 2,
                'reviewed_by_user_id' => null,
                'file_path' => 'pdf/invoices/demo-pending-invoice.pdf',
                'original_filename' => 'demo-pending-invoice.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 635,
                'review_status' => InvoiceReviewStatus::Pending->value,
                'admin_notes' => null,
                'uploaded_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'reviewed_at' => null,
                'created' => $now,
                'modified' => $now,
            ],
            [
                'id' => 2,
                'package_id' => 3,
                'uploaded_by_user_id' => 2,
                'reviewed_by_user_id' => 1,
                'file_path' => 'pdf/invoices/demo-approved-invoice.pdf',
                'original_filename' => 'demo-approved-invoice.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 629,
                'review_status' => InvoiceReviewStatus::Approved->value,
                'admin_notes' => null,
                'uploaded_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'reviewed_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'created' => $now,
                'modified' => $now,
            ],
            [
                'id' => 3,
                'package_id' => 4,
                'uploaded_by_user_id' => 2,
                'reviewed_by_user_id' => 1,
                'file_path' => 'pdf/invoices/demo-shipped-invoice.pdf',
                'original_filename' => 'demo-shipped-invoice.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 628,
                'review_status' => InvoiceReviewStatus::Approved->value,
                'admin_notes' => null,
                'uploaded_at' => date('Y-m-d H:i:s', strtotime('-8 days')),
                'reviewed_at' => date('Y-m-d H:i:s', strtotime('-7 days')),
                'created' => $now,
                'modified' => $now,
            ],
        ])->saveData();

        $this->table('ship_requests')->insert([
            [
                'id' => 1,
                'client_id' => 1,
                'submitted_by_user_id' => 2,
                'processed_by_user_id' => 1,
                'status' => ShipRequestStatus::Processed->value,
                'processing_reference' => 'SHP-DEMO-001',
                'submitted_at' => date('Y-m-d H:i:s', strtotime('-6 days')),
                'processed_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'notes' => 'Demo processed request.',
                'created' => $now,
                'modified' => $now,
            ],
        ])->saveData();

        $this->table('packages_ship_requests')->insert([
            [
                'package_id' => 4,
                'ship_request_id' => 1,
                'created' => date('Y-m-d H:i:s', strtotime('-6 days')),
            ],
        ])->saveData();

        $this->table('package_status_histories')->insert([
            [
                'package_id' => 1,
                'changed_by_user_id' => 1,
                'changed_by_role' => UserRole::Admin->value,
                'old_status' => null,
                'new_status' => PackageStatus::ReadyToSend->value,
                'note' => 'Package received at warehouse.',
                'created' => date('Y-m-d H:i:s', strtotime('-5 days')),
            ],
            [
                'package_id' => 2,
                'changed_by_user_id' => 2,
                'changed_by_role' => UserRole::Client->value,
                'old_status' => PackageStatus::ReadyToSend->value,
                'new_status' => PackageStatus::PendingInvoiceReview->value,
                'note' => 'Client uploaded invoice.',
                'created' => date('Y-m-d H:i:s', strtotime('-2 days')),
            ],
            [
                'package_id' => 3,
                'changed_by_user_id' => 1,
                'changed_by_role' => UserRole::Admin->value,
                'old_status' => PackageStatus::PendingInvoiceReview->value,
                'new_status' => PackageStatus::InvoiceApproved->value,
                'note' => 'Invoice approved.',
                'created' => date('Y-m-d H:i:s', strtotime('-1 day')),
            ],
            [
                'package_id' => 4,
                'changed_by_user_id' => 1,
                'changed_by_role' => UserRole::Admin->value,
                'old_status' => PackageStatus::ShipRequested->value,
                'new_status' => PackageStatus::Shipped->value,
                'note' => 'Ship request processed.',
                'created' => date('Y-m-d H:i:s', strtotime('-1 day')),
            ],
        ])->saveData();
    }

    private function clearTables(): void
    {
        $adapter = $this->getAdapter()->getAdapterType();
        if ($adapter === 'mysql') {
            $this->execute('SET FOREIGN_KEY_CHECKS=0');
        }

        foreach ([
            'package_status_histories',
            'packages_ship_requests',
            'ship_requests',
            'invoices',
            'packages',
            'clients',
            'users',
        ] as $table) {
            $this->table($table)->truncate();
        }

        if ($adapter === 'mysql') {
            $this->execute('SET FOREIGN_KEY_CHECKS=1');
        }
    }
}
