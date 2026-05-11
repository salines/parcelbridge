<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Package $package
 * @var string[]|\Cake\Collection\CollectionInterface $clients
 * @var array<string, string> $dimensionUnits
 * @var array<string, string> $weightUnits
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $package->id],
                ['confirm' => __('Are you sure you want to delete record #{0}?', $package->id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Packages'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="packages form content">
            <?= $this->Form->create($package) ?>
            <fieldset>
                <legend><?= __('Edit Package') ?></legend>
                <?php
                    echo $this->Form->control('client_id', ['options' => $clients]);
                    echo $this->Form->control('tracking_number');
                    echo $this->Form->control('width');
                    echo $this->Form->control('height');
                    echo $this->Form->control('length');
                    echo $this->Form->control('weight');
                    echo $this->Form->control('dimension_unit', ['options' => $dimensionUnits]);
                    echo $this->Form->control('weight_unit', ['options' => $weightUnits]);
                    echo $this->Form->control('contents_description');
                    echo $this->Form->control('received_at');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
