<?php

namespace Ruspanzer\LoggableBundle\Entity\Traits;

trait ObjectLog
{
    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=false)
     */
    private $class;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    private $objectId;

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function setClass(?string $class): self
    {
        $this->class = $class;

        return $this;
    }

    public function getObjectId(): ?int
    {
        return $this->objectId;
    }

    public function setObjectId(?int $objectId): self
    {
        $this->objectId = $objectId;

        return $this;
    }
}
