<?php

namespace acall\repositories;

class targetsRepository
{
    public function __construct($mailAdapterRepository, $smsAdapterRepository)
    {
        $this->mailAdapterRepository    = $mailAdapterRepository;
        $this->smsAdapterRepository     = $smsAdapterRepository;
    }

    public function sendNotificationToTargets($serviceId, $healthStatus, $targetsList) {
        foreach($targetsList as $target) 
        {
            if($target['type'] == 'sms')
            {
                $this->smsAdapterRepository->sendSms($target['number'], $serviceId, $healthStatus);
            }
            elseif($target['type'] == 'mail')
            {
                $this->mailAdapterRepository->sendMail($target['email'], $serviceId, $healthStatus);
            }
        }
    }

}