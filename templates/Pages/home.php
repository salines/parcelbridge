<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div class="row">
    <div class="column column-50 column-offset-25">
        <div class="pages content">
            <h3><?= __('Login') ?></h3>
            <p><?= __('Continue to the ParcelBridge login page.') ?></p>
            <?= $this->Html->link(__('Login'), ['controller' => 'Users', 'action' => 'login'], ['class' => 'button']) ?>
        </div>
    </div>
</div>
