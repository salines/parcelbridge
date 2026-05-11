<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('My Account'), ['action' => 'profile'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('Edit Account'), ['action' => 'edit'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="users form content">
            <?= $this->Form->create($user) ?>
            <fieldset>
                <legend><?= __('Change Password') ?></legend>
                <?= $this->Form->control('current_password', ['type' => 'password', 'required' => true]) ?>
                <?= $this->Form->control('password', ['type' => 'password', 'required' => true, 'value' => '']) ?>
                <?= $this->Form->control('password_confirm', ['type' => 'password', 'required' => true]) ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
