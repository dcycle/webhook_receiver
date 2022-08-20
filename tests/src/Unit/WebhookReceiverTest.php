<?php

namespace Drupal\Tests\webhook_receiver\Unit;

use Drupal\webhook_receiver\WebhookReceiver;
use PHPUnit\Framework\TestCase;

/**
 * Test WebhookReceiver.
 *
 * @group webhook_receiver
 */
class WebhookReceiverTest extends TestCase {

  /**
   * Smoke test.
   */
  public function testSmoke() {
    $object = $this->getMockBuilder(WebhookReceiver::class)
      // NULL = no methods are mocked; otherwise list the methods here.
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $this->assertTrue(is_object($object));
  }

}
