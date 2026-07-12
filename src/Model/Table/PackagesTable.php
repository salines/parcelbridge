<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Package as PackageEntity;
use App\Model\Enum\DimensionUnit;
use App\Model\Enum\PackageStatus;
use App\Model\Enum\UserRole;
use App\Model\Enum\WeightUnit;
use Cake\Database\Type\EnumType;
use Cake\I18n\DateTime;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use InvalidArgumentException;

/**
 * Packages Model
 *
 * @property \App\Model\Table\ClientsTable&\Cake\ORM\Association\BelongsTo $Clients
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $CreatedByUsers
 * @property \App\Model\Table\InvoicesTable&\Cake\ORM\Association\HasOne $Invoices
 * @property \App\Model\Table\PackageStatusHistoriesTable&\Cake\ORM\Association\HasMany $PackageStatusHistories
 * @property \App\Model\Table\PackagesShipRequestsTable&\Cake\ORM\Association\HasMany $PackagesShipRequests
 * @mixin \Search\Model\Behavior\SearchBehavior
 * @method \App\Model\Entity\Package newEmptyEntity()
 * @method \App\Model\Entity\Package newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Package[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Package get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Package findOrCreate(\Cake\ORM\Query\SelectQuery|callable|array $search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Package patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Package[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Package|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Package saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Package[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Package>|false saveMany(iterable $entities, array $options = [])
 * @method \App\Model\Entity\Package[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Package> saveManyOrFail(iterable $entities, array $options = [])
 * @method \App\Model\Entity\Package[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Package>|false deleteMany(iterable $entities, array $options = [])
 * @method \App\Model\Entity\Package[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Package> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @extends \Cake\ORM\Table<array{Search: \Search\Model\Behavior\SearchBehavior, Timestamp: \Cake\ORM\Behavior\TimestampBehavior}>
 */
class PackagesTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('packages');
        $this->setDisplayField('tracking_number');
        $this->setPrimaryKey('id');

        $this->getSchema()->setColumnType('status', EnumType::from(PackageStatus::class));
        $this->getSchema()->setColumnType('dimension_unit', EnumType::from(DimensionUnit::class));
        $this->getSchema()->setColumnType('weight_unit', EnumType::from(WeightUnit::class));

        $this->addBehavior('Timestamp');
        $this->addBehavior('Search.Search');

        $this->belongsTo('Clients', [
            'foreignKey' => 'client_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('CreatedByUsers', [
            'foreignKey' => 'created_by_user_id',
            'className' => 'Users',
        ]);
        $this->hasOne('Invoices', [
            'foreignKey' => 'package_id',
        ]);
        $this->hasMany('PackageStatusHistories', [
            'foreignKey' => 'package_id',
        ]);
        $this->hasMany('PackagesShipRequests', [
            'foreignKey' => 'package_id',
        ]);

        $this->getBehavior('Search')->searchManager()
            ->callback('q', [
                'callback' => function (SelectQuery $query, array $args): bool {
                    $search = trim((string)($args['q'] ?? ''));
                    if ($search === '') {
                        return false;
                    }

                    $like = '%' . $search . '%';
                    $conditions = [
                        'Packages.tracking_number LIKE' => $like,
                        'Packages.contents_description LIKE' => $like,
                        'Clients.suite_number LIKE' => $like,
                        'Users.name LIKE' => $like,
                        'Users.email LIKE' => $like,
                    ];

                    $normalizedStatus = strtolower(str_replace(' ', '_', $search));
                    foreach (PackageStatus::cases() as $status) {
                        if (
                            $normalizedStatus === $status->value
                            || str_contains(strtolower($status->label()), strtolower($search))
                        ) {
                            $conditions['Packages.status'] = $status->value;
                            break;
                        }
                    }

                    $query
                        ->leftJoinWith('Clients.Users')
                        ->where(['OR' => $conditions])
                        ->distinct(['Packages.id']);

                    return true;
                },
            ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('client_id')
            ->notEmptyString('client_id');

        $validator
            ->scalar('tracking_number')
            ->maxLength('tracking_number', 120)
            ->requirePresence('tracking_number', 'create')
            ->notEmptyString('tracking_number')
            ->add('tracking_number', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->decimal('width')
            ->requirePresence('width', 'create')
            ->notEmptyString('width');

        $validator
            ->decimal('height')
            ->requirePresence('height', 'create')
            ->notEmptyString('height');

        $validator
            ->decimal('length')
            ->requirePresence('length', 'create')
            ->notEmptyString('length');

        $validator
            ->decimal('weight')
            ->requirePresence('weight', 'create')
            ->notEmptyString('weight');

        $validator
            ->scalar('dimension_unit')
            ->maxLength('dimension_unit', 10)
            ->notEmptyString('dimension_unit');

        $validator
            ->scalar('weight_unit')
            ->maxLength('weight_unit', 10)
            ->notEmptyString('weight_unit');

        $validator
            ->scalar('contents_description')
            ->requirePresence('contents_description', 'create')
            ->notEmptyString('contents_description');

        $validator
            ->scalar('status')
            ->maxLength('status', 40)
            ->notEmptyString('status');

        $validator
            ->dateTime('received_at')
            ->requirePresence('received_at', 'create')
            ->notEmptyDateTime('received_at');

        $validator
            ->dateTime('shipped_at')
            ->allowEmptyDateTime('shipped_at');

        $validator
            ->dateTime('ready_for_pickup_at')
            ->allowEmptyDateTime('ready_for_pickup_at');

        $validator
            ->dateTime('delivered_at')
            ->allowEmptyDateTime('delivered_at');

        $validator
            ->integer('created_by_user_id')
            ->allowEmptyString('created_by_user_id');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['tracking_number']), ['errorField' => 'tracking_number']);
        $rules->add($rules->existsIn(['client_id'], 'Clients'), ['errorField' => 'client_id']);
        $rules->add($rules->existsIn(['created_by_user_id'], 'CreatedByUsers'), ['errorField' => 'created_by_user_id']);

        return $rules;
    }

    /**
     * Transition package status and write the audit log in one transaction.
     *
     * @param \App\Model\Entity\Package $package Package
     * @param \App\Model\Enum\PackageStatus $newStatus New status
     * @param int|null $changedByUserId User id
     * @param \App\Model\Enum\UserRole|null $changedByRole User role
     * @param string|null $note Audit note
     * @return bool
     */
    public function transitionStatus(
        PackageEntity $package,
        PackageStatus $newStatus,
        ?int $changedByUserId,
        ?UserRole $changedByRole,
        ?string $note = null,
    ): bool {
        $oldStatus = $package->status;
        $this->assertAllowedTransition($oldStatus, $newStatus);

        $package->status = $newStatus;
        $now = DateTime::now();

        if ($newStatus === PackageStatus::Shipped) {
            $package->shipped_at = $now;
        }
        if ($newStatus === PackageStatus::ReadyForPickup) {
            $package->ready_for_pickup_at = $now;
        }
        if ($newStatus === PackageStatus::Delivered) {
            $package->delivered_at = $now;
        }

        return (bool)$this->getConnection()->transactional(function () use (
            $package,
            $oldStatus,
            $newStatus,
            $changedByUserId,
            $changedByRole,
            $note,
        ): bool {
            if (!$this->save($package)) {
                return false;
            }

            return $this->recordStatusHistory($package, $oldStatus, $newStatus, $changedByUserId, $changedByRole, $note);
        });
    }

    /**
     * Write a package status audit row.
     *
     * @param \App\Model\Entity\Package $package Package
     * @param \App\Model\Enum\PackageStatus|null $oldStatus Old status
     * @param \App\Model\Enum\PackageStatus $newStatus New status
     * @param int|null $changedByUserId User id
     * @param \App\Model\Enum\UserRole|null $changedByRole User role
     * @param string|null $note Audit note
     * @return bool
     */
    public function recordStatusHistory(
        PackageEntity $package,
        ?PackageStatus $oldStatus,
        PackageStatus $newStatus,
        ?int $changedByUserId,
        ?UserRole $changedByRole,
        ?string $note = null,
    ): bool {
        $history = $this->PackageStatusHistories->newEntity([
            'package_id' => $package->id,
            'changed_by_user_id' => $changedByUserId,
            'changed_by_role' => $changedByRole?->value,
            'old_status' => $oldStatus?->value,
            'new_status' => $newStatus->value,
            'note' => $note,
        ]);

        return (bool)$this->PackageStatusHistories->save($history);
    }

    /**
     * Assert that a status transition follows the MVP workflow.
     *
     * @param \App\Model\Enum\PackageStatus|null $oldStatus Old status
     * @param \App\Model\Enum\PackageStatus $newStatus New status
     * @return void
     */
    private function assertAllowedTransition(?PackageStatus $oldStatus, PackageStatus $newStatus): void
    {
        $allowed = [
            PackageStatus::ReadyToSend->value => [PackageStatus::PendingInvoiceReview],
            PackageStatus::NeedsReview->value => [PackageStatus::PendingInvoiceReview],
            PackageStatus::PendingInvoiceReview->value => [
                PackageStatus::InvoiceApproved,
                PackageStatus::NeedsReview,
            ],
            PackageStatus::InvoiceApproved->value => [PackageStatus::ShipRequested],
            PackageStatus::ShipRequested->value => [PackageStatus::Shipped],
            PackageStatus::Shipped->value => [
                PackageStatus::ReadyForPickup,
                PackageStatus::Delivered,
            ],
            PackageStatus::ReadyForPickup->value => [PackageStatus::Delivered],
        ];
        $allowedTransitions = $oldStatus === null
            ? [PackageStatus::ReadyToSend]
            : ($allowed[$oldStatus->value] ?? []);

        if (!in_array($newStatus, $allowedTransitions, true)) {
            throw new InvalidArgumentException(__(
                'Package status cannot transition from {0} to {1}.',
                $oldStatus === null ? __('new') : $oldStatus->label(),
                $newStatus->label(),
            ));
        }
    }
}
