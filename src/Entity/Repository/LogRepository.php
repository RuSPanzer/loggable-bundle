<?php

namespace Ruspanzer\LoggableBundle\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Ruspanzer\LoggableBundle\Entity\Log;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Ruspanzer\LoggableBundle\Entity\Interfaces\LoggableInterface;
use Ruspanzer\LoggableBundle\Entity\LogRelatedEntity;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Log|null find($id, $lockMode = null, $lockVersion = null)
 * @method Log|null findOneBy(array $criteria, array $orderBy = null)
 * @method Log[]    findAll()
 * @method Log[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Log::class);
    }

    /**
     * Search by native sql, because mysql don't use two index by different tables in OR expression
     * And doctrine don't support UNION query(.
     *
     * here need write a pagination
     *
     * @return Log[]
     */
    public function getByObject(LoggableInterface $loggable): array
    {
        $logTableName = $this->getEntityManager()->getClassMetadata(Log::class)->getTableName();
        $logRelationTableName = $this->getEntityManager()->getClassMetadata(LogRelatedEntity::class)->getTableName();

        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata(Log::class, 'l');

        $queryString = "SELECT l.* 
                    FROM (
                        SELECT * 
                        FROM $logTableName
                        WHERE class = :class AND object_id = :obj
                        
                        UNION ALL
                        
                        SELECT $logTableName.* 
                        FROM $logTableName 
                        JOIN $logRelationTableName ON $logTableName.id = $logRelationTableName.log_id 
                        WHERE $logRelationTableName.class = :class AND $logRelationTableName.object_id = :obj
                    ) l ORDER BY date DESC";

        $query = $this->getEntityManager()
            ->createNativeQuery($queryString, $rsm)
            ->setParameters([
                'class' => get_class($loggable),
                'obj' => $loggable->getId(),
            ]);

        return $query->getResult();
    }
}
