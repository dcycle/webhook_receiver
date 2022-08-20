<?php

namespace Drupal\Tests\webhook_receiver\Unit\Utilities;

use Drupal\webhook_receiver\Utilities\Mockables;
use PHPUnit\Framework\TestCase;

// @codingStandardsIgnoreStart
class DummyMockablesObject {
  use Mockables;

}
// @codingStandardsIgnoreEnd

/**
 * Test Mockables.
 *
 * @group webhook_receiver
 */
class MockablesTest extends TestCase {

  /**
   * Smoke test.
   */
  public function testSmoke() {
    $object = new DummyMockablesObject();

    $this->assertTrue(is_object($object));
  }

}
