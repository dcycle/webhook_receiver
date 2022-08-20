<?php

namespace Drupal\Tests\webhook_receiver\Unit\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\webhook_receiver\Controller\WebhookReceiverController;
use Drupal\webhook_receiver\WebhookReceiver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Test WebhookReceiverController.
 *
 * @group webhook_receiver
 */
class WebhookReceiverControllerTest extends TestCase {

  /**
   * Smoke test.
   */
  public function testSmoke() {
    $object = $this->getMockBuilder(WebhookReceiverController::class)
      // NULL = no methods are mocked; otherwise list the methods here.
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $this->assertTrue(is_object($object));
  }

}
