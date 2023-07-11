<?php

namespace Drupal\webhook_receiver\SelfTest;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Extension\ExtensionList;
use Drupal\Core\File\FileSystemInterface;
use Drupal\webhook_receiver\Payload\PayloadFactoryInterface;
use Drupal\webhook_receiver\WebhookReceiver;

/**
 * Finds the directory "request-response-test" and runs the tests therein.
 *
 * Each module can have a request-response-test directory.
 */
class RequestResponseTest {

  const SUBDIR = 'request-response-test';

  use SelfTestLogTrait;

  /**
   * The injected file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The injected app singleton.
   *
   * @var \Drupal\webhook_receiver\WebhookReceiver
   */
  protected $app;

  /**
   * The injected extension list.
   *
   * @var \Drupal\Core\Extension\ExtensionList
   */
  protected $extensionList;

  /**
   * The injected payload factory.
   *
   * @var \Drupal\webhook_receiver\Payload\PayloadFactoryInterface
   */
  protected $payloadFactory;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   The injected file system.
   * @param \Drupal\webhook_receiver\WebhookReceiver $app
   *   The injected app singleton.
   * @param \Drupal\Core\Extension\ExtensionList $extensionList
   *   The injected extension list.
   * @param \Drupal\webhook_receiver\Payload\PayloadFactoryInterface $payloadFactory
   *   The injected payload factory.
   */
  public function __construct(FileSystemInterface $fileSystem, WebhookReceiver $app, ExtensionList $extensionList, PayloadFactoryInterface $payloadFactory) {
    $this->fileSystem = $fileSystem;
    $this->app = $app;
    $this->extensionList = $extensionList;
    $this->payloadFactory = $payloadFactory;
  }

  /**
   * Test an individual file for a module.
   *
   * @param string $module_name
   *   A module name.
   * @param string $file
   *   A file path such as
   *   modules/custom/webhook_receiver/modules/webhook_receiver_example/request-response-test/01/request.json.
   *   The files passed should always be request.json files, with associated
   *   response.json files.
   */
  public function testFile(string $module_name, string $file) {
    $this->log('Checking ' . $file);
    $this->log('');
    $this->log('The request is =>');
    $this->log('');
    $this->log($req = file_get_contents($file));
    $this->log('');
    $this->log('The plugin ID is =>');
    $this->log('');
    $this->log($plugin_id = file_get_contents(str_replace('request.json', 'plugin_id.txt', $file)));
    $this->log('');
    $this->log('The response is =>');
    $this->log('');
    $this->log($response = $this->toGenericResponse($this->app->process($plugin_id, $this->app->webhookReceiverSecurity()->token($plugin_id), TRUE, $this->payloadFactory->fromString($req))));
    $this->log('');
    $this->log('The EXPECTED response is =>');
    $this->log('');
    $this->log($expected = trim(file_get_contents(str_replace('request.json', 'response.json', $file))));
    $this->log('');
    if (JSON::decode($expected) == JSON::decode($response)) {
      $this->log('Response is as expected! Moving on');
    }
    else {
      $this->err('Response is NOT as expected :(');
    }
    $this->log('');
  }

  /**
   * Take a real response from the system and make it generic.
   *
   * Remove the timestamp.
   *
   * @param array $response
   *   A response from the system.
   *
   * @return string
   *   A generic response, as JSON.
   */
  public function toGenericResponse(array $response) : string {
    $ret = $response;

    if (array_key_exists('time', $response)) {
      $ret['time'] = 'SYSTEM_TIME';
    }
    if (isset($response['log']['errors'][0]['id'])) {
      $ret['log']['errors'][0]['id'] = 'THIS-IS-A-UUID';
    }

    return JSON::encode($ret);
  }

  /**
   * Runs tests in /path/to/module_name/request-response-test.
   *
   * @param string $module_name
   *   An active module.
   */
  public function run(string $module_name) {
    try {
      $path = $this->modulePath($module_name);
      $this->log($module_name . ' is valid, moving on.');
      $this->log('It is at ' . $path);
      $candidate = $path . '/' . self::SUBDIR;
      $this->log('Tests should be at ' . $candidate);
      $allFiles = $this->fileSystem->scanDirectory($candidate, '/request\.json/');
      foreach ($allFiles as $file) {
        $this->testFile($module_name, $file->uri);
      }
      $this->log('Yay! all good for ' . $module_name);
    }
    catch (\Throwable $t) {
      $this->err($t->getMessage());
    }
  }

  /**
   * Log an error and exit with an error exit code.
   *
   * @param mixed $mixed
   *   Anything that's loggable.
   */
  public function err($mixed) {
    $this->log(' --- ERROR --- ');
    $this->log($mixed);
    die();
  }

  /**
   * Log a message.
   *
   * @param mixed $mixed
   *   Anything that's loggable.
   */
  public function log($mixed) {
    if (is_string($mixed)) {
      print($mixed . PHP_EOL);
    }
    else {
      print_r($mixed);
    }
  }

  /**
   * Get a module path.
   *
   * @param string $module_name
   *   A module name.
   *
   * @return string
   *   A module path.
   */
  public function modulePath(string $module_name) {
    $candidate = $this->extensionList->getPath($module_name);

    if ($candidate) {
      return $candidate;
    }

    throw new \Exception($module_name . ' does not seem to be active.');
  }

}
