<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * PackagesShipRequests Model
 *
 * @property \App\Model\Table\PackagesTable&\Cake\ORM\Association\BelongsTo $Packages
 * @property \App\Model\Table\ShipRequestsTable&\Cake\ORM\Association\BelongsTo $ShipRequests
 * @method \App\Model\Entity\PackagesShipRequest newEmptyEntity()
 * @method \App\Model\Entity\PackagesShipRequest newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\PackagesShipRequest[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\PackagesShipRequest get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\PackagesShipRequest findOrCreate(\Cake\ORM\Query\SelectQuery|callable|array $search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\PackagesShipRequest patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\PackagesShipRequest[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\PackagesShipRequest|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\PackagesShipRequest saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\PackagesShipRequest[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\PackagesShipRequest>|false saveMany(iterable $entities, array $options = [])
 * @method \App\Model\Entity\PackagesShipRequest[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\PackagesShipRequest> saveManyOrFail(iterable $entities, array $options = [])
 * @method \App\Model\Entity\PackagesShipRequest[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\PackagesShipRequest>|false deleteMany(iterable $entities, array $options = [])
 * @method \App\Model\Entity\PackagesShipRequest[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\PackagesShipRequest> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @extends \Cake\ORM\Table<array{Timestamp: \Cake\ORM\Behavior\TimestampBehavior}>
 */
class PackagesShipRequestsTable extends Table
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

        $this->setTable('packages_ship_requests');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Packages', [
            'foreignKey' => 'package_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('ShipRequests', [
            'foreignKey' => 'ship_request_id',
            'joinType' => 'INNER',
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
            ->integer('package_id')
            ->notEmptyString('package_id');

        $validator
            ->integer('ship_request_id')
            ->notEmptyString('ship_request_id');

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
        $rules->add($rules->isUnique(['package_id', 'ship_request_id']), ['errorField' => 'package_id', 'message' => __('This combination already exists.')]);
        $rules->add($rules->existsIn(['package_id'], 'Packages'), ['errorField' => 'package_id']);
        $rules->add($rules->existsIn(['ship_request_id'], 'ShipRequests'), ['errorField' => 'ship_request_id']);

        return $rules;
    }
}
