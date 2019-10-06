<?php
/**
 * Created by PhpStorm.
 * User: ruspa
 * Date: 12.09.2018
 * Time: 9:13.
 */

namespace Ruspanzer\LoggableBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ruspanzer\LoggableBundle\Entity\Traits\Identity;
use Ruspanzer\LoggableBundle\Entity\Traits\ObjectLog;

/**
 * Class LogRelatedEntity.
 *
 * @ORM\Entity()
 * @ORM\Table(name="ruspanzer_log_relations", indexes={
 *     @ORM\Index(name="logs_relations_object_id_class_idx", columns={"class", "object_id"}),
 * })
 */
class LogRelatedEntity
{
    use Identity, ObjectLog;

    /**
     * @var Log|null
     * @ORM\ManyToOne(targetEntity="Ruspanzer\LoggableBundle\Entity\Log", inversedBy="relatedEntities")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $log;

    /**
     * @return Log|null
     */
    public function getLog(): ?Log
    {
        return $this->log;
    }

    /**
     * @param Log|null $log
     *
     * @return LogRelatedEntity
     */
    public function setLog(Log $log): LogRelatedEntity
    {
        $this->log = $log;

        return $this;
    }
}
