<?php

namespace Drupal\Tests\webhook_receiver\Unit\Controller;

use Drupal\webhook_receiver\Controller\WebhookReceiverController;
use PHPUnit\Framework\TestCase;

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
