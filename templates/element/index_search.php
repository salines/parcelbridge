<?php
/**
 * @var \App\View\AppView $this
 * @var string $search
 * @var string $placeholder
 */
?>
<?= $this->Form->create(null, ['type' => 'get', 'valueSources' => ['query']]) ?>
<div class="row">
    <div class="column">
        <?= $this->Form->control('q', [
            'label' => false,
            'placeholder' => $placeholder,
            'value' => $search,
        ]) ?>
    </div>
    <div class="column column-20">
        <?= $this->Form->button(__('Search')) ?>
        <?= $this->Html->link(__('Reset'), ['action' => 'index'], ['class' => 'button button-outline']) ?>
    </div>
</div>
<?= $this->Form->end() ?>
