<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Client[]|\Cake\Collection\CollectionInterface $clients
 * @var string $search
 */
?>
<div class="clients index content">
            <?= $this->Html->link(__('New Client'), ['action' => 'add'], ['class' => 'button float-right']) ?>
            <h3><?= __('Clients') ?></h3>
            <?= $this->element('index_search', [
                'search' => $search,
                'placeholder' => __('Search suite, client, email, phone, address, city, or country'),
            ]) ?>
            <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('user_id') ?></th>
                    <th><?= $this->Paginator->sort('suite_number') ?></th>
                    <th><?= __('Packages') ?></th>
                    <th><?= $this->Paginator->sort('phone') ?></th>
                    <th><?= $this->Paginator->sort('address_line_1') ?></th>
                    <th><?= $this->Paginator->sort('address_line_2') ?></th>
                    <th><?= $this->Paginator->sort('city') ?></th>
                    <th><?= $this->Paginator->sort('country') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clients as $client): ?>
                <tr>
                    <td><?= $this->Number->format($client->id) ?></td>
                    <td><?= $client->hasValue('user') ? $this->Html->link($client->user->name, ['controller' => 'Users', 'action' => 'view', $client->user->id]) : '' ?></td>
                    <td><?= h($client->suite_number) ?></td>
                    <td><?= $this->Number->format((int)($client->package_count ?? 0)) ?></td>
                    <td><?= h($client->phone) ?></td>
                    <td><?= h($client->address_line_1) ?></td>
                    <td><?= h($client->address_line_2) ?></td>
                    <td><?= h($client->city) ?></td>
                    <td><?= h($client->country) ?></td>
                    <td><?= h($client->created) ?></td>
                    <td><?= h($client->modified) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $client->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $client->id]) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $client->id],
                            [
                                'method' => 'delete',
                                'confirm' => __('Are you sure you want to delete record #{0}?', $client->id),
                            ]
                        ) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('First')) ?>
            <?= $this->Paginator->prev('< ' . __('Previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('Next') . ' >') ?>
            <?= $this->Paginator->last(__('Last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
    </div>
</div>
