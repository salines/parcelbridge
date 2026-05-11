<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Client $client
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Client'), ['action' => 'edit', $client->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Client'), ['action' => 'delete', $client->id], ['confirm' => __('Are you sure you want to delete record #{0}?', $client->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Clients'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Client'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="clients view content">
            <h3><?= h($client->suite_number) ?></h3>
            <table>
                <tr>
                    <th><?= __('User') ?></th>
                    <td><?= $client->hasValue('user') ? $this->Html->link($client->user->name, ['controller' => 'Users', 'action' => 'view', $client->user->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Suite Number') ?></th>
                    <td><?= h($client->suite_number) ?></td>
                </tr>
                <tr>
                    <th><?= __('Phone') ?></th>
                    <td><?= h($client->phone) ?></td>
                </tr>
                <tr>
                    <th><?= __('Address Line 1') ?></th>
                    <td><?= h($client->address_line_1) ?></td>
                </tr>
                <tr>
                    <th><?= __('Address Line 2') ?></th>
                    <td><?= h($client->address_line_2) ?></td>
                </tr>
                <tr>
                    <th><?= __('City') ?></th>
                    <td><?= h($client->city) ?></td>
                </tr>
                <tr>
                    <th><?= __('Country') ?></th>
                    <td><?= h($client->country) ?></td>
                </tr>
                <tr>
                    <th><?= __('ID') ?></th>
                    <td><?= $this->Number->format($client->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($client->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($client->modified) ?></td>
                </tr>
            </table>
            <div class="related">
                <h4><?= __('Related Packages') ?></h4>
                <?php if (!empty($client->packages)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('ID') ?></th>
                            <th><?= __('Tracking Number') ?></th>
                            <th><?= __('Width') ?></th>
                            <th><?= __('Height') ?></th>
                            <th><?= __('Length') ?></th>
                            <th><?= __('Weight') ?></th>
                            <th><?= __('Dimension Unit') ?></th>
                            <th><?= __('Weight Unit') ?></th>
                            <th><?= __('Contents') ?></th>
                            <th><?= __('Status') ?></th>
                            <th><?= __('Received At') ?></th>
                            <th><?= __('Shipped At') ?></th>
                            <th><?= __('Ready for Pickup At') ?></th>
                            <th><?= __('Delivered At') ?></th>
                            <th><?= __('Created By User ID') ?></th>
                            <th><?= __('Created') ?></th>
                            <th><?= __('Modified') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($client->packages as $package) : ?>
                        <tr>
                            <td><?= h($package->id) ?></td>
                            <td><?= h($package->tracking_number) ?></td>
                            <td><?= h($package->width) ?></td>
                            <td><?= h($package->height) ?></td>
                            <td><?= h($package->length) ?></td>
                            <td><?= h($package->weight) ?></td>
                            <td><?= h($package->dimension_unit?->label()) ?></td>
                            <td><?= h($package->weight_unit?->label()) ?></td>
                            <td><?= h($package->contents_description) ?></td>
                            <td><?= h($package->status?->label()) ?></td>
                            <td><?= h($package->received_at) ?></td>
                            <td><?= h($package->shipped_at) ?></td>
                            <td><?= h($package->ready_for_pickup_at) ?></td>
                            <td><?= h($package->delivered_at) ?></td>
                            <td><?= h($package->created_by_user_id) ?></td>
                            <td><?= h($package->created) ?></td>
                            <td><?= h($package->modified) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'Packages', 'action' => 'view', $package->id]) ?>
                                <?= $this->Html->link(__('Edit'), ['controller' => 'Packages', 'action' => 'edit', $package->id]) ?>
                                <?= $this->Form->postLink(
                                    __('Delete'),
                                    ['controller' => 'Packages', 'action' => 'delete', $package->id],
                                    [
                                        'method' => 'delete',
                                        'confirm' => __('Are you sure you want to delete record #{0}?', $package->id),
                                    ]
                                ) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <div class="related">
                <h4><?= __('Related Ship Requests') ?></h4>
                <?php if (!empty($client->ship_requests)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('ID') ?></th>
                            <th><?= __('Submitted By User ID') ?></th>
                            <th><?= __('Processed By User ID') ?></th>
                            <th><?= __('Status') ?></th>
                            <th><?= __('Processing Reference') ?></th>
                            <th><?= __('Submitted At') ?></th>
                            <th><?= __('Processed At') ?></th>
                            <th><?= __('Notes') ?></th>
                            <th><?= __('Created') ?></th>
                            <th><?= __('Modified') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($client->ship_requests as $shipRequest) : ?>
                        <tr>
                            <td><?= h($shipRequest->id) ?></td>
                            <td><?= h($shipRequest->submitted_by_user_id) ?></td>
                            <td><?= h($shipRequest->processed_by_user_id) ?></td>
                            <td><?= h($shipRequest->status?->label()) ?></td>
                            <td><?= h($shipRequest->processing_reference) ?></td>
                            <td><?= h($shipRequest->submitted_at) ?></td>
                            <td><?= h($shipRequest->processed_at) ?></td>
                            <td><?= h($shipRequest->notes) ?></td>
                            <td><?= h($shipRequest->created) ?></td>
                            <td><?= h($shipRequest->modified) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'ShipRequests', 'action' => 'view', $shipRequest->id]) ?>
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
