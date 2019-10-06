<?php


namespace Ruspanzer\LoggableBundle\Entity\Traits;

/**
 * Trait ObjectLog
 *
 * @package Ruspanzer\LoggableBundle\Entity\Traits
 */
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

    /**
     * @return string|null
     */
    public function getClass(): ?string
    {
        return $this->class;
    }

    /**
     * @param string|null $class
     *
     * @return $this
     */
    public function setClass(?string $class): self
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getObjectId(): ?int
    {
        return $this->objectId;
    }

    /**
     * @param int|null $objectId
     *
     * @return $this
     */
    public function setObjectId(?int $objectId): self
    {
        $this->objectId = $objectId;

        return $this;
    }
}