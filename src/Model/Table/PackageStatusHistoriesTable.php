<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Enum\PackageStatus;
use App\Model\Enum\UserRole;
use Cake\Database\Type\EnumType;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * PackageStatusHistories Model
 *
 * @property \App\Model\Table\PackagesTable&\Cake\ORM\Association\BelongsTo $Packages
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $ChangedByUsers
 * @method \App\Model\Entity\PackageStatusHistory newEmptyEntity()
 * @method \App\Model\Entity\PackageStatusHistory newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\PackageStatusHistory[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\PackageStatusHistory get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\PackageStatusHistory findOrCreate(\Cake\ORM\Query\SelectQuery|callable|array $search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\PackageStatusHistory patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\PackageStatusHistory[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\PackageStatusHistory|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\PackageStatusHistory saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\PackageStatusHistory[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\PackageStatusHistory>|false saveMany(iterable $entities, array $options = [])
 * @method \App\Model\Entity\PackageStatusHistory[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\PackageStatusHistory> saveManyOrFail(iterable $entities, array $options = [])
 * @method \App\Model\Entity\PackageStatusHistory[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\PackageStatusHistory>|false deleteMany(iterable $entities, array $options = [])
 * @method \App\Model\Entity\PackageStatusHistory[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\PackageStatusHistory> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @extends \Cake\ORM\Table<array{Timestamp: \Cake\ORM\Behavior\TimestampBehavior}>
 */
class PackageStatusHistoriesTable extends Table
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

        $this->setTable('package_status_histories');
        $this->setDisplayField('new_status');
        $this->setPrimaryKey('id');

        $this->getSchema()->setColumnType('changed_by_role', EnumType::from(UserRole::class));
        $this->getSchema()->setColumnType('old_status', EnumType::from(PackageStatus::class));
        $this->getSchema()->setColumnType('new_status', EnumType::from(PackageStatus::class));

        $this->addBehavior('Timestamp');

        $this->belongsTo('Packages', [
            'foreignKey' => 'package_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('ChangedByUsers', [
            'foreignKey' => 'changed_by_user_id',
            'className' => 'Users',
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
            ->integer('changed_by_user_id')
            ->allowEmptyString('changed_by_user_id');

        $validator
            ->scalar('changed_by_role')
            ->maxLength('changed_by_role', 20)
            ->allowEmptyString('changed_by_role');

        $validator
            ->scalar('old_status')
            ->maxLength('old_status', 40)
            ->allowEmptyString('old_status');

        $validator
            ->scalar('new_status')
            ->maxLength('new_status', 40)
            ->requirePresence('new_status', 'create')
            ->notEmptyString('new_status');

        $validator
            ->scalar('note')
            ->allowEmptyString('note');

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
        $rules->add($rules->existsIn(['package_id'], 'Packages'), ['errorField' => 'package_id']);
        $rules->add($rules->existsIn(['changed_by_user_id'], 'ChangedByUsers'), ['errorField' => 'changed_by_user_id']);

        return $rules;
    }
}
