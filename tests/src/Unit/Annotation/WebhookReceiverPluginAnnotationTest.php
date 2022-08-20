<?php

namespace Drupal\Tests\webhook_receiver\Unit\Annotation;

use Drupal\webhook_receiver\Annotation\WebhookReceiverPluginAnnotation;
use PHPUnit\Framework\TestCase;

/**
 * Test WebhookReceiverPluginAnnotation.
 *
 * @group webhook_receiver
 */
class WebhookReceiverPluginAnnotationTest extends TestCase {

  /**
   * Smoke test.
   */
  public function testSmoke() {
    $object = $this->getMockBuilder(WebhookReceiverPluginAnnotation::class)
      // NULL = no methods are mocked; otherwise list the methods here.
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $this->assertTrue(is_object($object));
  }

}
