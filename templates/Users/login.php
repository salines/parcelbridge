<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div class="row">
    <div class="column column-50 column-offset-25">
        <div class="users form content">
            <?= $this->Form->create(null, ['url' => ['action' => 'login']]) ?>
            <fieldset>
                <legend><?= __('Login') ?></legend>
                <?= $this->Form->control('email', ['required' => true]) ?>
                <?= $this->Form->control('password', ['required' => true]) ?>
            </fieldset>
            <?= $this->Form->button(__('Login')) ?>
            <?= $this->Form->end() ?>
            <p><?= $this->Html->link(__('Forgot password?'), ['action' => 'forgotPassword']) ?></p>
        </div>
    </div>
</div>
