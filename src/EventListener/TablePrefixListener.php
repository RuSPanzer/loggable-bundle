<?php

namespace Ruspanzer\LoggableBundle\EventListener;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Ruspanzer\LoggableBundle\Entity\Log;
use Ruspanzer\LoggableBundle\Entity\LogRelatedEntity;

class TablePrefixListener
{
    private $prefix;

    public function __construct(string $prefix)
    {
        $this->prefix = $prefix;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if (in_array($classMetadata->getReflectionClass()->getName(), [Log::class, LogRelatedEntity::class], true)) {
            $classMetadata->setPrimaryTable([
                'name' => sprintf('%s_%s', $this->prefix, $classMetadata->getTableName()),
            ]);
        }
    }
}
