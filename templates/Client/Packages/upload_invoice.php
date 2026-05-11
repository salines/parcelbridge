<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Package $package
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Back to Package'), ['action' => 'view', $package->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('My Packages'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="packages form content">
            <h3><?= h($package->tracking_number) ?></h3>
            <?php if (!empty($package->invoice?->admin_notes)) : ?>
                <blockquote><?= h($package->invoice->admin_notes) ?></blockquote>
            <?php endif; ?>
            <?= $this->Form->create(null, ['type' => 'file']) ?>
            <fieldset>
                <legend><?= __('Upload Invoice') ?></legend>
                <?= $this->Form->control('invoice_file', [
                    'type' => 'file',
                    'label' => __('Invoice File'),
                    'required' => true,
                    'accept' => '.pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png',
                ]) ?>
            </fieldset>
            <?= $this->Form->button(__('Upload')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
