<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         0.10.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @var \App\View\AppView $this
 * @var string|null $currentUserRole
 */

$cakeDescription = 'ParcelBridge';
?>
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?= $cakeDescription ?>:
        <?= $this->fetch('title') ?>
    </title>
    <?= $this->Html->meta('icon') ?>

    <?= $this->Html->css(['normalize.min', 'milligram.min', 'fonts', 'cake']) ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body>
    <nav class="top-nav container">
        <div class="top-nav-title">
            <a href="<?= $this->Url->build('/') ?>"><span>Parcel</span>Bridge</a>
        </div>
        <div class="top-nav-links">
            <?php if ($currentUserRole === 'admin') : ?>
                <?= $this->Html->link(__('Dashboard'), ['prefix' => 'Admin', 'controller' => 'Dashboard', 'action' => 'index']) ?>
                <?= $this->Html->link(__('Users'), ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'index']) ?>
                <?= $this->Html->link(__('Clients'), ['prefix' => 'Admin', 'controller' => 'Clients', 'action' => 'index']) ?>
                <?= $this->Html->link(__('Packages'), ['prefix' => 'Admin', 'controller' => 'Packages', 'action' => 'index']) ?>
                <?= $this->Html->link(__('Invoices'), ['prefix' => 'Admin', 'controller' => 'Invoices', 'action' => 'index']) ?>
                <?= $this->Html->link(__('Ship Requests'), ['prefix' => 'Admin', 'controller' => 'ShipRequests', 'action' => 'index']) ?>
                <?= $this->Html->link(__('My Account'), ['prefix' => false, 'controller' => 'Users', 'action' => 'profile']) ?>
                <?= $this->Html->link(__('Change Password'), ['prefix' => false, 'controller' => 'Users', 'action' => 'changePassword']) ?>
                <?= $this->Html->link(__('Logout'), ['prefix' => false, 'controller' => 'Users', 'action' => 'logout']) ?>
            <?php elseif ($currentUserRole === 'client') : ?>
                <?= $this->Html->link(__('Dashboard'), ['prefix' => 'Client', 'controller' => 'Dashboard', 'action' => 'index']) ?>
                <?= $this->Html->link(__('Packages'), ['prefix' => 'Client', 'controller' => 'Packages', 'action' => 'index']) ?>
                <?= $this->Html->link(__('Invoices'), ['prefix' => 'Client', 'controller' => 'Invoices', 'action' => 'index']) ?>
                <?= $this->Html->link(__('Ship Requests'), ['prefix' => 'Client', 'controller' => 'ShipRequests', 'action' => 'index']) ?>
                <?= $this->Html->link(__('Shipments'), ['prefix' => 'Client', 'controller' => 'Shipments', 'action' => 'index']) ?>
                <?= $this->Html->link(__('My Account'), ['prefix' => false, 'controller' => 'Users', 'action' => 'profile']) ?>
                <?= $this->Html->link(__('Change Password'), ['prefix' => false, 'controller' => 'Users', 'action' => 'changePassword']) ?>
                <?= $this->Html->link(__('Logout'), ['prefix' => false, 'controller' => 'Users', 'action' => 'logout']) ?>
            <?php else : ?>
                <?= $this->Html->link(__('Login'), ['prefix' => false, 'controller' => 'Users', 'action' => 'login']) ?>
            <?php endif; ?>
        </div>
    </nav>
    <main class="main">
        <div class="container">
            <?= $this->Flash->render() ?>
            <?= $this->fetch('content') ?>
        </div>
    </main>
    <footer>
    </footer>
</body>
</html>
