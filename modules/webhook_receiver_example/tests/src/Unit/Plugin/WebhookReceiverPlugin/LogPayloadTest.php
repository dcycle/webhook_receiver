<?php

namespace Drupal\Tests\webhook_receiver_example\Unit\Plugin\WebhookReceiverPlugin;

use Drupal\webhook_receiver_example\Plugin\WebhookReceiverPlugin\LogPayload;
use PHPUnit\Framework\TestCase;
use Drupal\webhook_receiver\WebhookReceiverLog\WebhookReceiverLog;

/**
 * Test LogPayload.
 *
 * @group webhook_receiver
 */
class LogPayloadTest extends TestCase {

  /**
   * Test for validatePayloadArray().
   *
   * @param string $message
   *   The test message.
   * @param array $input
   *   The input.
   * @param array $expected_errors
   *   An array of expected errors.
   * @param array $expected_debug_messages
   *   An array of expected debug messages.
   * @param bool $expected_result
   *   The expected result.
   * @param string $exception
   *   The exception class expected or an empty string if no exception is
   *   expected.
   *
   * @cover ::validatePayloadArray
   * @dataProvider providerValidatePayloadArray
   */
  public function testValidatePayloadArray(string $message, array $input, array $expected_errors, array $expected_debug_messages, bool $expected_result, string $exception) {
    $object = $this->getMockBuilder(LogPayload::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    if ($exception) {
      $this->expectException($exception);
    }

    $output = $object->validatePayloadArray($input, $log = new WebhookReceiverLog());

    if ($output != $expected_result) {
      print_r([
        'message' => $message,
        'output' => $output,
        'expected' => $expected_result,
      ]);
    }
    if ($log->toArray()['debug'] != $expected_debug_messages) {
      print_r([
        'message' => $output,
        'debug messages' => $log->_debug,
        'expected' => $expected_debug_messages,
      ]);
    }
    if ($log->toArray()['errors'] != $expected_errors) {
      print_r([
        'message' => $output,
        'errors' => $log->_err,
        'expected' => $expected_errors,
      ]);
    }

    $this->assertTrue($output == $expected_result, $message);
    $this->assertTrue($log->toArray()['errors'] == $expected_errors, $message);
    $this->assertTrue($log->toArray()['debug'] == $expected_debug_messages, $message);
  }

  /**
   * Provider for testValidatePayloadArray().
   */
  public function providerValidatePayloadArray() {
    return [
      [
        'message' => 'Required key does not exist',
        'input' => [],
        'expected errors' => [
          'We are expecting the payload to contain the key "' . LogPayload::PAYLOAD_REQUIRED_KEY . '".',
        ],
        'expected_debug_messages' => [],
        'expected result' => FALSE,
        'exception' => '',
      ],
      [
        'message' => 'Required key value is empty',
        'input' => [
          LogPayload::PAYLOAD_REQUIRED_KEY => '',
        ],
        'expected errors' => [
          'The payload contains the key "' . LogPayload::PAYLOAD_REQUIRED_KEY . '" but it is empty.',
        ],
        'expected_debug_messages' => [],
        'expected result' => FALSE,
        'exception' => '',
      ],
      [
        'message' => 'Required key value is not a string',
        'input' => [
          LogPayload::PAYLOAD_REQUIRED_KEY => [
            'this is an array, not a string :(',
          ],
        ],
        'expected errors' => [
          'The payload contains the key "' . LogPayload::PAYLOAD_REQUIRED_KEY . '" but it is not a string.',
        ],
        'expected_debug_messages' => [],
        'expected result' => FALSE,
        'exception' => '',
      ],
      [
        'message' => 'Required key is a string (happy path)',
        'input' => [
          LogPayload::PAYLOAD_REQUIRED_KEY => 'Yay, this is a string',
        ],
        'expected errors' => [],
        'expected_debug_messages' => [
          'The payload is valid.',
        ],
        'expected result' => TRUE,
        'exception' => '',
      ],
      [
        'message' => 'Exception thrown during validation',
        'input' => [
          LogPayload::PAYLOAD_REQUIRED_KEY => LogPayload::VALUE_TO_SIMULATE_EXCEPTION_ON_VALIDATE,
        ],
        'expected errors' => [],
        'expected_debug_messages' => [],
        'expected result' => TRUE,
        'exception' => '\Exception',
      ],
    ];
  }

}
