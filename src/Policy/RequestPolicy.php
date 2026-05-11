<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Enum\UserRole;
use Authorization\IdentityInterface;
use Authorization\Policy\RequestPolicyInterface;
use Cake\Http\ServerRequest;

class RequestPolicy implements RequestPolicyInterface
{
    /**
     * Check request access.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \Cake\Http\ServerRequest $request Request
     * @return bool
     */
    public function canAccess(?IdentityInterface $identity, ServerRequest $request): bool
    {
        $prefix = $request->getParam('prefix');
        if ($prefix === null) {
            return true;
        }

        if ($identity === null) {
            return true;
        }

        return match ($prefix) {
            'Admin' => $this->role($identity) === UserRole::Admin->value,
            'Client' => $this->role($identity) === UserRole::Client->value,
            default => true,
        };
    }

    /**
     * Resolve role from the decorated identity.
     *
     * @param \Authorization\IdentityInterface $identity Identity
     * @return string|null
     */
    private function role(IdentityInterface $identity): ?string
    {
        $role = $identity->role ?? null;
        if ($role instanceof UserRole) {
            return $role->value;
        }

        return is_string($role) ? $role : null;
    }
}
