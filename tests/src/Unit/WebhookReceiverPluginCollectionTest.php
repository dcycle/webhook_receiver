<?php

namespace Drupal\Tests\webhook_receiver\Unit;

use Drupal\webhook_receiver\WebhookReceiverPluginCollection;
use PHPUnit\Framework\TestCase;

/**
 * Test WebhookReceiverPluginCollection.
 *
 * @group webhook_receiver
 */
class WebhookReceiverPluginCollectionTest extends TestCase {

  /**
   * Test for pluginDefinitions().
   *
   * @param string $message
   *   The test message.
   * @param array $input
   *   The mock definitions.
   * @param array $expected
   *   The expected output.
   *
   * @cover ::pluginDefinitions
   * @dataProvider providerPluginDefinitions
   */
  public function testPluginDefinitions(string $message, array $input, array $expected) {
    $object = $this->getMockBuilder(WebhookReceiverPluginCollection::class)
      // NULL = no methods are mocked; otherwise list the methods here.
      ->setMethods([
        'pluginManager',
      ])
      ->disableOriginalConstructor()
      ->getMock();

    // @codingStandardsIgnoreStart
    $object->method('pluginManager')
      ->willReturn(new class($input) {
        public function __construct($input) {
          $this->input = $input;
        }
        public function getDefinitions() {
          return $this->input;
        }
      });

    $output = $object->pluginDefinitions();

    if ($output != $expected) {
      print_r([
        'message' => $message,
        'output' => $output,
        'expected' => $expected,
      ]);
    }

    $this->assertTrue($output == $expected, $message);
    // @codingStandardsIgnoreEnd
  }

  /**
   * Provider for testPluginDefinitions().
   */
  public function providerPluginDefinitions() {
    return [
      [
        'message' => 'Already sorted',
        'input' => [
          'a' => [
            'weight' => 1,
          ],
          'b' => [
            'weight' => 2,
          ],
        ],
        'expected' => [
          'a' => [
            'weight' => 1,
          ],
          'b' => [
            'weight' => 2,
          ],
        ],
      ],
      [
        'message' => 'Not sorted',
        'input' => [
          'a' => [
            'weight' => 3,
          ],
          'b' => [
            'weight' => 2,
          ],
        ],
        'expected' => [
          'b' => [
            'weight' => 2,
          ],
          'a' => [
            'weight' => 3,
          ],
        ],
      ],
    ];
  }

  /**
   * Test for plugins().
   *
   * @param string $message
   *   The test message.
   * @param array $input
   *   The input.
   * @param array $expected
   *   The expected result; ignored if an exception is expected.
   *
   * @cover ::plugins
   * @dataProvider providerPlugins
   */
  public function testPlugins(string $message, array $input, array $expected) {
    $object = $this->getMockBuilder(WebhookReceiverPluginCollection::class)
      // NULL = no methods are mocked; otherwise list the methods here.
      ->setMethods([
        'pluginDefinitions',
        'pluginManager',
      ])
      ->disableOriginalConstructor()
      ->getMock();

    // @codingStandardsIgnoreStart
    $object->method('pluginDefinitions')
      ->willReturn($input);
    $object->method('pluginManager')
      ->willReturn(new class() {
        function __construct() {}
        function createInstance($x) {
          return 'instance of ' . $x;
        }
      });

    $object->expects($this->once())
      ->method('pluginDefinitions');
    $output = $object->plugins(TRUE);
    $object->expects($this->never())
      ->method('pluginDefinitions');
    $output2 = $object->plugins();

    $this->assertTrue($output == $output2, 'static memory works.');

    if ($output != $expected) {
      print_r([
        'output' => $output,
        'expected' => $expected,
      ]);
    }

    $this->assertTrue($output == $expected, $message);
    // @codingStandardsIgnoreEnd
  }

  /**
   * Provider for testPlugins().
   */
  public function providerPlugins() {
    return [
      [
        'message' => 'Plugins exist',
        'input' => [
          'plugin_id_1' => 'whatever',
          'plugin_id_2' => 'whatever',
        ],
        'expected' => [
          'plugin_id_1' => 'instance of plugin_id_1',
          'plugin_id_2' => 'instance of plugin_id_2',
        ],
      ],
      [
        'message' => 'No plugin',
        'input' => [],
        'expected' => [],
      ],
    ];
  }

}
