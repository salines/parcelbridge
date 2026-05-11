<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController as BaseController;
use BackedEnum;
use Cake\Database\Type\EnumLabelInterface;
use Cake\Datasource\EntityInterface;
use Cake\I18n\Date;
use Cake\View\JsonView;
use DateTimeInterface;

/**
 * API App Controller.
 *
 * @property \Authentication\Controller\Component\AuthenticationComponent $Authentication
 * @property \Authorization\Controller\Component\AuthorizationComponent $Authorization
 */
class AppController extends BaseController
{
    /**
     * JSON view classes supported by API controllers.
     *
     * @return array<class-string>
     */
    public function viewClasses(): array
    {
        return [JsonView::class];
    }

    /**
     * Set JSON payload and HTTP status.
     *
     * @param array<string, mixed> $payload Response payload
     * @param int $status HTTP status code
     * @return void
     */
    protected function json(array $payload, int $status = 200): void
    {
        $this->set($payload);
        $this->viewBuilder()->setOption('serialize', array_keys($payload));
        $this->setResponse($this->getResponse()->withStatus($status));
    }

    /**
     * Set JSON error payload and HTTP status.
     *
     * @param string $message Error message
     * @param int $status HTTP status code
     * @return void
     */
    protected function jsonError(string $message, int $status = 400): void
    {
        $this->json(['error' => ['message' => $message]], $status);
    }

    /**
     * Serialize a user for API clients.
     *
     * @param \Cake\Datasource\EntityInterface $user User entity
     * @return array<string, mixed>
     */
    protected function userResource(EntityInterface $user): array
    {
        return [
            'id' => $user->get('id'),
            'name' => $user->get('name'),
            'email' => $user->get('email'),
            'role' => $this->enumResource($user->get('role')),
            'client' => $user->has('client') ? $this->clientResource($user->get('client')) : null,
        ];
    }

    /**
     * Serialize a client.
     *
     * @param \Cake\Datasource\EntityInterface|null $client Client entity
     * @return array<string, mixed>|null
     */
    protected function clientResource(?EntityInterface $client): ?array
    {
        if ($client === null) {
            return null;
        }

        return [
            'id' => $client->get('id'),
            'suite_number' => $client->get('suite_number'),
            'phone' => $client->get('phone'),
            'address_line_1' => $client->get('address_line_1'),
            'address_line_2' => $client->get('address_line_2'),
            'city' => $client->get('city'),
            'country' => $client->get('country'),
        ];
    }

    /**
     * Serialize a package.
     *
     * @param \Cake\Datasource\EntityInterface $package Package entity
     * @return array<string, mixed>
     */
    protected function packageResource(EntityInterface $package): array
    {
        return [
            'id' => $package->get('id'),
            'client_id' => $package->get('client_id'),
            'client' => $package->has('client') ? $this->clientResource($package->get('client')) : null,
            'tracking_number' => $package->get('tracking_number'),
            'contents_description' => $package->get('contents_description'),
            'status' => $this->enumResource($package->get('status')),
            'width' => $package->get('width'),
            'height' => $package->get('height'),
            'length' => $package->get('length'),
            'dimension_unit' => $this->enumResource($package->get('dimension_unit')),
            'weight' => $package->get('weight'),
            'weight_unit' => $this->enumResource($package->get('weight_unit')),
            'received_at' => $this->dateResource($package->get('received_at')),
            'shipped_at' => $this->dateResource($package->get('shipped_at')),
            'ready_for_pickup_at' => $this->dateResource($package->get('ready_for_pickup_at')),
            'delivered_at' => $this->dateResource($package->get('delivered_at')),
            'invoice' => $package->has('invoice') ? $this->invoiceResource($package->get('invoice'), false) : null,
        ];
    }

    /**
     * Serialize an invoice.
     *
     * @param \Cake\Datasource\EntityInterface $invoice Invoice entity
     * @param bool $includePackage Whether to include the package relation
     * @return array<string, mixed>
     */
    protected function invoiceResource(EntityInterface $invoice, bool $includePackage = true): array
    {
        $resource = [
            'id' => $invoice->get('id'),
            'package_id' => $invoice->get('package_id'),
            'original_filename' => $invoice->get('original_filename'),
            'mime_type' => $invoice->get('mime_type'),
            'file_size' => $invoice->get('file_size'),
            'review_status' => $this->enumResource($invoice->get('review_status')),
            'admin_notes' => $invoice->get('admin_notes'),
            'uploaded_at' => $this->dateResource($invoice->get('uploaded_at')),
            'reviewed_at' => $this->dateResource($invoice->get('reviewed_at')),
        ];

        if ($includePackage) {
            $resource['package'] = $invoice->has('package') ? $this->packageResource($invoice->get('package')) : null;
        }

        return $resource;
    }

    /**
     * Serialize a ship request.
     *
     * @param \Cake\Datasource\EntityInterface $shipRequest Ship request entity
     * @return array<string, mixed>
     */
    protected function shipRequestResource(EntityInterface $shipRequest): array
    {
        $packages = [];
        foreach ((array)$shipRequest->get('packages_ship_requests') as $join) {
            if ($join->has('package')) {
                $packages[] = $this->packageResource($join->get('package'));
            }
        }

        return [
            'id' => $shipRequest->get('id'),
            'client_id' => $shipRequest->get('client_id'),
            'client' => $shipRequest->has('client') ? $this->clientResource($shipRequest->get('client')) : null,
            'status' => $this->enumResource($shipRequest->get('status')),
            'processing_reference' => $shipRequest->get('processing_reference'),
            'submitted_at' => $this->dateResource($shipRequest->get('submitted_at')),
            'processed_at' => $this->dateResource($shipRequest->get('processed_at')),
            'notes' => $shipRequest->get('notes'),
            'packages' => $packages,
        ];
    }

    /**
     * Serialize PHP backed enum values with i18n-ready labels.
     *
     * @param mixed $value Value
     * @return mixed|array<string, mixed>
     */
    protected function enumResource(mixed $value): mixed
    {
        if (!$value instanceof BackedEnum) {
            return $value;
        }

        return [
            'value' => $value->value,
            'label' => $value instanceof EnumLabelInterface ? $value->label() : $value->name,
        ];
    }

    /**
     * Serialize date/time values.
     *
     * @param mixed $value Value
     * @return string|null
     */
    protected function dateResource(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Date) {
            return (string)$value->jsonSerialize();
        }

        return $value instanceof DateTimeInterface ? $value->format(DATE_ATOM) : (string)$value;
    }
}
