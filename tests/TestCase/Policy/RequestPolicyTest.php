<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Enum\UserRole;
use App\Policy\RequestPolicy;
use ArrayAccess;
use Authorization\IdentityInterface;
use Authorization\Policy\Result;
use Authorization\Policy\ResultInterface;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;

class RequestPolicyTest extends TestCase
{
    public function testAdminPrefixAllowsAdmin(): void
    {
        $policy = new RequestPolicy();
        $request = (new ServerRequest())->withParam('prefix', 'Admin');

        $this->assertTrue($policy->canAccess($this->identity(UserRole::Admin), $request));
    }

    public function testAdminPrefixRejectsClient(): void
    {
        $policy = new RequestPolicy();
        $request = (new ServerRequest())->withParam('prefix', 'Admin');

        $this->assertFalse($policy->canAccess($this->identity(UserRole::Client), $request));
    }

    public function testClientPrefixAllowsClient(): void
    {
        $policy = new RequestPolicy();
        $request = (new ServerRequest())->withParam('prefix', 'Client');

        $this->assertTrue($policy->canAccess($this->identity(UserRole::Client), $request));
    }

    public function testClientPrefixRejectsAdmin(): void
    {
        $policy = new RequestPolicy();
        $request = (new ServerRequest())->withParam('prefix', 'Client');

        $this->assertFalse($policy->canAccess($this->identity(UserRole::Admin), $request));
    }

    private function identity(UserRole $role): IdentityInterface
    {
        return new class ($role) implements IdentityInterface {
            public function __construct(public UserRole $role)
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
                return ['role' => $this->role];
            }

            public function offsetExists(mixed $offset): bool
            {
                return $offset === 'role';
            }

            public function offsetGet(mixed $offset): mixed
            {
                return $offset === 'role' ? $this->role : null;
            }

            public function offsetSet(mixed $offset, mixed $value): void
            {
            }

            public function offsetUnset(mixed $offset): void
            {
            }
        };
    }
}
