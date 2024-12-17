<?php

namespace Drupal\webhook_receiver\WebhookReceiverSecurity;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Site\Settings;
use Drupal\Core\State\State;

/**
 * Manage tokens for webhooks.
 *
 * This class will work whether or not the sodium extension is installed.
 */
class WebhookReceiverSecurity extends WebhookReceiverSecurityBase {

  /**
   * The injected security implementer which uses Sodium.
   *
   * @var \Drupal\webhook_receiver\WebhookReceiverSecurity\WebhookReceiverSecuritySodium
   */
  protected $securitySodium;

  /**
   * The injected security implementer which does not use Sodium.
   *
   * @var \Drupal\webhook_receiver\WebhookReceiverSecurity\WebhookReceiverSecurityNoSodium
   */
  protected $securityNoSodium;

  /**
   * Constructs a new WebhookReceiver object.
   *
   * @param \Drupal\Core\State\State $state
   *   An injected state service.
   */
  public function __construct(
    State $state,
    WebhookReceiverSecuritySodium $securitySodium,
    WebhookReceiverSecurityNoSodium $securityNoSodium,
  ) {
    parent::__construct($state);
    $this->securitySodium = $securitySodium;
    $this->securityNoSodium = $securityNoSodium;
  }

  /**
   * Check whether the Sodium PHP extension is installed.
   *
   * On certain Red Hat systems, it is possible that the Sodium extension
   * is not installed.
   *
   * @return bool
   *   TRUE if the Sodium extension is installed, FALSE otherwise.
   */
  public function sodiumIsInstalled() : bool {
    return defined('SODIUM_CRYPTO_SECRETBOX_KEYBYTES');
  }

  /**
   * Get the security implementation.
   *
   * @return \Drupal\webhook_receiver\WebhookReceiverSecurity\WebhookReceiverSecurityInterface
   *   The security implementation.
   */
  public function securityImplementation() : WebhookReceiverSecurityInterface {
    if ($this->sodiumIsInstalled()) {
      return $this->securitySodium;
    }
    else {
      return $this->securityNoSodium;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function safeEncrypt(string $message, string $key) : string {
    return $this->securityImplementation()->safeEncrypt($message, $key);
  }

  /**
   * {@inheritdoc}
   */
  public function safeDecrypt(string $encrypted, string $key) : string {
    return $this->securityImplementation()->safeDecrypt($encrypted, $key);
  }

  /**
   * {@inheritdoc}
   */
  public function encryptionKey() : string {
    return $this->securityImplementation()->encryptionKey();
  }

}
