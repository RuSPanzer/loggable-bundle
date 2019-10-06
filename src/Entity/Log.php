<?php
/**
 * Created by PhpStorm.
 * User: ruspa
 * Date: 11.09.2018
 * Time: 21:19.
 */

namespace Ruspanzer\LoggableBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Ruspanzer\LoggableBundle\Entity\Traits\Identity;
use Ruspanzer\LoggableBundle\Entity\Traits\ObjectLog;

/**
 * Class Log.
 *
 * @ORM\Entity(repositoryClass="Ruspanzer\LoggableBundle\Entity\Repository\LogRepository")
 * @ORM\Table(name="ruspanzer_logs", indexes={
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

    use Identity, ObjectLog;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    private $user;

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
     * @var
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
        $this->date            = new DateTime();
        $this->relatedEntities = new ArrayCollection();
    }

    /**
     * @return string|null
     */
    public function getUser(): ?string
    {
        return $this->user;
    }

    /**
     * @param string|null $user
     *
     * @return Log
     */
    public function setUser(?string $user): Log
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return array
     */
    public function getOldData(): array
    {
        return $this->oldData;
    }

    /**
     * @param array $oldData
     *
     * @return Log
     */
    public function setOldData(array $oldData): Log
    {
        $this->oldData = $oldData;

        return $this;
    }

    /**
     * @return array
     */
    public function getNewData(): array
    {
        return $this->newData;
    }

    /**
     * @param array $newData
     *
     * @return Log
     */
    public function setNewData(array $newData): Log
    {
        $this->newData = $newData;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getDate(): ?DateTime
    {
        return $this->date;
    }

    /**
     * @return string|null
     */
    public function getAction(): ?string
    {
        return $this->action;
    }

    /**
     * @param string|null $action
     *
     * @return Log
     */
    public function setAction(?string $action): Log
    {
        $this->action = $action;

        return $this;
    }

    public function getRelatedEntities()
    {
        return $this->relatedEntities;
    }

    public function addRelatedEntity(LogRelatedEntity $entity)
    {
        $entity->setLog($this);
        $this->relatedEntities->add($entity);

        return $this;
    }

    public function removeRelatedEntity(LogRelatedEntity $entity)
    {
        $this->relatedEntities->removeElement($entity);

        return $this;
    }

    /**
     * @return int
     */
    public function getDataRowCount(): int
    {
        return max(count($this->getOldData()), count($this->getNewData()));
    }

    public function isActionCreate()
    {
        return self::ACTION_CREATE === $this->getAction();
    }

    public function isActionUpdate()
    {
        return self::ACTION_UPDATE === $this->getAction();
    }

    public function isActionRemove()
    {
        return self::ACTION_REMOVE === $this->getAction();
    }

    /**
     * @return array
     */
    public function getDataFields()
    {
        return count($this->getOldData()) ? array_keys($this->getOldData()) : array_keys($this->getNewData());
    }
}
