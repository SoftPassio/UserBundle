<?php

namespace SoftPassio\UserBundle\Entity;

interface RoleableInterface
{
    public function hasRole($role);
}
