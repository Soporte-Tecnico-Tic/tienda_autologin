<?php

namespace Drupal\tienda_decoupled;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use GuzzleHttp\Exception\RequestException;
use Drupal\user\UserInterface;
use Psr\Log\LoggerInterface;
use Drupal\Component\Serialization\Json;
use Psr\Http\Message\ResponseInterface;
use Drupal\Core\Url;
use Drupal\user\Entity\User;

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
    $this->config =  \Drupal::config('tienda_decoupled.configuration');
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
   * Autenticar en microservicio
   */
  public function getUser($user_uid) {
    try {
      $response = $this->client->get("{$this->api_url}/user/{$user_uid}?_format=json");
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
  protected function getLoginStatusUrlString($format = 'json') {
    $user_login_status_url = Url::fromRoute('user.login_status.http');
    $user_login_status_url->setRouteParameter('_format', $format);
    $user_login_status_url->setAbsolute();
    return $user_login_status_url->toString();
  }

  /**
   * {@inheritdoc}
   * Autenticar en microservicio
   */
  public function load($user_name, $user_pass, $format='json') {
    try {     
      $response = $this->client->post("{$this->api_url}/user/login?_format=json", [
       'body' => Json::Encode([
          'name' => "{$user_name}",
          'pass' => "{$user_pass}"
        ]),
        'headers' => [
          'Accept' => 'application/json', 
          'Content-Type' => 'application/json',
          'X-CSRF-Token' => $this->getTokenAccess()
        ],
        'verify' => false,
       // 'cookies' => $this->cookies,
      ]);

      $data = Json::Decode($response->getBody()->getContents());

      $response = $this->client->get("{$this->api_url}/user/login_status?_format=json", ['verify' => false]);
      $status_user = $response->getBody()->getContents();

      $account_data = [];

      //$account = user_load_by_name($data['current_user']['name']);
      $authmap = \Drupal::service('externalauth.authmap');
      $externalauth = \Drupal::service('externalauth.externalauth');
      $provider = 'tienda_decoupled';

      // loginRegister will only make a new account if one does not exist.
      $account = $externalauth->loginRegister($data['current_user']['name'], $provider, $account_data);
      setcookie("tiendadecoupleduser", json_encode(["u" => $data['current_user']['name'], "s" => $status_user]), \Drupal::time()->getRequestTime()+3600);

      if (empty($data)) {
        return FALSE;
      }
      else {

        return $data;
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
    return;
  }
}
