<?php

namespace SoftPassio\UserBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Annotation\Target({"CLASS", "METHOD"})
 */
class AVSecurity
{
    /**
     * @var array
     *
     * allowed roles list
     */
    public $allow = [];

    /**
     * @var array
     *
     * disallow roles list
     */
    public $disallow = [];

    /**
     * @var array
     *
     * security action name
     */
    public $name;

    /**
     * @var array
     *
     * security actions group
     */
    public $group;
}
