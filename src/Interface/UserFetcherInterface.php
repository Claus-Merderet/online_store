<?php

namespace App\Interface;

interface UserFetcherInterface
{
    public function getAuthUser(): AuthUserInterface;

}