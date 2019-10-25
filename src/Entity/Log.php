<?php

namespace Ruspanzer\LoggableBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Ruspanzer\LoggableBundle\Entity\Traits\ExtraDataTrait;
use Ruspanzer\LoggableBundle\Entity\Traits\Identity;
use Ruspanzer\LoggableBundle\Entity\Traits\ObjectLog;

/**
 * @ORM\Entity(repositoryClass="Ruspanzer\LoggableBundle\Entity\Repository\LogRepository")
 * @ORM\Table(name="logs", indexes={
 *     @ORM\Index(name="logs_class_idx", columns={"class"}),
 *     @ORM\Index(name="logs_date_idx", columns={"date"}),
 *     @ORM\Index(name="logs_object_id_idx", columns={"object_id"}),
 *     @ORM\Index(name="logs_object_id_class_idx", columns={"class", "object_id"}),
 * })
 */
class Log
{
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_REMOVE = 'remove';

    use Identity;
    use ObjectLog;
    use ExtraDataTrait;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    private $username;

    /**
     * @var DateTime|null
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $date;

    /**
     * @var array
     * @ORM\Column(type="json", nullable=false)
     */
    private $oldData = [];

    /**
     * @var array
     * @ORM\Column(type="json", nullable=false)
     */
    private $newData = [];

    /**
     * @var ArrayCollection|LogRelatedEntity[]
     * @ORM\OneToMany(targetEntity="Ruspanzer\LoggableBundle\Entity\LogRelatedEntity", mappedBy="log", orphanRemoval=true, cascade={"all"})
     */
    private $relatedEntities;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=false)
     */
    private $action;

    public function __construct()
    {
        $this->date = new DateTime();
        $this->relatedEntities = new ArrayCollection();
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getOldData(): array
    {
        return $this->oldData;
    }

    public function setOldData(array $oldData): self
    {
        $this->oldData = $oldData;

        return $this;
    }

    public function getNewData(): array
    {
        return $this->newData;
    }

    public function setNewData(array $newData): self
    {
        $this->newData = $newData;

        return $this;
    }

    public function getDate(): ?DateTime
    {
        return $this->date;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(?string $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function getRelatedEntities()
    {
        return $this->relatedEntities;
    }

    public function addRelatedEntity(LogRelatedEntity $entity): self
    {
        $entity->setLog($this);
        $this->relatedEntities->add($entity);

        return $this;
    }

    public function removeRelatedEntity(LogRelatedEntity $entity): self
    {
        $this->relatedEntities->removeElement($entity);

        return $this;
    }

    public function getDataRowCount(): int
    {
        return max(count($this->getOldData()), count($this->getNewData()));
    }

    public function isActionCreate(): bool
    {
        return self::ACTION_CREATE === $this->getAction();
    }

    public function isActionUpdate(): bool
    {
        return self::ACTION_UPDATE === $this->getAction();
    }

    public function isActionRemove(): bool
    {
        return self::ACTION_REMOVE === $this->getAction();
    }

    public function getDataFields(): array
    {
        return count($this->getOldData()) ? array_keys($this->getOldData()) : array_keys($this->getNewData());
    }
}
