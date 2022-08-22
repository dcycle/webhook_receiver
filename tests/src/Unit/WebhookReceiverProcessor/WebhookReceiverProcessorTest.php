<?php

namespace Drupal\Tests\webhook_receiver\Unit\WebhookReceiverProcessor;

use Drupal\webhook_receiver\WebhookReceiverProcessor\WebhookReceiverProcessor;
use PHPUnit\Framework\TestCase;

/**
 * Test WebhookReceiverProcessor.
 *
 * @group webhook_receiver
 */
class WebhookReceiverProcessorTest extends TestCase {

  /**
   * Smoke test.
   */
  public function testSmoke() {
    $object = $this->getMockBuilder(WebhookReceiverProcessor::class)
      // NULL = no methods are mocked; otherwise list the methods here.
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $this->assertTrue(is_object($object));
  }

}
