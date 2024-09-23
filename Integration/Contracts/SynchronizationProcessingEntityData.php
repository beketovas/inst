<?php

namespace Modules\Integration\Contracts;

interface SynchronizationProcessingEntityData
{
    public function getTransformingData() : array;

    public function getCreatedAt();
}
