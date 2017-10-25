<?php

namespace AppVerk\UserBundle\Component\Doctrine;

interface UserProviderInterface
{
    public function findUserByUsername(string $username);
}
