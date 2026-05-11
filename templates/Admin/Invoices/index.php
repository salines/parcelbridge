<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Invoice[]|\Cake\Collection\CollectionInterface $invoices
 * @var string $search
 */
?>
<div class="invoices index content">
            <h3><?= __('Invoice Review') ?></h3>
            <?= $this->element('index_search', [
                'search' => $search,
                'placeholder' => __('Search file, tracking number, uploader, reviewer, notes, or status'),
            ]) ?>
            <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('package_id') ?></th>
                    <th><?= $this->Paginator->sort('uploaded_by_user_id') ?></th>
                    <th><?= $this->Paginator->sort('original_filename') ?></th>
                    <th><?= $this->Paginator->sort('mime_type') ?></th>
                    <th><?= $this->Paginator->sort('file_size') ?></th>
                    <th><?= $this->Paginator->sort('review_status') ?></th>
                    <th><?= $this->Paginator->sort('uploaded_at') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($invoices as $invoice): ?>
                <tr>
                    <td><?= $invoice->hasValue('package') ? $this->Html->link($invoice->package->tracking_number, ['controller' => 'Packages', 'action' => 'view', $invoice->package->id]) : '' ?></td>
                    <td><?= $invoice->hasValue('uploaded_by_user') ? $this->Html->link($invoice->uploaded_by_user->name, ['controller' => 'Users', 'action' => 'view', $invoice->uploaded_by_user->id]) : '' ?></td>
                    <td><?= h($invoice->original_filename) ?></td>
                    <td><?= h($invoice->mime_type) ?></td>
                    <td><?= $this->Number->format($invoice->file_size) ?></td>
                    <td><?= h($invoice->review_status?->label()) ?></td>
                    <td><?= h($invoice->uploaded_at) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $invoice->id]) ?>
                        <?= $this->Html->link(__('Open File'), ['action' => 'viewFile', $invoice->id], ['target' => '_blank', 'rel' => 'noopener']) ?>
                        <?= $this->Html->link(__('Download'), ['action' => 'downloadFile', $invoice->id]) ?>
                        <?= $this->Form->postLink(__('Approve'), ['action' => 'approve', $invoice->id]) ?>
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
