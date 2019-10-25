<?php

namespace Ruspanzer\LoggableBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ruspanzer\LoggableBundle\Entity\Traits\Identity;
use Ruspanzer\LoggableBundle\Entity\Traits\ObjectLog;

/**
 * @ORM\Entity()
 * @ORM\Table(name="log_relations", indexes={
 *     @ORM\Index(name="logs_relations_object_id_class_idx", columns={"class", "object_id"}),
 * })
 */
class LogRelatedEntity
{
    use Identity;
    use ObjectLog;

    /**
     * @var Log|null
     * @ORM\ManyToOne(targetEntity="Ruspanzer\LoggableBundle\Entity\Log", inversedBy="relatedEntities")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $log;

    public function getLog(): ?Log
    {
        return $this->log;
    }

    public function setLog(?Log $log): self
    {
        $this->log = $log;

        return $this;
    }
}
