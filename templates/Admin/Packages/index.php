<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Package[]|\Cake\Collection\CollectionInterface $packages
 * @var string $search
 */
?>
<div class="packages index content">
            <?= $this->Html->link(__('New Package'), ['action' => 'add'], ['class' => 'button float-right']) ?>
            <?= $this->Html->link(__('Export CSV'), ['action' => 'exportCsv', '?' => $this->getRequest()->getQueryParams()], ['class' => 'button button-outline float-right']) ?>
            <h3><?= __('Packages') ?></h3>
            <?= $this->element('index_search', [
                'search' => $search,
                'placeholder' => __('Search tracking, client, email, contents, or status'),
            ]) ?>
            <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('client_id') ?></th>
                    <th><?= $this->Paginator->sort('tracking_number') ?></th>
                    <th><?= $this->Paginator->sort('width') ?></th>
                    <th><?= $this->Paginator->sort('height') ?></th>
                    <th><?= $this->Paginator->sort('length') ?></th>
                    <th><?= $this->Paginator->sort('weight') ?></th>
                    <th><?= $this->Paginator->sort('dimension_unit') ?></th>
                    <th><?= $this->Paginator->sort('weight_unit') ?></th>
                    <th><?= $this->Paginator->sort('status') ?></th>
                    <th><?= $this->Paginator->sort('received_at') ?></th>
                    <th><?= $this->Paginator->sort('shipped_at') ?></th>
                    <th><?= $this->Paginator->sort('ready_for_pickup_at') ?></th>
                    <th><?= $this->Paginator->sort('delivered_at') ?></th>
                    <th><?= $this->Paginator->sort('created_by_user_id') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($packages as $package): ?>
                <tr>
                    <td><?= $this->Number->format($package->id) ?></td>
                    <td><?= $package->hasValue('client') ? $this->Html->link($package->client->suite_number, ['controller' => 'Clients', 'action' => 'view', $package->client->id]) : '' ?></td>
                    <td><?= h($package->tracking_number) ?></td>
                    <td><?= $this->Number->format($package->width) ?></td>
                    <td><?= $this->Number->format($package->height) ?></td>
                    <td><?= $this->Number->format($package->length) ?></td>
                    <td><?= $this->Number->format($package->weight) ?></td>
                    <td><?= h($package->dimension_unit?->label()) ?></td>
                    <td><?= h($package->weight_unit?->label()) ?></td>
                    <td><?= h($package->status?->label()) ?></td>
                    <td><?= h($package->received_at) ?></td>
                    <td><?= h($package->shipped_at) ?></td>
                    <td><?= h($package->ready_for_pickup_at) ?></td>
                    <td><?= h($package->delivered_at) ?></td>
                    <td><?= $package->hasValue('created_by_user') ? $this->Html->link($package->created_by_user->name, ['controller' => 'Users', 'action' => 'view', $package->created_by_user->id]) : '' ?></td>
                    <td><?= h($package->created) ?></td>
                    <td><?= h($package->modified) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $package->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $package->id]) ?>
                        <?php if ($package->isShipped()) : ?>
                            <?= $this->Form->postLink(__('Ready for Pickup'), ['action' => 'readyForPickup', $package->id]) ?>
                            <?= $this->Form->postLink(__('Delivered'), ['action' => 'deliver', $package->id]) ?>
                        <?php elseif ($package->isReadyForPickup()) : ?>
                            <?= $this->Form->postLink(__('Delivered'), ['action' => 'deliver', $package->id]) ?>
                        <?php endif; ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $package->id],
                            [
                                'method' => 'delete',
                                'confirm' => __('Are you sure you want to delete record #{0}?', $package->id),
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
