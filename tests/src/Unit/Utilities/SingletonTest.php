<?php

namespace Drupal\Tests\webhook_receiver\Unit\Utilities;

use Drupal\webhook_receiver\Utilities\Singleton;
use PHPUnit\Framework\TestCase;

// @codingStandardsIgnoreStart
class DummySingletonObject {
  use Singleton;

}
// @codingStandardsIgnoreEnd

/**
 * Test Singleton.
 *
 * @group webhook_receiver
 */
class SingletonTest extends TestCase {

  /**
   * Smoke test.
   */
  public function testSmoke() {
    $this->assertTrue(DummySingletonObject::instance() === DummySingletonObject::instance());
  }

}
