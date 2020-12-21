<?php

use PHPUnit\Framework\TestCase;
use acall\adapters\persistenceAdapterRepository;
use acall\repositories\targetsRepository;
use acall\adapters\escalationPolicyAdapterRepository;
use acall\adapters\timerAdapterRepository;

include_once("../domainLogic.php");
include_once("../adapters/persistenceAdapterRepository.php");
include_once("../repositories/targetsRepository.php");
include_once("../adapters/escalationPolicyAdapterRepository.php");
include_once("../adapters/timerAdapterRepository.php");

class domainLogicTest extends TestCase
{
   public function test_GivenAMonitoredServiceHealthy__ReceivesAndAlert_BecomesUnhealthy_NotifyFirstLevel_Sets15MinutesAck()
   {
        $serviceId = 1;

        $persistenceAdapterRepositoryMock = $this->createMock(persistenceAdapterRepository::class);
        $persistenceAdapterRepositoryMock->method("getServiceStatusHealth")->willReturn('healthy');

        $escalationPolicyAdapterREpository = $this->createMock(escalationPolicyAdapterRepository::class);

        $targetsRepository = $this->createMock(targetsRepository::class);

        $timerAdapterRepository = $this->createMock(timerAdapterRepository::class);

        $domainLogic = new domainLogic($persistenceAdapterRepositoryMock, $escalationPolicyAdapterREpository, $targetsRepository, $timerAdapterRepository);
        $this->assertEquals(1, $domainLogic->manageAlertFromMonitoredService($serviceId, 'unhealthy'));
      }

   public function test_GivenAMonitoredServiceUnhealthy_NotAcknowledged_NotLastLevelNotified_WhenReceivesAcknowledgementTimeout_PagerNotifiesTargetsLastLevel_AndSetTimeout()
      {
         $serviceId = 1;

         $persistenceAdapterRepositoryMock = $this->createMock(persistenceAdapterRepository::class);
         $persistenceAdapterRepositoryMock->method("getServiceStatusHealth")->willReturn('unhealthy');
         $persistenceAdapterRepositoryMock->method("isServiceAcnkoledged")->willReturn(false);
         $persistenceAdapterRepositoryMock->method("getLastLevelNotified")->willReturn(1);

         $escalationPolicyAdapterREpository = $this->createMock(escalationPolicyAdapterRepository::class);
         $escalationPolicyAdapterREpository->method("getLastLetvelPolicy")->willReturn(2);
         $tartegts = array(
            array('type' => 'email', 'email' => 'testemail@email.com'),
            array('type' => 'sms', 'number' => '+34123456789'),
            array('type' => 'email', 'email' => 'testemail2@email.com'),
         );
         $escalationPolicyAdapterREpository->method("getTargetsToNotifyByLevel")->willReturn($tartegts);

         $targetsRepository = $this->createMock(targetsRepository::class);

         $timerAdapterRepository = $this->createMock(timerAdapterRepository::class);

         $domainLogic = new domainLogic($persistenceAdapterRepositoryMock, $escalationPolicyAdapterREpository, $targetsRepository, $timerAdapterRepository);
         $this->assertEquals(1, $domainLogic->manageAcknowledgementTimeout($serviceId));
      }

      public function test_GivenAMonitoredServiceUnhealthyAcknoledged_WhenReceivesAcknowledgementTimeout_NotNotify_NotTimeout()
      {
         $serviceId = 1;

         $persistenceAdapterRepositoryMock = $this->createMock(persistenceAdapterRepository::class);
         $persistenceAdapterRepositoryMock->method("getServiceStatusHealth")->willReturn('unhealthy');
         $persistenceAdapterRepositoryMock->method("isServiceAcnkoledged")->willReturn(true);
         $persistenceAdapterRepositoryMock->method("getLastLevelNotified")->willReturn(1);


         $escalationPolicyAdapterREpository = $this->createMock(escalationPolicyAdapterRepository::class);
         $escalationPolicyAdapterREpository->method("getLastLetvelPolicy")->willReturn(2);

         $targetsRepository = $this->createMock(targetsRepository::class);

         $timerAdapterRepository = $this->createMock(timerAdapterRepository::class);

         $domainLogic = new domainLogic($persistenceAdapterRepositoryMock, $escalationPolicyAdapterREpository, $targetsRepository, $timerAdapterRepository);
         $this->assertEquals(0, $domainLogic->manageAcknowledgementTimeout($serviceId));
      }

      public function test_GivenAMonitoredServiceUnhealthy__ReceivesAndAlert_NotNotify_NotSetAcknowledgementTimeout()
   {
      
        $serviceId = 1;

        $persistenceAdapterRepositoryMock = $this->createMock(persistenceAdapterRepository::class);
        $persistenceAdapterRepositoryMock->method("getServiceStatusHealth")->willReturn('unhealthy');

        $escalationPolicyAdapterREpository = $this->createMock(escalationPolicyAdapterRepository::class);

        $targetsRepository = $this->createMock(targetsRepository::class);

        $timerAdapterRepository = $this->createMock(timerAdapterRepository::class);

        $domainLogic = new domainLogic($persistenceAdapterRepositoryMock, $escalationPolicyAdapterREpository, $targetsRepository, $timerAdapterRepository);
        $this->assertEquals(0, $domainLogic->manageAlertFromMonitoredService($serviceId, 'unhealthy'));
        $this->assertEquals(0, $domainLogic->manageAlertFromMonitoredService($serviceId, 'healthy'));
      }

      public function test_GivenAMonitoredServiceUnhealthy_ThatReceiesHealthyEvent_ReceivesAcknowledgementTimeout_BecomesHealthy_NotNotify_NotSetDelayTimeout()
      {
         $serviceId = 1;

         $persistenceAdapterRepositoryMock = $this->createMock(persistenceAdapterRepository::class);
         $persistenceAdapterRepositoryMock->method("getServiceStatusHealth")->willReturn('healthy');


         $escalationPolicyAdapterREpository = $this->createMock(escalationPolicyAdapterRepository::class);

         $targetsRepository = $this->createMock(targetsRepository::class);

         $timerAdapterRepository = $this->createMock(timerAdapterRepository::class);

         $domainLogic = new domainLogic($persistenceAdapterRepositoryMock, $escalationPolicyAdapterREpository, $targetsRepository, $timerAdapterRepository);
         $this->assertEquals(2, $domainLogic->manageAcknowledgementTimeout($serviceId));
      }
}
