<?php

namespace Drupal\Tests\webhook_receiver\Unit;

use Drupal\webhook_receiver\WebhookReceiverPluginBase;
use PHPUnit\Framework\TestCase;

/**
 * Test WebhookReceiverPluginBase.
 *
 * @group webhook_receiver
 */
class WebhookReceiverPluginBaseTest extends TestCase {

  /**
   * Smoke test.
   */
  public function testSmoke() {
    $object = $this->getMockBuilder(WebhookReceiverPluginBase::class)
      // NULL = no methods are mocked; otherwise list the methods here.
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $this->assertTrue(is_object($object));
  }

}
