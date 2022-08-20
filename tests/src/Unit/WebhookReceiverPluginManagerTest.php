<?php

namespace Drupal\Tests\webhook_receiver\Unit;

use Drupal\webhook_receiver\WebhookReceiverPluginManager;
use PHPUnit\Framework\TestCase;

/**
 * Test WebhookReceiverPluginManager.
 *
 * @group webhook_receiver
 */
class WebhookReceiverPluginManagerTest extends TestCase {

  /**
   * Smoke test.
   */
  public function testSmoke() {
    $object = $this->getMockBuilder(WebhookReceiverPluginManager::class)
      // NULL = no methods are mocked; otherwise list the methods here.
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $this->assertTrue(is_object($object));
  }

}
