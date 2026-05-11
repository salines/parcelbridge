<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div class="row">
    <div class="column column-50 column-offset-25">
        <div class="users form content">
            <?= $this->Form->create() ?>
            <fieldset>
                <legend><?= __('Forgot Password') ?></legend>
                <?= $this->Form->control('email', ['required' => true]) ?>
            </fieldset>
            <?= $this->Form->button(__('Send Reset Link')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
