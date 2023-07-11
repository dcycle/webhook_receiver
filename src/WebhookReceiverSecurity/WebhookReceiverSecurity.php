<?php

namespace Drupal\webhook_receiver\WebhookReceiverSecurity;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Site\Settings;
use Drupal\Core\State\State;

/**
 * Manage tokens for webhooks.
 */
class WebhookReceiverSecurity {

  const STATE_VAR = 'webform_receiver_security';

  /**
   * Encrypt a message.
   *
   * See https://stackoverflow.com/a/30159120/1207752.
   *
   * @param string $message
   *   The unencrypted string.
   * @param string $key
   *   The encryption key which must be 32 bytes.
   *
   * @return string
   *   The encrypted string.
   */
  public function safeEncrypt(string $message, string $key) : string {
    if (mb_strlen($key, '8bit') !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
      throw new \Exception('Key is not the correct size (must be 32 bytes).');
    }
    $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

    $cipher = base64_encode($nonce . sodium_crypto_secretbox($message, $nonce, $key));
    sodium_memzero($message);
    sodium_memzero($key);
    return $cipher;
  }

  /**
   * Decrypt a message.
   *
   * See https://stackoverflow.com/a/30159120/1207752.
   *
   * @param string $encrypted
   *   The encrypted message.
   * @param string $key
   *   They encryption key.
   *
   * @return string
   *   The unencrypted message.
   */
  public function safeDecrypt(string $encrypted, string $key) : string {
    $decoded = base64_decode($encrypted);
    $nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
    $ciphertext = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, NULL, '8bit');

    $plain = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);
    if (!is_string($plain)) {
      throw new \Exception("The encryption key used to decrypt the string does not seem to be the same as the encryption key used to encrypt it. This can happen, for example, if you have changed your site's hash salt in settings.php. The token cannot be retrieved; please regenerate it using webhook_receiver()->deleteToken(id)");
    }
    sodium_memzero($ciphertext);
    sodium_memzero($key);
    return $plain;
  }

  /**
   * Remove a token from the database.
   *
   * @param string $id
   *   A token to remove from the database.
   */
  public function removeToken(string $id) {
    $id = (trim($id));

    $tokens = $this->allEncryptedTokens();
    unset($tokens[$id]);
    $this->state->set(self::STATE_VAR, $tokens);
  }

  /**
   * The injected state store.
   *
   * @var \Drupal\Core\State\State
   */
  protected $state;

  /**
   * Constructs a new WebhookReceiver object.
   *
   * @param \Drupal\Core\State\State $state
   *   An injected state service.
   */
  public function __construct(State $state) {
    $this->state = $state;
  }

  /**
   * Get the encryption key.
   *
   * Use the encryption key from the settings.php file's hash salt, never
   * store it in the database, in case the database is compromised.
   *
   * @return string
   *   An encryption key derived from the hash salt.
   */
  public function encryptionKey() : string {
    $hash_salt = mb_substr(Settings::getHashSalt(), 0, SODIUM_CRYPTO_SECRETBOX_KEYBYTES);

    if (!$hash_salt) {
      throw new \Exception("Webhook Receiver requires that the sites's hash salt (often defined in settings.php) not be empty.");
    }

    $hash_salt_32 = mb_substr($hash_salt, 0, 32);

    if (mb_strlen($hash_salt_32) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
      throw new \Exception('The hash salt (defined in settings.php seems to be less than 32 bytes long. Webhook Receiver requires a hash salt of at least 32 bytes.)');
    }

    return $hash_salt_32;
  }

  /**
   * Get all encrypted tokens keyed by id.
   *
   * @return array
   *   All encyprted tokens keyed by id.
   */
  public function allEncryptedTokens() : array {
    $tokens = $this->state->get(self::STATE_VAR, []);

    if (!is_array($tokens)) {
      $tokens = [];
    }

    return $tokens;
  }

  /**
   * Get a token for a key, create a new one if it does not exist.
   *
   * @param string $key
   *   A key to which this token should be associated.
   *
   * @return string
   *   The decrypted token.
   */
  public function token(string $key) : string {
    $key = (trim($key));

    if (!$key) {
      throw new \Exception('Webhook key must not be empty. The key is the plugin id, for example webhook_receiver_example_log_payload.');
    }

    $tokens = $this->allEncryptedTokens();

    $encryption_key = $this->encryptionKey();

    if (empty($tokens[$key]) || !is_string($tokens[$key])) {
      $candidate = $this->createNewToken($key);
    }
    else {
      try {
        // Decrypt tokens because they are encrypted at rest.
        $candidate = $this->safeDecrypt($tokens[$key], $encryption_key);
      }
      catch (\Throwable $t) {
        $candidate = $this->createNewToken($key);
      }
    }

    if (!$candidate) {
      throw new \Exception('Something went wrong, the token should not be empty');
    }

    return $candidate;
  }

  /**
   * Create a new token, encrypted it and save it to the database.
   *
   * If a token for this key already exists, it will be overwritten.
   *
   * @param string $key
   *   A key to which this token should be associated.
   *
   * @return string
   *   The unencrypted token.
   */
  public function createNewToken(string $key) : string {
    $key = (trim($key));

    $unencrypted = Crypt::hashBase64(random_bytes(128));
    $tokens = $this->allEncryptedTokens();
    // Encrypt newly-generated tokens following the principle of encryption
    // at rest.
    $tokens[$key] = $this->safeEncrypt($unencrypted, $this->encryptionKey());
    $this->state->set(self::STATE_VAR, $tokens);
    return $unencrypted;
  }

}
