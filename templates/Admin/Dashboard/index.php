<?php
/**
 * @var \App\View\AppView $this
 * @var array<string, int> $packageCounts
 * @var int $clientCount
 * @var int $pendingInvoiceCount
 */
?>
<div class="dashboard index content">
            <h3><?= __('Admin Dashboard') ?></h3>

    <div class="table-responsive">
        <table>
            <tr>
                <th><?= __('Clients') ?></th>
                <td><?= $this->Number->format($clientCount) ?></td>
            </tr>
            <tr>
                <th><?= __('Pending Invoice Reviews') ?></th>
                <td><?= $this->Number->format($pendingInvoiceCount) ?></td>
            </tr>
        </table>
    </div>

    <h4><?= __('Packages by Status') ?></h4>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= __('Status') ?></th>
                    <th><?= __('Count') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($packageCounts as $status => $count): ?>
                <tr>
                    <td><?= h($status) ?></td>
                    <td><?= $this->Number->format($count) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
