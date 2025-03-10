<?php

declare(strict_types=1);

namespace App\Security;

use App\Interface\AuthUserInterface;
use App\Interface\UserFetcherInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Webmozart\Assert\Assert;

readonly class UserFetcher implements UserFetcherInterface
{
    public function __construct(private Security $security)
    {
    }

    public function getAuthUser(): AuthUserInterface
    {
        /** @var AuthUserInterface $user*/
        $user = $this->security->getUser();
        Assert::notNull($user, 'Current user not found check security access list');
        Assert::isInstanceOf($user, AuthUserInterface::class, sprintf('Invalid user type %s', get_class($user)));

        return $user;
    }
}
