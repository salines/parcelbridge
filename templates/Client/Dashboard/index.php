<?php
/**
 * @var \App\View\AppView $this
 * @var array<string, int> $packageCounts
 */
?>
<div class="dashboard index content">
    <h3><?= __('Dashboard') ?></h3>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= __('Package Status') ?></th>
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
