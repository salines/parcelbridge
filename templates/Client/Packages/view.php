<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Package $package
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('My Packages'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('Download PDF'), ['action' => 'document', $package->id], ['class' => 'side-nav-item']) ?>
            <?php if ($package->canUploadInvoice()) : ?>
                <?= $this->Html->link(__('Upload Invoice'), ['action' => 'uploadInvoice', $package->id], ['class' => 'side-nav-item']) ?>
            <?php endif; ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="packages view content">
            <h3><?= h($package->tracking_number) ?></h3>
            <table>
                <tr>
                    <th><?= __('Client') ?></th>
                    <td><?= $package->hasValue('client') ? h($package->client->suite_number) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Tracking Number') ?></th>
                    <td><?= h($package->tracking_number) ?></td>
                </tr>
                <tr>
                    <th><?= __('Dimension Unit') ?></th>
                    <td><?= h($package->dimension_unit?->label()) ?></td>
                </tr>
                <tr>
                    <th><?= __('Weight Unit') ?></th>
                    <td><?= h($package->weight_unit?->label()) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created By') ?></th>
                    <td><?= $package->hasValue('created_by_user') ? h($package->created_by_user->name) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Invoice') ?></th>
                    <td><?= $package->hasValue('invoice') ? $this->Html->link($package->invoice->file_path, ['controller' => 'Invoices', 'action' => 'view', $package->invoice->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('ID') ?></th>
                    <td><?= $this->Number->format($package->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Width') ?></th>
                    <td><?= $this->Number->format($package->width) ?></td>
                </tr>
                <tr>
                    <th><?= __('Height') ?></th>
                    <td><?= $this->Number->format($package->height) ?></td>
                </tr>
                <tr>
                    <th><?= __('Length') ?></th>
                    <td><?= $this->Number->format($package->length) ?></td>
                </tr>
                <tr>
                    <th><?= __('Weight') ?></th>
                    <td><?= $this->Number->format($package->weight) ?></td>
                </tr>
                <tr>
                    <th><?= __('Status') ?></th>
                    <td><?= h($package->status?->label()) ?></td>
                </tr>
                <tr>
                    <th><?= __('Received At') ?></th>
                    <td><?= h($package->received_at) ?></td>
                </tr>
                <tr>
                    <th><?= __('Shipped At') ?></th>
                    <td><?= h($package->shipped_at) ?></td>
                </tr>
                <tr>
                    <th><?= __('Ready for Pickup At') ?></th>
                    <td><?= h($package->ready_for_pickup_at) ?></td>
                </tr>
                <tr>
                    <th><?= __('Delivered At') ?></th>
                    <td><?= h($package->delivered_at) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($package->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($package->modified) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('Contents') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($package->contents_description)); ?>
                </blockquote>
            </div>
            <?php if (!empty($package->invoice?->admin_notes)) : ?>
                <div class="text">
                    <strong><?= __('Invoice Review Note') ?></strong>
                    <blockquote><?= $this->Text->autoParagraph(h($package->invoice->admin_notes)); ?></blockquote>
                </div>
            <?php endif; ?>
            <div class="related">
                <h4><?= __('Related Ship Requests') ?></h4>
                <?php if (!empty($package->packages_ship_requests)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('ID') ?></th>
                            <th><?= __('Status') ?></th>
                            <th><?= __('Processing Reference') ?></th>
                            <th><?= __('Submitted At') ?></th>
                            <th><?= __('Processed At') ?></th>
                            <th><?= __('Notes') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($package->packages_ship_requests as $join) : ?>
                        <?php $shipRequest = $join->ship_request; ?>
                        <tr>
                            <td><?= h($shipRequest->id) ?></td>
                            <td><?= h($shipRequest->status?->label()) ?></td>
                            <td><?= h($shipRequest->processing_reference) ?></td>
                            <td><?= h($shipRequest->submitted_at) ?></td>
                            <td><?= h($shipRequest->processed_at) ?></td>
                            <td><?= h($shipRequest->notes) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'ShipRequests', 'action' => 'view', $shipRequest->id]) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <div class="related">
                <h4><?= __('Related Package Status Histories') ?></h4>
                <?php if (!empty($package->package_status_histories)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('ID') ?></th>
                            <th><?= __('Changed By User ID') ?></th>
                            <th><?= __('Changed By Role') ?></th>
                            <th><?= __('Old Status') ?></th>
                            <th><?= __('New Status') ?></th>
                            <th><?= __('Note') ?></th>
                            <th><?= __('Created') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($package->package_status_histories as $packageStatusHistory) : ?>
                        <tr>
                            <td><?= h($packageStatusHistory->id) ?></td>
                            <td><?= h($packageStatusHistory->changed_by_user_id) ?></td>
                            <td><?= h($packageStatusHistory->changed_by_role?->label()) ?></td>
                            <td><?= h($packageStatusHistory->old_status?->label()) ?></td>
                            <td><?= h($packageStatusHistory->new_status?->label()) ?></td>
                            <td><?= h($packageStatusHistory->note) ?></td>
                            <td><?= h($packageStatusHistory->created) ?></td>
                            <td class="actions"></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
