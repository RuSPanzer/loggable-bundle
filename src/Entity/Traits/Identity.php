<?php

namespace Ruspanzer\LoggableBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait Identity
{
    /**
     * @var int
     * @ORM\Id()
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    public function getId(): int
    {
        return (int) $this->id;
    }
}
