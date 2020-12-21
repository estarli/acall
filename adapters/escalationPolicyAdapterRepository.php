<?php

namespace acall\adapters;

interface escalationPolicyAdapterRepository
{
    public function getTargetsToNotifyByLevel($serviceId, $level);

    public function getLastLetvelPolicy($serviceId);

}