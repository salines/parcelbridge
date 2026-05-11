<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Package as PackageEntity;
use App\Model\Entity\ShipRequest;
use App\Model\Enum\PackageStatus;
use App\Model\Enum\ShipRequestStatus;
use App\Model\Enum\UserRole;
use Cake\Database\Type\EnumType;
use Cake\I18n\DateTime;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use InvalidArgumentException;

/**
 * ShipRequests Model
 *
 * @property \App\Model\Table\ClientsTable&\Cake\ORM\Association\BelongsTo $Clients
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $SubmittedByUsers
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $ProcessedByUsers
 * @property \App\Model\Table\PackagesShipRequestsTable&\Cake\ORM\Association\HasMany $PackagesShipRequests
 * @method \App\Model\Entity\ShipRequest newEmptyEntity()
 * @method \App\Model\Entity\ShipRequest newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\ShipRequest[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ShipRequest get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ShipRequest findOrCreate(\Cake\ORM\Query\SelectQuery|callable|array $search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\ShipRequest patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\ShipRequest[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ShipRequest|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\ShipRequest saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\ShipRequest[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ShipRequest>|false saveMany(iterable $entities, array $options = [])
 * @method \App\Model\Entity\ShipRequest[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ShipRequest> saveManyOrFail(iterable $entities, array $options = [])
 * @method \App\Model\Entity\ShipRequest[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ShipRequest>|false deleteMany(iterable $entities, array $options = [])
 * @method \App\Model\Entity\ShipRequest[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ShipRequest> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Search\Model\Behavior\SearchBehavior
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @extends \Cake\ORM\Table<array{Search: \Search\Model\Behavior\SearchBehavior, Timestamp: \Cake\ORM\Behavior\TimestampBehavior}>
 */
class ShipRequestsTable extends Table
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

        $this->setTable('ship_requests');
        $this->setDisplayField('status');
        $this->setPrimaryKey('id');

        $this->getSchema()->setColumnType('status', EnumType::from(ShipRequestStatus::class));

        $this->addBehavior('Timestamp');
        $this->addBehavior('Search.Search');

        $this->belongsTo('Clients', [
            'foreignKey' => 'client_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('SubmittedByUsers', [
            'foreignKey' => 'submitted_by_user_id',
            'className' => 'Users',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('ProcessedByUsers', [
            'foreignKey' => 'processed_by_user_id',
            'className' => 'Users',
        ]);
        $this->hasMany('PackagesShipRequests', [
            'foreignKey' => 'ship_request_id',
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
                        'ShipRequests.processing_reference LIKE' => $like,
                        'ShipRequests.notes LIKE' => $like,
                        'Clients.suite_number LIKE' => $like,
                        'SubmittedByUsers.name LIKE' => $like,
                        'SubmittedByUsers.email LIKE' => $like,
                        'ProcessedByUsers.name LIKE' => $like,
                        'ProcessedByUsers.email LIKE' => $like,
                    ];

                    if (ctype_digit($search)) {
                        $conditions['ShipRequests.id'] = (int)$search;
                    }

                    $normalizedStatus = strtolower(str_replace(' ', '_', $search));
                    foreach (ShipRequestStatus::cases() as $status) {
                        if (
                            $normalizedStatus === $status->value
                            || str_contains(strtolower($status->label()), strtolower($search))
                        ) {
                            $conditions['ShipRequests.status'] = $status->value;
                            break;
                        }
                    }

                    $query
                        ->leftJoinWith('Clients')
                        ->leftJoinWith('SubmittedByUsers')
                        ->leftJoinWith('ProcessedByUsers')
                        ->where(['OR' => $conditions])
                        ->distinct(['ShipRequests.id']);

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
            ->integer('submitted_by_user_id')
            ->notEmptyString('submitted_by_user_id');

        $validator
            ->integer('processed_by_user_id')
            ->allowEmptyString('processed_by_user_id');

        $validator
            ->scalar('status')
            ->maxLength('status', 40)
            ->notEmptyString('status');

        $validator
            ->scalar('processing_reference')
            ->maxLength('processing_reference', 120)
            ->allowEmptyString('processing_reference');

        $validator
            ->dateTime('submitted_at')
            ->requirePresence('submitted_at', 'create')
            ->notEmptyDateTime('submitted_at');

        $validator
            ->dateTime('processed_at')
            ->allowEmptyDateTime('processed_at');

        $validator
            ->scalar('notes')
            ->allowEmptyString('notes');

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
        $rules->add($rules->existsIn(['client_id'], 'Clients'), ['errorField' => 'client_id']);
        $rules->add($rules->existsIn(['submitted_by_user_id'], 'SubmittedByUsers'), ['errorField' => 'submitted_by_user_id']);
        $rules->add($rules->existsIn(['processed_by_user_id'], 'ProcessedByUsers'), ['errorField' => 'processed_by_user_id']);

        return $rules;
    }

    /**
     * Submit a client ship request for approved packages.
     *
     * @param int $clientId Client id
     * @param int $submittedByUserId Submitted by user id
     * @param array<int|string> $packageIds Package ids
     * @return \App\Model\Entity\ShipRequest|false
     */
    public function submitForPackages(int $clientId, int $submittedByUserId, array $packageIds): ShipRequest|false
    {
        $packageIds = array_values(array_unique(array_map('intval', array_filter($packageIds))));
        if ($packageIds === []) {
            throw new InvalidArgumentException(__('Select at least one package.'));
        }

        $packagesTable = $this->PackagesShipRequests->Packages;
        $packages = $packagesTable->find()
            ->where([
                'Packages.id IN' => $packageIds,
                'Packages.client_id' => $clientId,
                'Packages.status' => PackageStatus::InvoiceApproved->value,
            ])
            ->all();

        if ($packages->count() !== count($packageIds)) {
            throw new InvalidArgumentException(__('Only approved packages can be submitted.'));
        }

        return $this->getConnection()->transactional(function () use (
            $clientId,
            $submittedByUserId,
            $packagesTable,
            $packages,
        ): ShipRequest|false {
            $packageList = [];
            foreach ($packages as $package) {
                if (!$package instanceof PackageEntity) {
                    return false;
                }
                $packageList[] = $package;
            }
            $shipRequest = $this->newEntity([
                'client_id' => $clientId,
                'submitted_by_user_id' => $submittedByUserId,
                'status' => ShipRequestStatus::Submitted->value,
                'submitted_at' => DateTime::now(),
            ]);

            $shipRequest = $this->save($shipRequest);
            if (!$shipRequest) {
                return false;
            }

            $joins = [];
            foreach ($packageList as $package) {
                $joins[] = $this->PackagesShipRequests->newEntity([
                    'ship_request_id' => $shipRequest->id,
                    'package_id' => $package->id,
                ]);
            }

            if (!$this->PackagesShipRequests->saveMany($joins)) {
                return false;
            }

            foreach ($packageList as $package) {
                $packagesTable->transitionStatus(
                    $package,
                    PackageStatus::ShipRequested,
                    $submittedByUserId,
                    UserRole::Client,
                    __('Client submitted ship request.'),
                );
            }

            return $shipRequest;
        });
    }
}
