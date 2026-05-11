<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ShipRequest[]|\Cake\Collection\CollectionInterface $shipRequests
 * @var string $search
 */
?>
<div class="shipRequests index content">
    <?= $this->Html->link(__('New Ship Request'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <?= $this->Html->link(__('Export CSV'), ['action' => 'exportCsv', '?' => $this->getRequest()->getQueryParams()], ['class' => 'button button-outline float-right']) ?>
    <h3><?= __('Ship Requests') ?></h3>
    <?= $this->element('index_search', [
        'search' => $search,
        'placeholder' => __('Search request id, reference, notes, or status'),
    ]) ?>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('status') ?></th>
                    <th><?= $this->Paginator->sort('processing_reference') ?></th>
                    <th><?= $this->Paginator->sort('submitted_at') ?></th>
                    <th><?= $this->Paginator->sort('processed_at') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($shipRequests as $shipRequest): ?>
                <tr>
                    <td><?= $this->Number->format($shipRequest->id) ?></td>
                    <td><?= h($shipRequest->status?->label()) ?></td>
                    <td><?= h($shipRequest->processing_reference) ?></td>
                    <td><?= h($shipRequest->submitted_at) ?></td>
                    <td><?= h($shipRequest->processed_at) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $shipRequest->id]) ?>
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
