<?php
declare(strict_types=1);

use App\Model\Enum\DimensionUnit;
use App\Model\Enum\InvoiceReviewStatus;
use App\Model\Enum\PackageStatus;
use App\Model\Enum\ShipRequestStatus;
use App\Model\Enum\UserRole;
use App\Model\Enum\WeightUnit;
use Migrations\BaseMigration;

class Init extends BaseMigration
{
    public function up(): void
    {
        $this->table('users')
            ->addColumn('name', 'string', ['limit' => 160])
            ->addColumn('email', 'string', ['limit' => 255])
            ->addColumn('password', 'string', ['limit' => 255])
            ->addColumn('role', 'string', ['limit' => 20])
            ->addColumn('active', 'boolean', ['default' => true, 'null' => false])
            ->addColumn('last_login', 'datetime', ['null' => true])
            ->addTimestamps('created', 'modified')
            ->addIndex(['email'], ['unique' => true])
            ->addIndex(['role', 'active'])
            ->create();

        $this->table('clients')
            ->addColumn('user_id', 'integer')
            ->addColumn('suite_number', 'string', ['limit' => 40])
            ->addColumn('phone', 'string', ['limit' => 40, 'null' => true])
            ->addColumn('address_line_1', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('address_line_2', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('city', 'string', ['limit' => 120, 'null' => true])
            ->addColumn('country', 'string', ['limit' => 120, 'default' => 'Destination', 'null' => false])
            ->addTimestamps('created', 'modified')
            ->addIndex(['user_id'], ['unique' => true])
            ->addIndex(['suite_number'], ['unique' => true])
            ->addIndex(['country', 'city'])
            ->addForeignKey('user_id', 'users', 'id')
            ->create();

        $this->table('packages')
            ->addColumn('client_id', 'integer')
            ->addColumn('tracking_number', 'string', ['limit' => 120])
            ->addColumn('width', 'decimal', ['precision' => 10, 'scale' => 2])
            ->addColumn('height', 'decimal', ['precision' => 10, 'scale' => 2])
            ->addColumn('length', 'decimal', ['precision' => 10, 'scale' => 2])
            ->addColumn('weight', 'decimal', ['precision' => 10, 'scale' => 2])
            ->addColumn('dimension_unit', 'string', [
                'limit' => 10,
                'default' => DimensionUnit::Inch->value,
                'null' => false,
            ])
            ->addColumn('weight_unit', 'string', [
                'limit' => 10,
                'default' => WeightUnit::Pound->value,
                'null' => false,
            ])
            ->addColumn('contents_description', 'text')
            ->addColumn('status', 'string', [
                'limit' => 40,
                'default' => PackageStatus::ReadyToSend->value,
                'null' => false,
            ])
            ->addColumn('received_at', 'datetime')
            ->addColumn('shipped_at', 'datetime', ['null' => true])
            ->addColumn('ready_for_pickup_at', 'datetime', ['null' => true])
            ->addColumn('delivered_at', 'datetime', ['null' => true])
            ->addColumn('created_by_user_id', 'integer', ['null' => true])
            ->addTimestamps('created', 'modified')
            ->addIndex(['tracking_number'], ['unique' => true])
            ->addIndex(['client_id'])
            ->addIndex(['created_by_user_id'])
            ->addIndex(['received_at'])
            ->addIndex(['status', 'received_at'])
            ->addIndex(['client_id', 'status', 'received_at'])
            ->addIndex(['client_id', 'received_at'])
            ->addIndex(['shipped_at'])
            ->addIndex(['ready_for_pickup_at'])
            ->addIndex(['delivered_at'])
            ->addIndex(
                ['tracking_number', 'contents_description'],
                ['type' => 'fulltext', 'name' => 'fulltext_packages_search']
            )
            ->addForeignKey('client_id', 'clients', 'id')
            ->addForeignKey('created_by_user_id', 'users', 'id')
            ->create();

        $this->table('invoices')
            ->addColumn('package_id', 'integer')
            ->addColumn('uploaded_by_user_id', 'integer')
            ->addColumn('reviewed_by_user_id', 'integer', ['null' => true])
            ->addColumn('file_path', 'string', ['limit' => 255])
            ->addColumn('original_filename', 'string', ['limit' => 255])
            ->addColumn('mime_type', 'string', ['limit' => 120])
            ->addColumn('file_size', 'integer')
            ->addColumn('review_status', 'string', [
                'limit' => 40,
                'default' => InvoiceReviewStatus::Pending->value,
                'null' => false,
            ])
            ->addColumn('admin_notes', 'text', ['null' => true])
            ->addColumn('uploaded_at', 'datetime')
            ->addColumn('reviewed_at', 'datetime', ['null' => true])
            ->addTimestamps('created', 'modified')
            ->addIndex(['package_id'], ['unique' => true])
            ->addIndex(['uploaded_by_user_id'])
            ->addIndex(['reviewed_by_user_id'])
            ->addIndex(['review_status', 'uploaded_at'])
            ->addIndex(['review_status', 'reviewed_at'])
            ->addForeignKey('package_id', 'packages', 'id')
            ->addForeignKey('uploaded_by_user_id', 'users', 'id')
            ->addForeignKey('reviewed_by_user_id', 'users', 'id')
            ->create();

        $this->table('ship_requests')
            ->addColumn('client_id', 'integer')
            ->addColumn('submitted_by_user_id', 'integer')
            ->addColumn('processed_by_user_id', 'integer', ['null' => true])
            ->addColumn('status', 'string', [
                'limit' => 40,
                'default' => ShipRequestStatus::Submitted->value,
                'null' => false,
            ])
            ->addColumn('processing_reference', 'string', ['limit' => 120, 'null' => true])
            ->addColumn('submitted_at', 'datetime')
            ->addColumn('processed_at', 'datetime', ['null' => true])
            ->addColumn('notes', 'text', ['null' => true])
            ->addTimestamps('created', 'modified')
            ->addIndex(['client_id'])
            ->addIndex(['submitted_by_user_id'])
            ->addIndex(['processed_by_user_id'])
            ->addIndex(['processing_reference'])
            ->addIndex(['status', 'submitted_at'])
            ->addIndex(['client_id', 'status', 'submitted_at'])
            ->addIndex(['processed_at'])
            ->addForeignKey('client_id', 'clients', 'id')
            ->addForeignKey('submitted_by_user_id', 'users', 'id')
            ->addForeignKey('processed_by_user_id', 'users', 'id')
            ->create();

        $this->table('packages_ship_requests')
            ->addColumn('package_id', 'integer')
            ->addColumn('ship_request_id', 'integer')
            ->addColumn('created', 'datetime')
            ->addIndex(['package_id', 'ship_request_id'], ['unique' => true])
            ->addIndex(['ship_request_id'])
            ->addIndex(['created'])
            ->addForeignKey('package_id', 'packages', 'id')
            ->addForeignKey('ship_request_id', 'ship_requests', 'id')
            ->create();

        $this->table('package_status_histories')
            ->addColumn('package_id', 'integer')
            ->addColumn('changed_by_user_id', 'integer', ['null' => true])
            ->addColumn('changed_by_role', 'string', ['limit' => 20, 'null' => true])
            ->addColumn('old_status', 'string', ['limit' => 40, 'null' => true])
            ->addColumn('new_status', 'string', ['limit' => 40])
            ->addColumn('note', 'text', ['null' => true])
            ->addColumn('created', 'datetime')
            ->addIndex(['package_id', 'created'])
            ->addIndex(['changed_by_user_id'])
            ->addIndex(['changed_by_role', 'created'])
            ->addIndex(['new_status', 'created'])
            ->addIndex(['old_status', 'new_status'])
            ->addForeignKey('package_id', 'packages', 'id')
            ->addForeignKey('changed_by_user_id', 'users', 'id')
            ->create();

        $this->addDomainConstraints();
    }

    public function down(): void
    {
        $this->table('package_status_histories')->drop()->save();
        $this->table('packages_ship_requests')->drop()->save();
        $this->table('ship_requests')->drop()->save();
        $this->table('invoices')->drop()->save();
        $this->table('packages')->drop()->save();
        $this->table('clients')->drop()->save();
        $this->table('users')->drop()->save();
    }

    private function addDomainConstraints(): void
    {
        if ($this->getAdapter()->getAdapterType() === 'sqlite') {
            return;
        }

        $this->addAllowedValuesConstraint('users', 'role', $this->enumValues(UserRole::cases()));
        $this->addAllowedValuesConstraint('packages', 'status', $this->enumValues(PackageStatus::cases()));
        $this->addAllowedValuesConstraint('packages', 'dimension_unit', $this->enumValues(DimensionUnit::cases()));
        $this->addAllowedValuesConstraint('packages', 'weight_unit', $this->enumValues(WeightUnit::cases()));
        $this->addAllowedValuesConstraint(
            'invoices',
            'review_status',
            $this->enumValues(InvoiceReviewStatus::cases())
        );
        $this->addAllowedValuesConstraint(
            'ship_requests',
            'status',
            $this->enumValues(ShipRequestStatus::cases())
        );
        $this->addAllowedValuesConstraint(
            'package_status_histories',
            'old_status',
            $this->enumValues(PackageStatus::cases()),
            true
        );
        $this->addAllowedValuesConstraint(
            'package_status_histories',
            'changed_by_role',
            $this->enumValues(UserRole::cases()),
            true
        );
        $this->addAllowedValuesConstraint(
            'package_status_histories',
            'new_status',
            $this->enumValues(PackageStatus::cases())
        );
    }

    /**
     * @param array<\BackedEnum> $cases
     * @return array<string>
     */
    private function enumValues(array $cases): array
    {
        return array_map(
            fn(\BackedEnum $case): string => (string)$case->value,
            $cases
        );
    }

    /**
     * @param array<string> $allowedValues
     */
    private function addAllowedValuesConstraint(
        string $table,
        string $column,
        array $allowedValues,
        bool $allowNull = false
    ): void {
        $constraintName = sprintf('chk_%s_%s', $table, $column);
        $quotedValues = implode(', ', array_map(
            fn(string $value): string => "'" . str_replace("'", "''", $value) . "'",
            $allowedValues
        ));
        $nullClause = $allowNull ? sprintf(' OR `%s` IS NULL', $column) : '';

        $this->execute(sprintf(
            'ALTER TABLE `%s` ADD CONSTRAINT `%s` CHECK (`%s` IN (%s)%s)',
            $table,
            $constraintName,
            $column,
            $quotedValues,
            $nullClause
        ));
    }
}
