<?php

namespace Drupal\webhook_receiver\WebhookReceiverLog;

/**
 * A log whose results are shown as a result to the webhook caller.
 */
class WebhookReceiverLog implements WebhookReceiverLogInterface {

  /**
   * The errors.
   *
   * @var array
   */
  protected $errors;

  /**
   * The debug messages.
   *
   * @var array
   */
  protected $debug;

  /**
   * Constructor.
   */
  public function __construct() {
    $this->errors = [];
    $this->debug = [];
  }

  /**
   * {@inheritdoc}
   */
  public function err(string $string) {
    $this->errors[] = $string;
  }

  /**
   * {@inheritdoc}
   */
  public function debug(string $string) {
    $this->debug[] = $string;
  }

  /**
   * {@inheritdoc}
   */
  public function toArray() : array {
    return [
      'errors' => $this->errors,
      'debug' => $this->debug,
    ];
  }

}
