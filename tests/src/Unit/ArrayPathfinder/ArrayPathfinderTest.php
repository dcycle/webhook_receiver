<?php

namespace Drupal\Tests\webhook_receiver\Unit\ArrayPathfinder;

use Drupal\webhook_receiver\ArrayPathfinder\ArrayPathfinder;
use PHPUnit\Framework\TestCase;

/**
 * Test ArrayPathfinder.
 *
 * @group webhook_receiver
 */
class ArrayPathfinderTest extends TestCase {

  /**
   * Test for unset().
   *
   * @param string $message
   *   The test message.
   * @param array $input
   *   The input array.
   * @param array $path
   *   The path to unset.
   * @param array $expected
   *   The expected result.
   *
   * @cover ::unset
   * @dataProvider providerUnset
   */
  public function testUnset(string $message, array $input, array $path, array $expected) {
    $object = $this->getMockBuilder(ArrayPathfinder::class)
      // NULL = no methods are mocked; otherwise list the methods here.
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $output = $input;

    $object->unset($output, $path);

    if ($output != $expected) {
      print_r([
        'message' => $message,
        'output' => $output,
        'expected' => $expected,
      ]);
    }

    $this->assertTrue($output == $expected, $message);
  }

  /**
   * Provider for testUnset().
   */
  public function providerUnset() {
    return [
      [
        'message' => 'Smoke test',
        'input' => [],
        'path' => [],
        'expected' => [],
      ],
      [
        'message' => 'Basic test',
        'input' => [
          'a' => [
            'b' => 'c',
          ],
        ],
        'path' => ['a', 'b'],
        'expected' => [
          'a' => [],
        ],
      ],
    ];
  }

}
