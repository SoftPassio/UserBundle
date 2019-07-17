<?php

namespace SoftPassio\UserBundle\Security;

use SoftPassio\UserBundle\Entity\RoleableInterface;

interface AccessResolverInterface
{
    public function resolve(RoleableInterface $user, $action) : bool;
}
