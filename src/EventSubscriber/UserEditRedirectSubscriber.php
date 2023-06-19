<?php
namespace Drupal\tienda_autologin\EventSubscriber;

use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * User edit redirect subscriber to redirect to external site.
 */
class UserEditRedirectSubscriber implements EventSubscriberInterface {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The route provider.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param \Drupal\Core\Routing\RouteProviderInterface $route_provider
   *   The route provider.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(RouteMatchInterface $route_match, RouteProviderInterface $route_provider, EntityTypeManagerInterface $entity_type_manager) {
    $this->routeMatch = $route_match;
    $this->routeProvider = $route_provider;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * If current route is considered content entity edit route.
   *
   * @return bool
   *   True if url is considered content entity url, false otherwise.
   */
  protected function isUserEditRoute() {
    // Entity edit url has one single route parameter, which matches the entity
    // type of that entity. Check if number of route parameters equals one.
    $route_parameters = $this->routeMatch->getParameters()->all();
    if (count($route_parameters) !== 1) {
      return FALSE;
    }
    // Drupal content entities use to follow the pattern for their edit
    // routes which is usually entity.{entity_type}.edit_form so check if
    // pattern matches.
    $entity_type = key($route_parameters);
    return $this->routeMatch->getRouteName() == 'entity.user.edit_form';
  }

  /**
   * Handles the entity edit redirect.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   The event to process.
   */
  public function onRespondEntityEditRedirect(ResponseEvent $event) {

    // Current route must be an entity edit route.
    if (!$this->isUserEditRoute()) {
      return;
    }
    // Get entity as it is first route parameter for entity edit routes.
    $route_parameters = $this->routeMatch->getParameters()->all();

    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = reset($route_parameters);

    // Build user edit url.
    $config =  \Drupal::config('tienda_autologin.configuration');
    if ($api_url = $config->get('backend_url')) {
      if (!empty($_COOKIE['tienda_autologin'])) {
        $cookie_value = $_COOKIE['tienda_autologin'];
        $authentication = \Drupal::service('tienda_autologin.externalauth');
        $status_user = $authentication->getLoginStatus($cookie_value);
        if ($status_user) {
          $user_values = $authentication->getCurrentUser($cookie_value);
          $user_values = reset($user_values);
          $uid = $user_values['uid'][0]['value'];
    
          $host = \Drupal::request()->getSchemeAndHttpHost();
          $host = urlencode($host);
          $user_edit_url = "{$api_url}/user/{$uid}/edit?destination={$host}";
    
          // Redirect to configured remote.
          $response = new TrustedRedirectResponse($user_edit_url, 301);
          $event->setResponse($response);
          $event->stopPropagation();
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Run as soon as possible.
    $events[KernelEvents::RESPONSE][] = ['onRespondEntityEditRedirect', 220];
    return $events;
  }

}
