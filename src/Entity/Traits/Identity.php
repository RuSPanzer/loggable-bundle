<?php

namespace Ruspanzer\LoggableBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trait Identity.
 *
 * @package Ruspanzer\LoggableBundle\Entity\Traits
 */
trait Identity
{
    /**
     * @var int
     * @ORM\Id()
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @return int
     */
    public function getId()
    {
        return (int) $this->id;
    }
}
