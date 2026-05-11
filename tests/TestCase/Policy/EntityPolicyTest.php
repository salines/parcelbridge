<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Entity\Client;
use App\Model\Entity\Invoice;
use App\Model\Entity\Package;
use App\Model\Entity\ShipRequest;
use App\Model\Entity\User;
use App\Model\Enum\PackageStatus;
use App\Model\Enum\ShipRequestStatus;
use App\Model\Enum\UserRole;
use App\Policy\InvoicePolicy;
use App\Policy\PackagePolicy;
use App\Policy\ShipRequestPolicy;
use ArrayAccess;
use Authorization\IdentityInterface;
use Authorization\Policy\Result;
use Authorization\Policy\ResultInterface;
use Cake\TestSuite\TestCase;

class EntityPolicyTest extends TestCase
{
    public function testPackagePolicyUsesOwnershipAndWorkflowActions(): void
    {
        $policy = new PackagePolicy();
        $package = new Package([
            'client_id' => 1,
            'status' => PackageStatus::ReadyToSend,
        ]);

        $this->assertTrue($policy->canView($this->identity(UserRole::Client, 1), $package));
        $this->assertFalse($policy->canView($this->identity(UserRole::Client, 2), $package));
        $this->assertTrue($policy->canUploadInvoice($this->identity(UserRole::Client, 1), $package));
        $this->assertFalse($policy->canEdit($this->identity(UserRole::Client, 1), $package));
        $this->assertFalse($policy->canDelete($this->identity(UserRole::Client, 1), $package));
        $this->assertTrue($policy->canEdit($this->identity(UserRole::Admin), $package));
        $this->assertTrue($policy->canReadyForPickup($this->identity(UserRole::Admin), $package));
    }

    public function testInvoicePolicyRestrictsClientToOwnedViewAndAdminReviewActions(): void
    {
        $policy = new InvoicePolicy();
        $invoice = new Invoice([
            'package' => new Package(['client_id' => 1]),
        ]);

        $this->assertTrue($policy->canView($this->identity(UserRole::Client, 1), $invoice));
        $this->assertFalse($policy->canView($this->identity(UserRole::Client, 2), $invoice));
        $this->assertFalse($policy->canApprove($this->identity(UserRole::Client, 1), $invoice));
        $this->assertFalse($policy->canNeedsReview($this->identity(UserRole::Client, 1), $invoice));
        $this->assertFalse($policy->canEdit($this->identity(UserRole::Client, 1), $invoice));
        $this->assertFalse($policy->canDelete($this->identity(UserRole::Admin), $invoice));
        $this->assertTrue($policy->canApprove($this->identity(UserRole::Admin), $invoice));
        $this->assertTrue($policy->canNeedsReview($this->identity(UserRole::Admin), $invoice));
        $this->assertTrue($policy->canReject($this->identity(UserRole::Admin), $invoice));
    }

    public function testShipRequestPolicyRestrictsClientAndAdminWorkflowActions(): void
    {
        $policy = new ShipRequestPolicy();
        $shipRequest = new ShipRequest([
            'client_id' => 1,
            'status' => ShipRequestStatus::Submitted,
        ]);

        $this->assertTrue($policy->canView($this->identity(UserRole::Client, 1), $shipRequest));
        $this->assertFalse($policy->canView($this->identity(UserRole::Client, 2), $shipRequest));
        $this->assertFalse($policy->canProcess($this->identity(UserRole::Client, 1), $shipRequest));
        $this->assertFalse($policy->canEdit($this->identity(UserRole::Admin), $shipRequest));
        $this->assertFalse($policy->canDelete($this->identity(UserRole::Admin), $shipRequest));
        $this->assertTrue($policy->canProcess($this->identity(UserRole::Admin), $shipRequest));
    }

    private function identity(UserRole $role, ?int $clientId = null): IdentityInterface
    {
        $user = new User([
            'id' => $role === UserRole::Admin ? 1 : 2,
            'role' => $role,
        ]);
        if ($clientId !== null) {
            $user->client = new Client(['id' => $clientId]);
        }

        return new class ($user) implements IdentityInterface {
            public function __construct(private User $user)
            {
            }

            public function can(string $action, mixed $resource): bool
            {
                return true;
            }

            public function canResult(string $action, mixed $resource): ResultInterface
            {
                return new Result(true);
            }

            public function applyScope(string $action, mixed $resource, mixed ...$optionalArgs): mixed
            {
                return $resource;
            }

            public function getOriginalData(): ArrayAccess|array
            {
                return $this->user;
            }

            public function offsetExists(mixed $offset): bool
            {
                return $this->user->has($offset);
            }

            public function offsetGet(mixed $offset): mixed
            {
                return $this->user->get($offset);
            }

            public function offsetSet(mixed $offset, mixed $value): void
            {
                $this->user->set($offset, $value);
            }

            public function offsetUnset(mixed $offset): void
            {
                $this->user->unset($offset);
            }
        };
    }
}
