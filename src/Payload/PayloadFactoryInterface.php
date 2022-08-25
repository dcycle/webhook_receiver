<?php

namespace Drupal\webhook_receiver\Payload;

/**
 * The payload factory is used to generate payload objects.
 */
interface PayloadFactoryInterface {

  /**
   * Given a string, return a Payload object.
   *
   * @param string $payload_string
   *   A payload string.
   *
   * @return \Drupal\webhook_receiver\Payload\PayloadInterface
   *   A payload object.
   */
  public function fromString(string $payload_string) : PayloadInterface;

}
