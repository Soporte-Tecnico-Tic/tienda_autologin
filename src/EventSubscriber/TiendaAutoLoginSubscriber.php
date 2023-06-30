<?php
namespace Drupal\tienda_autologin\EventSubscriber;

use GuzzleHttp\Exception\RequestException;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;

class TiendaAutoLoginSubscriber implements EventSubscriberInterface {

  /**
   * Auto login del usuario
   */
  public function externalAuthLoginRegister(GetResponseEvent $event) {
    $config =  \Drupal::config('tienda_autologin.configuration');
    $authentication = \Drupal::service('tienda_autologin.externalauth');

    $op_edit_usuario_extenal = \Drupal::request()->query->get('op_edit_usuario_extenal');
    if (!empty($op_edit_usuario_extenal)) {
      \Drupal::messenger()->addMessage(t('Se guardaron los datos del usuario con exito'));
    }
  }

  /**
   * Verifica el status del usuario mediante la cookie
   */
  public function getLoginStatus($cookie_value, $format = 'json') {
    $config =  \Drupal::config('tienda_autologin.configuration');
    $api_url = $config->get('backend_url');
    $url_scheme = parse_url($api_url);

    try {
      $response = \Drupal::httpClient()->get("{$api_url}/user/login_status?_format={$format}", [
        'headers' => [
          'Accept' => 'application/json', 
          'Content-Type' => 'application/json',
          'Cookie' => $cookie_value
        ],
        'verify' => boolval($config->get('certificate_url'))
      ]);
      $status_user = $response->getBody()->getContents();
      return $status_user;
    } catch (RequestException $e) {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['externalAuthLoginRegister', 200];
    return $events;
  }

}
