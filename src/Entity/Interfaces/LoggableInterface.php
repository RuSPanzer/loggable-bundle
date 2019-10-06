<?php
/**
 * Created by PhpStorm.
 * User: ruspa
 * Date: 11.09.2018
 * Time: 21:18.
 */

namespace Ruspanzer\LoggableBundle\Entity\Interfaces;

interface LoggableInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return LoggableInterface[]
     */
    public function getRelatedLogEntities(): array;
}
