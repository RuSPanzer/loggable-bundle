<?php

namespace Ruspanzer\LoggableBundle\Entity\Traits;

trait ExtraDataTrait
{
    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $extraData;

    public function setExtraData(array $extraData): self
    {
        $this->extraData = $extraData;

        return $this;
    }

    public function getExtraData($key = null, $default = null)
    {
        return $key ? ($this->extraData[$key] ?? $default) : $this->extraData;
    }

    public function addExtraData($key, $value, $unsetIsNull = true): self
    {
        if (null === $value && $unsetIsNull) {
            unset($this->extraData[$key]);
        } else {
            $this->extraData[$key] = $value;
        }

        return $this;
    }

    public function removeExtraData($key): self
    {
        return $this->addExtraData($key, null);
    }
}
