<?php

namespace AppVerk\UserBundle\Security;

use AppVerk\UserBundle\Entity\RoleableInterface;

interface AccessResolverInterface
{
    public function resolve(RoleableInterface $user, $action) : bool;
}
