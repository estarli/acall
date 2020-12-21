<?php

namespace acall\adapters;

interface mailAdapterRepository
{
    public function sendMail($email, $serviceId, $healthStatus);
}