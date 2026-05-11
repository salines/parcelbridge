<?php
/**
 * @var \App\View\AppView $this
 * @var string $token
 */
?>
<div class="row">
    <div class="column column-50 column-offset-25">
        <div class="users form content">
            <?= $this->Form->create(null, ['url' => ['action' => 'resetPassword', $token]]) ?>
            <fieldset>
                <legend><?= __('Reset Password') ?></legend>
                <?= $this->Form->control('password', ['type' => 'password', 'required' => true]) ?>
                <?= $this->Form->control('password_confirm', ['type' => 'password', 'required' => true]) ?>
            </fieldset>
            <?= $this->Form->button(__('Reset Password')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
