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
  protected $_errors;

  /**
   * The debug messages.
   *
   * @var array
   */
  protected $_debug;

  /**
   * Constructor.
   */
  public function __construct() {
    $this->_errors = [];
    $this->_debug = [];
  }

  /**
   * {@inheritdoc}
   */
  public function err(string $string) {
    $this->_errors[] = $string;
  }

  /**
   * {@inheritdoc}
   */
  public function debug(string $string) {
    $this->_debug[] = $string;
  }

  /**
   * {@inheritdoc}
   */
  public function toArray() : array {
    return [
      'errors' => $this->_errors,
      'debug' => $this->_debug,
    ];
  }

}
