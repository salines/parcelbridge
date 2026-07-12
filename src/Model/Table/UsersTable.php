<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Enum\UserRole;
use Cake\Database\Type\EnumType;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Users Model
 *
 * @property \App\Model\Table\ClientsTable&\Cake\ORM\Association\HasOne $Clients
 * @method \App\Model\Entity\User newEmptyEntity()
 * @method \App\Model\Entity\User newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\User[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\User get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\User findOrCreate(\Cake\ORM\Query\SelectQuery|callable|array $search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\User patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\User[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\User|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\User saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\User>|false saveMany(iterable $entities, array $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\User> saveManyOrFail(iterable $entities, array $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\User>|false deleteMany(iterable $entities, array $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\User> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Search\Model\Behavior\SearchBehavior
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @extends \Cake\ORM\Table<array{Search: \Search\Model\Behavior\SearchBehavior, Timestamp: \Cake\ORM\Behavior\TimestampBehavior}, \App\Model\Entity\User>
 */
class UsersTable extends Table
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

        $this->setTable('users');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->getSchema()->setColumnType('role', EnumType::from(UserRole::class));

        $this->addBehavior('Timestamp');
        $this->addBehavior('Search.Search');

        $this->hasOne('Clients', [
            'foreignKey' => 'user_id',
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
                        'Users.name LIKE' => $like,
                        'Users.email LIKE' => $like,
                        'Clients.suite_number LIKE' => $like,
                    ];

                    $normalizedRole = strtolower(str_replace(' ', '_', $search));
                    foreach (UserRole::cases() as $role) {
                        if (
                            $normalizedRole === $role->value
                            || str_contains(strtolower($role->label()), strtolower($search))
                        ) {
                            $conditions['Users.role'] = $role->value;
                            break;
                        }
                    }

                    $query
                        ->leftJoinWith('Clients')
                        ->where(['OR' => $conditions])
                        ->distinct(['Users.id']);

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
            ->scalar('name')
            ->maxLength('name', 160)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmptyString('email')
            ->add('email', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('password')
            ->minLength('password', 8)
            ->maxLength('password', 255)
            ->requirePresence('password', 'create')
            ->notEmptyString('password');

        $validator
            ->scalar('role')
            ->maxLength('role', 20)
            ->requirePresence('role', 'create')
            ->notEmptyString('role');

        $validator
            ->boolean('active')
            ->notEmptyString('active');

        $validator
            ->dateTime('last_login')
            ->allowEmptyDateTime('last_login');

        return $validator;
    }

    /**
     * Finder used by authentication so inactive users cannot log in.
     *
     * @param \Cake\ORM\Query\SelectQuery $query Query
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findActive(SelectQuery $query): SelectQuery
    {
        return $query
            ->contain(['Clients'])
            ->where(['Users.active' => true]);
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
        $rules->add($rules->isUnique(['email']), ['errorField' => 'email']);

        return $rules;
    }
}
