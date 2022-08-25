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

  /**
   * Test for validatePath().
   *
   * @param string $message
   *   The test message.
   * @param array $array
   *   The array.
   * @param array $path
   *   The path.
   * @param mixed $default
   *   The default value.
   * @param callable $callback
   *   The callback.
   * @param bool $expected
   *   The expected result.
   *
   * @cover ::validatePath
   * @dataProvider providerValidatePath
   */
  public function testValidatePath(string $message, array $array, array $path, $default, callable $callback, bool $expected) {
    $object = $this->getMockBuilder(ArrayPathfinder::class)
      // NULL = no methods are mocked; otherwise list the methods here.
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $output = $object->validatePath($array, $path, $default, $callback);

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
   * Provider for testValidatePath().
   */
  public function providerValidatePath() {
    return [
      [
        'message' => 'FALSE callback returns FALSE.',
        'array' => [],
        'path' => [],
        'default' => '',
        'callback' => function ($x) {
          return FALSE;
        },
        'expected' => FALSE,
      ],
      [
        'message' => 'TRUE callback returns TRUE.',
        'array' => [],
        'path' => [],
        'default' => '',
        'callback' => function ($x) {
          return FALSE;
        },
        'expected' => FALSE,
      ],
    ];
  }

  /**
   * Test for get().
   *
   * @param string $message
   *   The test message.
   * @param array $array
   *   The input array.
   * @param array $path
   *   The path.
   * @param mixed $default
   *   The default result.
   * @param mixed $expected
   *   The expected output.
   *
   * @cover ::get
   * @dataProvider providerGet
   */
  public function testGet(string $message, array $array, array $path, $default, $expected) {
    $object = $this->getMockBuilder(ArrayPathfinder::class)
      // NULL = no methods are mocked; otherwise list the methods here.
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $output = $object->get($array, $path, $default);

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
   * Provider for testGet().
   */
  public function providerGet() {
    return [
      [
        'message' => 'Base case',
        'array' => [],
        'path' => [],
        'default' => '',
        'expected' => '',
      ],
    ];
  }

}
