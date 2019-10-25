<?php

namespace Ruspanzer\LoggableBundle\Event;

use Ruspanzer\LoggableBundle\Entity\Log;
use Symfony\Component\EventDispatcher\Event;

class CreateLogEvent extends Event
{
    private $log;

    public function __construct(Log $log)
    {
        $this->log = $log;
    }

    public function getLog(): Log
    {
        return $this->log;
    }
}
