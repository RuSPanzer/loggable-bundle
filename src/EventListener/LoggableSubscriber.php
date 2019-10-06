<?php
/**
 * Created by PhpStorm.
 * User: ruspa
 * Date: 11.09.2018
 * Time: 21:17.
 */

namespace Ruspanzer\LoggableBundle\EventListener;

use Serializable;
use JsonSerializable;
use Doctrine\ORM\Events;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Ruspanzer\LoggableBundle\Entity\Log;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Ruspanzer\LoggableBundle\Entity\LogRelatedEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Ruspanzer\LoggableBundle\Entity\Interfaces\LoggableInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class LoggableSubscriber
 * @package Ruspanzer\LoggableBundle\EventListener
 */
class LoggableSubscriber implements EventSubscriber
{
    private $tokenStorage;

    protected $pendingObjectIdObjects = [];

    protected $pendingRelatedObjects = [];

    protected $manyToManyData = [];

    private $handledOids = [];

    /**
     * LoggableSubscriber constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return [
            Events::onFlush,
            Events::postPersist,
        ];
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        /** @var PersistentCollection $scheduledCollectionUpdate */
        foreach ($uow->getScheduledCollectionUpdates() as $scheduledCollectionUpdate) {
            $mapping = $scheduledCollectionUpdate->getMapping();
            $owner = $scheduledCollectionUpdate->getOwner();
            if (ClassMetadata::MANY_TO_MANY === $mapping['type'] && $owner instanceof LoggableInterface) {
                $this->processMtMCollection($scheduledCollectionUpdate, $em, $mapping);
            }
            if (ClassMetadata::ONE_TO_MANY === $mapping['type']
                && array_key_exists(LoggableInterface::class, $scheduledCollectionUpdate->getTypeClass()->getReflectionClass()->getInterfaces())
            ) {
                // for softdeletable entities
                foreach ($scheduledCollectionUpdate->getDeleteDiff() as $item) {
                    if (!array_key_exists(spl_object_hash($item), $uow->getScheduledEntityDeletions())) {
                        $this->createLog(Log::ACTION_REMOVE, $item, $em);
                    }
                }
            }
        }

        foreach ($uow->getScheduledEntityInsertions() as $insertion) {
            if ($insertion instanceof LoggableInterface) {
                $this->createLog(Log::ACTION_CREATE, $insertion, $em);
            }
        }
        foreach ($uow->getScheduledEntityUpdates() as $update) {
            if ($update instanceof LoggableInterface) {
                $this->createLog(Log::ACTION_UPDATE, $update, $em);
            }
        }
        foreach ($uow->getScheduledEntityDeletions() as $deletion) {
            if ($deletion instanceof LoggableInterface) {
                $this->createLog(Log::ACTION_REMOVE, $deletion, $em);
            }
        }

