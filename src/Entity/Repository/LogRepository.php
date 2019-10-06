<?php

namespace Ruspanzer\LoggableBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Ruspanzer\LoggableBundle\Entity\Log;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Ruspanzer\LoggableBundle\Entity\Interfaces\LoggableInterface;

class LogRepository extends EntityRepository
{
    /**
     * Search by native sql, because mysql don't use two index by different tables in OR expression
     * And doctrine don't support UNION query(
     *
     * here need write a pagination
     *
     * @param LoggableInterface $loggable
     *
     * @return Log[]
     */
    public function getByObject(LoggableInterface $loggable)
    {
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata(Log::class, 'l');

        $queryString = 'SELECT l.* FROM (
                    SELECT * FROM ruspanzer_logs WHERE class = :class AND object_id = :obj 
                    UNION SELECT ruspanzer_logs.* FROM ruspanzer_logs JOIN ruspanzer_log_relations ON ruspanzer_logs.id = ruspanzer_log_relations.log_id 
                        WHERE ruspanzer_log_relations.class = :class AND ruspanzer_log_relations.object_id = :obj
                  ) l ORDER BY date';

        $query = $this->getEntityManager()
            ->createNativeQuery($queryString, $rsm)
            ->setParameters([
                'class' => get_class($loggable),
                'obj'   => $loggable->getId(),
            ]);

        return $query->getResult();
    }
}