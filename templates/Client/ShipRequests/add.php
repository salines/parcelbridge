<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ShipRequest $shipRequest
 * @var \App\Model\Entity\Package[]|\Cake\Collection\CollectionInterface $packages
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('My Ship Requests'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="shipRequests form content">
            <?= $this->Form->create($shipRequest) ?>
            <fieldset>
                <legend><?= __('Create Ship Request') ?></legend>
                <?php foreach ($packages as $package) : ?>
                    <?= $this->Form->control('packages._ids[]', [
                        'type' => 'checkbox',
                        'value' => $package->id,
                        'hiddenField' => false,
                        'label' => sprintf('%s - %s', $package->tracking_number, $package->contents_description),
                    ]) ?>
                <?php endforeach; ?>
                <?php if (count($packages) === 0) : ?>
                    <p><?= __('No approved packages are available for shipment.') ?></p>
                <?php endif; ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
