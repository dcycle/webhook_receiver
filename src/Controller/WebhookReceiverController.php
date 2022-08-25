<?php

namespace Drupal\webhook_receiver\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\webhook_receiver\WebhookReceiver;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\webhook_receiver\Utilities\Mockables;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\webhook_receiver\Payload\PayloadInterface;
use Drupal\webhook_receiver\Payload\PayloadFactoryInterface;

/**
 * Controller for the /admin/reports/status/[token] request.
 */
class WebhookReceiverController extends ControllerBase {

  use Mockables;

  /**
   * The injected webhook_receiver service.
   *
   * @var \Drupal\webhook_receiver\WebhookReceiver
   */
  protected $webhookReceiver;

  /**
   * The injected request stack, to get POST data.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The injected payload factory.
   *
   * @var \Drupal\webhook_receiver\Payload\PayloadFactoryInterface
   */
  protected $payloadFactory;

  /**
   * Constructs a new WebhookReceiverController object.
   *
   * @param \Drupal\webhook_receiver\WebhookReceiver $webhook_receiver
   *   An injected webhook_receiver service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   An injected request stack.
   * @param \Drupal\webhook_receiver\Payload\PayloadFactoryInterface $payload_factory
   *   An injected request stack.
   */
  public function __construct(WebhookReceiver $webhook_receiver, RequestStack $request_stack, PayloadFactoryInterface $payload_factory) {
    $this->webhookReceiver = $webhook_receiver;
    $this->requestStack = $request_stack;
    $this->payloadFactory = $payload_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // PHPStan complains that using this should make the class final, but
    // this is widely used in Drupal, for example in
    // ./core/lib/Drupal/Core/Entity/Controller/EntityController.php.
    // Declaring the class final would make it unmockable.
    // @phpstan-ignore-next-line
    return new static(
      $container->get('webhook_receiver'),
      $container->get('request_stack'),
      $container->get('webhook_receiver.payload_factory'),
    );
  }

  /**
   * Process a request.
   *
   * @param string $plugin_id
   *   A plugin ID.
   * @param string $token
   *   A token.
   * @param bool $simulate
   *   Whether to simulate the processing of a request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A response for output as JSON.
   */
  public function process(string $plugin_id, string $token, bool $simulate) {
    $data = $this->webhookReceiver->process($plugin_id, $token, $simulate, $this->payload());
    $ret = new JsonResponse($data);
    if (!empty($data['code'])) {
      $ret->setStatusCode($data['code']);
    }
    return $ret;
  }

  /**
   * Get the payload as a string.
   *
   * @return \Drupal\webhook_receiver\Payload\PayloadInterface
   *   The payload.
   */
  public function payload() : PayloadInterface {
    return $this->payloadFactory->fromString($this->payloadString());
  }

  /**
   * Get the payload as a string.
   *
   * @return string
   *   The brute payload.
   */
  public function payloadString() : string {
    return $this->requestStack->getCurrentRequest()->getContent();
  }

  /**
   * Simulate the processing of a request.
   *
   * @param string $plugin_id
   *   A plugin ID.
   * @param string $token
   *   A token.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A response for output as JSON.
   */
  public function simulate(string $plugin_id, string $token) {
    return $this->process($plugin_id, $token, simulate: TRUE);
  }

}
