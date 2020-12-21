<?php

namespace acall\adapters;

interface smsAdapterRepository
{
    public function sendSms($number, $serviceId, $healthStatus);

}