<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Clients Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\PackagesTable&\Cake\ORM\Association\HasMany $Packages
 * @property \App\Model\Table\ShipRequestsTable&\Cake\ORM\Association\HasMany $ShipRequests
 * @method \App\Model\Entity\Client newEmptyEntity()
 * @method \App\Model\Entity\Client newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Client[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Client get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Client findOrCreate(\Cake\ORM\Query\SelectQuery|callable|array $search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Client patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Client[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Client|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Client saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Client[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Client>|false saveMany(iterable $entities, array $options = [])
 * @method \App\Model\Entity\Client[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Client> saveManyOrFail(iterable $entities, array $options = [])
 * @method \App\Model\Entity\Client[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Client>|false deleteMany(iterable $entities, array $options = [])
 * @method \App\Model\Entity\Client[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Client> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Search\Model\Behavior\SearchBehavior
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @extends \Cake\ORM\Table<array{Search: \Search\Model\Behavior\SearchBehavior, Timestamp: \Cake\ORM\Behavior\TimestampBehavior}, \App\Model\Entity\Client>
 */
class ClientsTable extends Table
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

        $this->setTable('clients');
        $this->setDisplayField('suite_number');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Search.Search');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('Packages', [
            'foreignKey' => 'client_id',
        ]);
        $this->hasMany('ShipRequests', [
            'foreignKey' => 'client_id',
        ]);

        $this->getBehavior('Search')->searchManager()
            ->callback('q', [
                'callback' => function (SelectQuery $query, array $args): bool {
                    $search = trim((string)($args['q'] ?? ''));
                    if ($search === '') {
                        return false;
                    }

                    $like = '%' . $search . '%';
                    $query
                        ->leftJoinWith('Users')
                        ->where(['OR' => [
                            'Clients.suite_number LIKE' => $like,
                            'Clients.phone LIKE' => $like,
                            'Clients.address_line_1 LIKE' => $like,
                            'Clients.address_line_2 LIKE' => $like,
                            'Clients.city LIKE' => $like,
                            'Clients.country LIKE' => $like,
                            'Users.name LIKE' => $like,
                            'Users.email LIKE' => $like,
                        ]])
                        ->distinct(['Clients.id']);

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
            ->integer('user_id')
            ->notEmptyString('user_id')
            ->add('user_id', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('suite_number')
            ->maxLength('suite_number', 40)
            ->requirePresence('suite_number', 'create')
            ->notEmptyString('suite_number')
            ->add('suite_number', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('phone')
            ->maxLength('phone', 40)
            ->allowEmptyString('phone');

        $validator
            ->scalar('address_line_1')
            ->maxLength('address_line_1', 255)
            ->allowEmptyString('address_line_1');

        $validator
            ->scalar('address_line_2')
            ->maxLength('address_line_2', 255)
            ->allowEmptyString('address_line_2');

        $validator
            ->scalar('city')
            ->maxLength('city', 120)
            ->allowEmptyString('city');

        $validator
            ->scalar('country')
            ->maxLength('country', 120)
            ->notEmptyString('country');

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
        $rules->add($rules->isUnique(['user_id']), ['errorField' => 'user_id']);
        $rules->add($rules->isUnique(['suite_number']), ['errorField' => 'suite_number']);
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }
}
