<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testGetRolesAlwaysContainsRoleUser(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_HOST']);

        self::assertContains('ROLE_HOST', $user->getRoles());
        self::assertContains('ROLE_USER', $user->getRoles());
    }

    public function testDeactivateAndRequestReactivationFlow(): void
    {
        $user = new User();
        $user->deactivateByUser();
        $user->requestReactivation('merci');

        self::assertTrue($user->isDeactivatedByUser());
        self::assertNotNull($user->getReactivationRequestedAt());
        self::assertSame('merci', $user->getReactivationRequestNote());
    }

    public function testSuspendThenActivateAccountResetsStatus(): void
    {
        $user = new User();
        $user->suspend();
        self::assertTrue($user->isSuspended());

        $user->activateAccount();
        self::assertSame(User::STATUS_ACTIVE, $user->getAccountStatus());
        self::assertFalse($user->isBanned());
    }
}
