<?php

namespace Ruspanzer\LoggableBundle\Entity\Interfaces;

interface LoggableInterface
{
    public function getId(): ?int;

    /**
     * @return LoggableInterface[]
     */
    public function getRelatedLogEntities(): array;
}
