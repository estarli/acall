<?php

use acall\adapters\persistenceAdapterRepository;
use acall\repositories\targetsRepository;
use acall\adapters\escalationPolicyAdapterRepository;
use acall\adapters\timerAdapterRepository;

class domainLogic
{
    private $persistenceAdapterRepository;
    private $escalationPolicyAdapterRepository;
    private $targetsRepository;
    private $timerAdapterRepository;

    function __construct(
        persistenceAdapterRepository $persistenceAdapterRepository, 
        escalationPolicyAdapterRepository $escalationPolicyAdapterRepository, 
        targetsRepository $targetsRepository,
        timerAdapterRepository $timerAdapterRepository
        ) 
    {
        $this->persistenceAdapterRepository         = $persistenceAdapterRepository;
        $this->escalationPolicyAdapterRepository    = $escalationPolicyAdapterRepository;
        $this->targetsRepository                    = $targetsRepository;
        $this->timerAdapterRepository               = $timerAdapterRepository;
    }

    public function manageAlertFromMonitoredService($serviceId, $alertStatus)
    {
        $serviceStatusHealth = $this->persistenceAdapterRepository->getServiceStatusHealth($serviceId);
        if($serviceStatusHealth == 'healthy' && $alertStatus == 'unhealthy') {
            $this->persistenceAdapterRepository->setServiceStatusHealth($serviceId, 'unhealthy');
            $this->notifyAndSetTimer($serviceId, $serviceStatusHealth, 1);
            $this->persistenceAdapterRepository->setLastLevelNotified($serviceId, 1);
            return 1;
        }

        return 0;
    }

    public function manageAcknowledgementTimeout($serviceId)
    {
        $serviceStatusHealth    = $this->persistenceAdapterRepository->getServiceStatusHealth($serviceId);
        $isServiceAcknoledged   = $this->persistenceAdapterRepository->isServiceAcnkoledged($serviceId);
        $isLastLevelNotified    = $this->isLastLevelNotified($serviceId);

        if($serviceStatusHealth == 'unhealthy')
        {
            if(!$isServiceAcknoledged && !$isLastLevelNotified)
            {
                $nextEscalationLevelNotified    = $this->persistenceAdapterRepository->getLastEscalationLevelNotified($serviceId);
                $this->notifyAndSetTimer($serviceId, $serviceStatusHealth, $nextEscalationLevelNotified);
                return 1;
            }
        }
        elseif ($serviceStatusHealth == 'healthy')
        {
            $this->persistenceAdapterRepository->setServiceStatusHealth($serviceId, 'healthy');
            return 2;
        }

        return 0;
    }

    private function notifyAndSetTimer($serviceId, $serviceStatusHealth, $level)
    {
        $targets = $this->escalationPolicyAdapterRepository->getTargetsToNotifyByLevel($serviceId, $level);
        $this->targetsRepository->sendNotificationToTargets($serviceId, $serviceStatusHealth, $targets);
        $this->timerAdapterRepository->setTimer(900);
    }

    private function isLastLevelNotified($serviceId)
    {
        $lastLevelNotified  = $this->persistenceAdapterRepository->getLastLevelNotified($serviceId);
        $getLastLevelEP     = $this->escalationPolicyAdapterRepository->getLastLetvelPolicy($serviceId);

        return $lastLevelNotified == $getLastLevelEP;
    }

}