<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Invoice $invoice
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Review Queue'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('Open File'), ['action' => 'viewFile', $invoice->id], ['class' => 'side-nav-item', 'target' => '_blank', 'rel' => 'noopener']) ?>
            <?= $this->Html->link(__('Download File'), ['action' => 'downloadFile', $invoice->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Approve'), ['action' => 'approve', $invoice->id], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="invoices view content">
            <h3><?= h($invoice->original_filename) ?></h3>
            <table>
                <tr>
                    <th><?= __('Package') ?></th>
                    <td><?= $invoice->hasValue('package') ? $this->Html->link($invoice->package->tracking_number, ['controller' => 'Packages', 'action' => 'view', $invoice->package->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Uploaded By') ?></th>
                    <td><?= $invoice->hasValue('uploaded_by_user') ? $this->Html->link($invoice->uploaded_by_user->name, ['controller' => 'Users', 'action' => 'view', $invoice->uploaded_by_user->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Reviewed By') ?></th>
                    <td><?= $invoice->hasValue('reviewed_by_user') ? $this->Html->link($invoice->reviewed_by_user->name, ['controller' => 'Users', 'action' => 'view', $invoice->reviewed_by_user->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('File Path') ?></th>
                    <td><?= h($invoice->file_path) ?></td>
                </tr>
                <tr>
                    <th><?= __('Original File Name') ?></th>
                    <td><?= h($invoice->original_filename) ?></td>
                </tr>
                <tr>
                    <th><?= __('MIME Type') ?></th>
                    <td><?= h($invoice->mime_type) ?></td>
                </tr>
                <tr>
                    <th><?= __('ID') ?></th>
                    <td><?= $this->Number->format($invoice->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('File Size') ?></th>
                    <td><?= $this->Number->format($invoice->file_size) ?></td>
                </tr>
                <tr>
                    <th><?= __('Review Status') ?></th>
                    <td><?= h($invoice->review_status?->label()) ?></td>
                </tr>
                <tr>
                    <th><?= __('Uploaded At') ?></th>
                    <td><?= h($invoice->uploaded_at) ?></td>
                </tr>
                <tr>
                    <th><?= __('Reviewed At') ?></th>
                    <td><?= h($invoice->reviewed_at) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($invoice->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($invoice->modified) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('Admin Notes') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($invoice->admin_notes)); ?>
                </blockquote>
            </div>
            <?= $this->Form->create(null, ['url' => ['action' => 'needsReview', $invoice->id]]) ?>
            <fieldset>
                <legend><?= __('Flag as Needs Review') ?></legend>
                <?= $this->Form->control('admin_notes', ['type' => 'textarea', 'required' => true]) ?>
            </fieldset>
            <?= $this->Form->button(__('Flag')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
