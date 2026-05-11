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
            <?= $this->Html->link(__('Edit Account'), ['action' => 'edit'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('Change Password'), ['action' => 'changePassword'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="users view content">
            <h3><?= h($user->name) ?></h3>
            <table>
                <tr>
                    <th><?= __('Name') ?></th>
                    <td><?= h($user->name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Email') ?></th>
                    <td><?= h($user->email) ?></td>
                </tr>
                <tr>
                    <th><?= __('Role') ?></th>
                    <td><?= h($user->role?->label()) ?></td>
                </tr>
                <?php if ($user->hasValue('client')) : ?>
                <tr>
                    <th><?= __('Suite Number') ?></th>
                    <td><?= h($user->client->suite_number) ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th><?= __('Last Login') ?></th>
                    <td><?= h($user->last_login) ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>
