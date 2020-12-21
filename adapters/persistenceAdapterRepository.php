<?php

namespace acall\adapters;

interface persistenceAdapterRepository
{
    public function getServiceStatusHealth($serviceId);

    public function setServiceStatusHealth($serviceId, $health);

    public function getLastLevelNotified($serviceId);

    public function setLastLevelNotified($serviceId, $level);

    public function isServiceAcnkoledged($serviceId);

    public function isLastLevelNotified($false);

    public function getLastEscalationLevelNotified($serviceId);

}