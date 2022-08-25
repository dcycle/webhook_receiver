<?php

namespace Drupal\Tests\webhook_receiver\Unit\Processor;

use Drupal\webhook_receiver\Processor\Processor;
use PHPUnit\Framework\TestCase;

/**
 * Test Processor.
 *
 * @group webhook_receiver
 */
class ProcessorTest extends TestCase {

  /**
   * Smoke test.
   */
  public function testSmoke() {
    $object = $this->getMockBuilder(Processor::class)
      // NULL = no methods are mocked; otherwise list the methods here.
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $this->assertTrue(is_object($object));
  }

}