        if (count($this->manyToManyData)) {
            foreach ($this->manyToManyData as $item) {
                $this->createLog(Log::ACTION_UPDATE, $item['entity'], $em);
            }
        }
    }

    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $entity = $eventArgs->getEntity();
        $oid = spl_object_hash($entity);
        $uow = $em->getUnitOfWork();

        if (array_key_exists($oid, $this->pendingObjectIdObjects)) {
            $entityMetadata = $em->getClassMetadata(get_class($entity));
            $id = $this->getId($entityMetadata, $entity);
            foreach ($this->pendingObjectIdObjects[$oid] as $object) {
                $objectMetadata = $em->getClassMetadata(get_class($object));
                $objectMetadata->getReflectionProperty('objectId')->setValue($object, $id);
                $uow->scheduleExtraUpdate($object, [
                    'objectId' => [null, $id],
                ]);
                $uow->setOriginalEntityProperty(spl_object_hash($object), 'objectId', $id);
            }
            unset($this->pendingObjectIdObjects[$oid]);
        }
        if (array_key_exists($oid, $this->pendingRelatedObjects)) {
            $entityMetadata = $em->getClassMetadata(get_class($entity));
            $id = $this->getId($entityMetadata, $entity);
            foreach ($this->pendingRelatedObjects[$oid] as $props) {
                /** @var Log $log */
                $log = $props['log'];
                $oldNewData = $newNewData = $log->getNewData();
                $newNewData[$props['field']] = $id;
                $log->setNewData($newNewData);
                $uow->scheduleExtraUpdate($log, [
                    'newData' => [$oldNewData, $newNewData],
                ]);
                $uow->setOriginalEntityProperty(spl_object_hash($log), 'newData', $id);
            }
            unset($this->pendingRelatedObjects[$oid]);
        }
    }

    private function createLog(string $action, LoggableInterface $entity, EntityManagerInterface $em)
    {
        $meta = $em->getClassMetadata(get_class($entity));
        $oid = spl_object_hash($entity);

        if (array_key_exists($oid, $this->handledOids)) {
            return;
        }
        $this->handledOids[$oid] = true;

        $changeSet = $em->getUnitOfWork()->getEntityChangeSet($entity);

        $oldData = [];
        $newData = [];

        $log = new Log();
        $log->setAction($action)
            ->setClass($meta->getName())
            ->setUsername($this->getUsername());

        foreach ($changeSet as $field => $values) {
            if (Log::ACTION_CREATE !== $action) {
                $oldData[$field] = $this->getFieldValue($em, $meta, $field, $values[0], $log);
            }
            if (Log::ACTION_REMOVE !== $action) {
                $newData[$field] = $this->getFieldValue($em, $meta, $field, $values[1], $log);
            }
        }

        if (array_key_exists($oid, $this->manyToManyData)) {
            foreach ($this->manyToManyData[$oid]['changeset'] as $field => $dataSet) {
                if (Log::ACTION_CREATE !== $action) {
                    $oldData[$field] = $dataSet[0];
                }
                if (Log::ACTION_REMOVE !== $action) {
                    $newData[$field] = $dataSet[1];
                }
            }

            unset($this->manyToManyData[$oid]);
        }

        if (0 === count($newData) && 0 === count($oldData)) {
            return;
        }

        $log->setOldData($oldData)->setNewData($newData);
        if ($entity->getId()) {
            $log->setObjectId($entity->getId());
        } else {
            $this->pendingObjectIdObjects[$oid][] = $log;
        }

        foreach ($entity->getRelatedLogEntities() as $relatedLogEntity) {
            $relatedLog = new LogRelatedEntity();
            $relatedLog->setClass($em->getClassMetadata(get_class($relatedLogEntity))->getName());
            if ($relatedLogEntity->getId()) {
                $relatedLog->setObjectId($relatedLogEntity->getId());
            } else {
                $this->pendingObjectIdObjects[spl_object_hash($relatedLogEntity)][] = $relatedLog;
            }
            $log->addRelatedEntity($relatedLog);
        }

        $em->persist($log);
        $em->getUnitOfWork()->computeChangeSet($em->getClassMetadata(Log::class), $log);
        $logRelatedMeta = $em->getClassMetadata(LogRelatedEntity::class);
        foreach ($log->getRelatedEntities() as $relatedEntity) {
            $em->getUnitOfWork()->computeChangeSet($logRelatedMeta, $relatedEntity);
        }
    }

    public function getFieldValue(
        EntityManagerInterface $entityManager,
        ClassMetadata $meta,
        string $field,
        $value,
        Log $log
    )
    {
        if (!$value) {
            return null;
        }

        if (!$meta->hasAssociation($field)) {
            if (is_object($value)) {
                if ($value instanceof JsonSerializable) {
                    return $value->jsonSerialize();
                } elseif (method_exists($value, '__toString')) {
                    return (string)$value;
                } elseif ($value instanceof Serializable) {
                    return serialize($value);
                }
            }

            return $value;
        }

        if ($meta->isSingleValuedAssociation($field)) {
            $valueMeta = $entityManager->getClassMetadata(get_class($value));
            $id = $this->getId($valueMeta, $value);
            if (!$id) {
                $oid = spl_object_hash($value);
                $this->pendingRelatedObjects[$oid][] = [
                    'log' => $log,
                    'field' => $field,
                ];
            }

            return $id;
        }

        return null;
    }

    /**
     * @param ClassMetadata $classMetadata
     * @param $object
     *
     * @return int|string|null
     */
    private function getId(ClassMetadata $classMetadata, $object)
    {
        $identifiers = $classMetadata->getIdentifierValues($object);
        if (0 === count($identifiers)) {
            return null;
        }

        $id = reset($identifiers);

        return is_numeric($id) ? (int)$id : $id;
    }

    private function getUsername()
    {
        if (!$token = $this->tokenStorage->getToken()) {
            return null;
        }

        /** @var UserInterface|string|null $user */
        $user = $token->getUser();
        if (!is_object($user)) {
            return null;
        }

        return $user->getUsername();
    }

    private function processMtMCollection(PersistentCollection $scheduledCollectionUpdate, EntityManagerInterface $em, array $mapping)
    {
        $metadataClass = $em->getClassMetadata($mapping['targetEntity']);
        $field = $mapping['fieldName'];

        $oldData = [];
        $newData = [];
        foreach ($scheduledCollectionUpdate->getSnapshot() as $item) {
            $oldData[] = $this->getId($metadataClass, $item);
        }
        foreach ($scheduledCollectionUpdate as $item) {
            $newData[] = $this->getId($metadataClass, $item);
        }

        $oid = spl_object_hash($scheduledCollectionUpdate->getOwner());
        $this->manyToManyData[$oid]['entity'] = $scheduledCollectionUpdate->getOwner();
        $this->manyToManyData[$oid]['changeset'][$field] = [$oldData, $newData];
    }
}
