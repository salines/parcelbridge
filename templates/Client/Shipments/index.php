<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Package> $shipments
 * @var string $search
 */
?>
<div class="shipments index content">
    <h3><?= __('Shipment Status') ?></h3>
    <?= $this->element('index_search', [
        'search' => $search,
        'placeholder' => __('Search tracking number, contents, or status'),
    ]) ?>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('tracking_number') ?></th>
                    <th><?= $this->Paginator->sort('status') ?></th>
                    <th><?= $this->Paginator->sort('shipped_at') ?></th>
                    <th><?= $this->Paginator->sort('ready_for_pickup_at') ?></th>
                    <th><?= $this->Paginator->sort('delivered_at') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($shipments as $package): ?>
                <tr>
                    <td><?= h($package->tracking_number) ?></td>
                    <td><?= h($package->status?->label()) ?></td>
                    <td><?= h($package->shipped_at) ?></td>
                    <td><?= h($package->ready_for_pickup_at) ?></td>
                    <td><?= h($package->delivered_at) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), [
                            'controller' => 'Packages',
                            'action' => 'view',
                            $package->id,
                        ]) ?>
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
