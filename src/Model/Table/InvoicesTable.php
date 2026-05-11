<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Enum\InvoiceReviewStatus;
use Cake\Database\Type\EnumType;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Invoices Model
 *
 * @property \App\Model\Table\PackagesTable&\Cake\ORM\Association\BelongsTo $Packages
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $UploadedByUsers
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $ReviewedByUsers
 * @method \App\Model\Entity\Invoice newEmptyEntity()
 * @method \App\Model\Entity\Invoice newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Invoice[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Invoice get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Invoice findOrCreate(\Cake\ORM\Query\SelectQuery|callable|array $search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Invoice patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Invoice[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Invoice|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Invoice saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Invoice[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Invoice>|false saveMany(iterable $entities, array $options = [])
 * @method \App\Model\Entity\Invoice[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Invoice> saveManyOrFail(iterable $entities, array $options = [])
 * @method \App\Model\Entity\Invoice[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Invoice>|false deleteMany(iterable $entities, array $options = [])
 * @method \App\Model\Entity\Invoice[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Invoice> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Search\Model\Behavior\SearchBehavior
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @extends \Cake\ORM\Table<array{Search: \Search\Model\Behavior\SearchBehavior, Timestamp: \Cake\ORM\Behavior\TimestampBehavior}>
 */
class InvoicesTable extends Table
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

        $this->setTable('invoices');
        $this->setDisplayField('file_path');
        $this->setPrimaryKey('id');

        $this->getSchema()->setColumnType('review_status', EnumType::from(InvoiceReviewStatus::class));

        $this->addBehavior('Timestamp');
        $this->addBehavior('Search.Search');

        $this->belongsTo('Packages', [
            'foreignKey' => 'package_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('UploadedByUsers', [
            'foreignKey' => 'uploaded_by_user_id',
            'className' => 'Users',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('ReviewedByUsers', [
            'foreignKey' => 'reviewed_by_user_id',
            'className' => 'Users',
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
                        'Invoices.original_filename LIKE' => $like,
                        'Invoices.mime_type LIKE' => $like,
                        'Invoices.admin_notes LIKE' => $like,
                        'Packages.tracking_number LIKE' => $like,
                        'UploadedByUsers.name LIKE' => $like,
                        'UploadedByUsers.email LIKE' => $like,
                        'ReviewedByUsers.name LIKE' => $like,
                        'ReviewedByUsers.email LIKE' => $like,
                    ];

                    $normalizedStatus = strtolower(str_replace(' ', '_', $search));
                    foreach (InvoiceReviewStatus::cases() as $status) {
                        if (
                            $normalizedStatus === $status->value
                            || str_contains(strtolower($status->label()), strtolower($search))
                        ) {
                            $conditions['Invoices.review_status'] = $status->value;
                            break;
                        }
                    }

                    $query
                        ->leftJoinWith('Packages')
                        ->leftJoinWith('UploadedByUsers')
                        ->leftJoinWith('ReviewedByUsers')
                        ->where(['OR' => $conditions])
                        ->distinct(['Invoices.id']);

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
            ->integer('package_id')
            ->notEmptyString('package_id')
            ->add('package_id', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->integer('uploaded_by_user_id')
            ->notEmptyString('uploaded_by_user_id');

        $validator
            ->integer('reviewed_by_user_id')
            ->allowEmptyString('reviewed_by_user_id');

        $validator
            ->scalar('file_path')
            ->maxLength('file_path', 255)
            ->requirePresence('file_path', 'create')
            ->notEmptyString('file_path');

        $validator
            ->scalar('original_filename')
            ->maxLength('original_filename', 255)
            ->requirePresence('original_filename', 'create')
            ->notEmptyString('original_filename');

        $validator
            ->scalar('mime_type')
            ->maxLength('mime_type', 120)
            ->requirePresence('mime_type', 'create')
            ->notEmptyString('mime_type');

        $validator
            ->integer('file_size')
            ->requirePresence('file_size', 'create')
            ->notEmptyString('file_size');

        $validator
            ->scalar('review_status')
            ->maxLength('review_status', 40)
            ->notEmptyString('review_status');

        $validator
            ->scalar('admin_notes')
            ->allowEmptyString('admin_notes');

        $validator
            ->dateTime('uploaded_at')
            ->requirePresence('uploaded_at', 'create')
            ->notEmptyDateTime('uploaded_at');

        $validator
            ->dateTime('reviewed_at')
            ->allowEmptyDateTime('reviewed_at');

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
        $rules->add($rules->isUnique(['package_id']), ['errorField' => 'package_id']);
        $rules->add($rules->existsIn(['package_id'], 'Packages'), ['errorField' => 'package_id']);
        $rules->add($rules->existsIn(['uploaded_by_user_id'], 'UploadedByUsers'), ['errorField' => 'uploaded_by_user_id']);
        $rules->add($rules->existsIn(['reviewed_by_user_id'], 'ReviewedByUsers'), ['errorField' => 'reviewed_by_user_id']);

        return $rules;
    }
}
