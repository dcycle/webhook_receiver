<?php

namespace Drupal\Tests\webhook_receiver\Unit\WebhookReceiverProcessor;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\webhook_receiver\WebhookReceiverProcessor\WebhookReceiverProcessor;
use Drupal\webhook_receiver\WebhookReceiver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

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
