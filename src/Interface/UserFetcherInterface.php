<?php

declare(strict_types=1);

namespace App\Interface;

interface UserFetcherInterface
{
    public function getAuthUser(): AuthUserInterface;
}
