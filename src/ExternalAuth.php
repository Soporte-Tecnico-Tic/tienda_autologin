<?php

namespace Drupal\tienda_autologin;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use GuzzleHttp\Exception\RequestException;
use Drupal\user\UserInterface;
use Psr\Log\LoggerInterface;
use Drupal\Component\Serialization\Json;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Service to handle external authentication logic.
 */
class ExternalAuth {
  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The Drupal user account.
   *
   * @var \Drupal\user\Entity\User
   */
  private $account;

  /**
   * 
   */
  private $config;

  /**
   * 
   */
  private $api_url;

  /**
   * 
   */
  private $api_token;

  /**
   * 
   */
  private $client;

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, LoggerInterface $logger, EventDispatcherInterface $event_dispatcher) {
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger;
    $this->eventDispatcher = $event_dispatcher;
    $this->config =  \Drupal::config('tienda_autologin.configuration');
    $this->api_url = $this->config->get('backend_url');
    $this->client = \Drupal::httpClient();
  }

  /**
   * Check if user is excluded.
   *
   * @param \Drupal\user\UserInterface $account
   *   A Drupal user object.
   *
   * @return bool
   *   TRUE if user should be excluded from LDAP provision/syncing
   */
  public function excludeUser(UserInterface $account): bool {
    if ($this->config->get('skipRoles')) {
      $roles = $this->config->get('skipRoles');
      if (!empty(array_intersect($account->getRoles(), $roles))) {
        return TRUE;
      }
    }
    return $account;
  }

  /**
   * {@inheritdoc}
   * Autenticar en microservicio
   */
  public function getTokenAccess() {
    try {
      $response = $this->client->get("{$this->api_url}/session/token");
      return (string) $response->getBody();
    }
    catch (RequestException $e) {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   * Obtener la información del usuario
   */
  public function getCurrentUser($access_token_external, $format = 'json') {
    try {
      $response = $this->client->get("{$this->api_url}/current-user?_format={$format}", [
        'headers' => [
          'Accept' => 'application/json', 
          'Content-Type' => 'application/json',
          'Authorization' => "Bearer {$access_token_external}",
          'cache-control' => 'no-cache'
        ],
        'verify' => boolval($this->config->get('certificate_url'))
      ]);

      $data = Json::Decode($response->getBody()->getContents());

      if (empty($data)) {
        return FALSE;
      }
      else {
        return $data;
      }
    } catch (RequestException $e) {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   * Obtener la información del usuario
   */
  public function getUser($access_token_external, $user_uid, $format = 'json') {
    try {
      $response = $this->client->get("{$this->api_url}/user/{$user_uid}?_format={$format}", [
        'headers' => [
          'Accept' => 'application/json', 
          'Content-Type' => 'application/json',
          'Authorization' => "Bearer {$access_token_external}",
          'cache-control' => 'no-cache'
        ],
        'verify' => boolval($this->config->get('certificate_url'))
      ]);

      $data = Json::Decode($response->getBody()->getContents());

      if (empty($data)) {
        return FALSE;
      }
      else{
        return $data;
      }
    } catch (RequestException $e) {
      return FALSE;
    }
  }
  

  /**
   * Gets the URL string for checking login for a given serialization format.
   *
   * @param string $format
   *   The format to use to make the request.
   *
   * @return string
   *   The URL string.
   */
  public function getLoginStatus($access_token_external, $format = 'json') {
    $response = $this->client->get("{$this->api_url}/user/login_status?_format={$format}", [
      'headers' => [
        'Accept' => 'application/json', 
        'Content-Type' => 'application/json',
        'Authorization' => "Bearer {$access_token_external}",
        'cache-control' => 'no-cache'
      ],
      'verify' => boolval($this->config->get('certificate_url'))
      ]);
     $status_user = $response->getBody()->getContents();
     return $status_user;
  }

  /**
   * {@inheritdoc}
   * Autenticar en microservicio
   */
  public function load($user_name, $user_pass, $format='json') {
    try {
      $result = $this->client->post("{$this->api_url}/user/login?_format={$format}", [
        'body' => Json::Encode([
          'name' => "{$user_name}",
          'pass' => "{$user_pass}"
        ]),
        'headers' => [
          'Accept' => "application/{$format}",
          'Content-Type' => "application/{$format}",
          'X-CSRF-Token' => $this->getTokenAccess()
        ],
        'http_errors' => FALSE,
        'verify' => boolval($this->config->get('certificate_url')),
      ]);

      $has_authenticate = false;
      $content['body'] = Json::Decode($result->getBody()->getContents());

      if (!empty($content['body']) && !empty($content['body']['access_token'])) {
        return $content;
      }
      else {
        return ['error' => $content['body']];
      }
    } catch (RequestException $e) {
      if (!$e->hasResponse()) {
        throw $e;
      }
      $response = $e->getResponse();
      $data = Json::Decode($response->getBody()->getContents());
      return ["error" => $data["message"]];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function login($user_name, $user_pass) {
    $response = $this->load($user_name, $user_pass);

    if (!empty($response["error"])) {
      return $response;
    }
    return $response;
  }
}