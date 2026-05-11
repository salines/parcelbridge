<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ShipRequest $shipRequest
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Ship Requests'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('Download Manifest PDF'), ['action' => 'manifest', $shipRequest->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Ship Request'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="shipRequests view content">
            <h3><?= h($shipRequest->status?->label()) ?></h3>
            <table>
                <tr>
                    <th><?= __('Client') ?></th>
                    <td><?= $shipRequest->hasValue('client') ? h($shipRequest->client->suite_number) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Submitted By User') ?></th>
                    <td><?= $shipRequest->hasValue('submitted_by_user') ? h($shipRequest->submitted_by_user->name) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Processed By User') ?></th>
                    <td><?= $shipRequest->hasValue('processed_by_user') ? h($shipRequest->processed_by_user->name) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Processing Reference') ?></th>
                    <td><?= h($shipRequest->processing_reference) ?></td>
                </tr>
                <tr>
                    <th><?= __('ID') ?></th>
                    <td><?= $this->Number->format($shipRequest->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Status') ?></th>
                    <td><?= h($shipRequest->status?->label()) ?></td>
                </tr>
                <tr>
                    <th><?= __('Submitted At') ?></th>
                    <td><?= h($shipRequest->submitted_at) ?></td>
                </tr>
                <tr>
                    <th><?= __('Processed At') ?></th>
                    <td><?= h($shipRequest->processed_at) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($shipRequest->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($shipRequest->modified) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('Notes') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($shipRequest->notes)); ?>
                </blockquote>
            </div>
            <div class="related">
                <h4><?= __('Related Packages') ?></h4>
                <?php if (!empty($shipRequest->packages_ship_requests)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Tracking Number') ?></th>
                            <th><?= __('Weight') ?></th>
                            <th><?= __('Weight Unit') ?></th>
                            <th><?= __('Contents') ?></th>
                            <th><?= __('Status') ?></th>
                            <th><?= __('Received At') ?></th>
                            <th><?= __('Shipped At') ?></th>
                            <th><?= __('Ready for Pickup At') ?></th>
                            <th><?= __('Delivered At') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($shipRequest->packages_ship_requests as $join) : ?>
                        <?php $package = $join->package; ?>
                        <tr>
                            <td><?= h($package->tracking_number) ?></td>
                            <td><?= h($package->weight) ?></td>
                            <td><?= h($package->weight_unit?->label()) ?></td>
                            <td><?= h($package->contents_description) ?></td>
                            <td><?= h($package->status?->label()) ?></td>
                            <td><?= h($package->received_at) ?></td>
                            <td><?= h($package->shipped_at) ?></td>
                            <td><?= h($package->ready_for_pickup_at) ?></td>
                            <td><?= h($package->delivered_at) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'Packages', 'action' => 'view', $package->id]) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
