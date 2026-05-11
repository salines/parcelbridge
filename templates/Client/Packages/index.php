<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Package[]|\Cake\Collection\CollectionInterface $packages
 * @var string $search
 */
?>
<div class="packages index content">
    <?= $this->Html->link(__('Export CSV'), ['action' => 'exportCsv', '?' => $this->getRequest()->getQueryParams()], ['class' => 'button button-outline float-right']) ?>
    <h3><?= __('My Packages') ?></h3>
    <?= $this->element('index_search', [
        'search' => $search,
        'placeholder' => __('Search tracking number, contents, or status'),
    ]) ?>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('tracking_number') ?></th>
                    <th><?= $this->Paginator->sort('weight') ?></th>
                    <th><?= $this->Paginator->sort('status') ?></th>
                    <th><?= $this->Paginator->sort('received_at') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($packages as $package): ?>
                <tr>
                    <td><?= h($package->tracking_number) ?></td>
                    <td><?= $this->Number->format($package->weight) ?> <?= h($package->weight_unit?->label()) ?></td>
                    <td><?= h($package->status?->label()) ?></td>
                    <td><?= h($package->received_at) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $package->id]) ?>
                        <?php if ($package->canUploadInvoice()) : ?>
                            <?= $this->Html->link(__('Upload Invoice'), ['action' => 'uploadInvoice', $package->id]) ?>
                        <?php endif; ?>
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
