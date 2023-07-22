<?php
namespace Drupal\tienda_autologin\EventSubscriber;

use Drupal\user\Entity\User;
use GuzzleHttp\Exception\RequestException;
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
    $session = \Drupal::request()->getSession();

    $config =  \Drupal::config('tienda_autologin.configuration');
    $authentication = \Drupal::service('tienda_autologin.externalauth');

    $current = \Drupal::currentUser();


    if (!\Drupal::currentUser()->isAnonymous()) {


      if ($reponse_external = $session->get('tienda_autologin_response_external', null)) {
        $access_token_external = $reponse_external['access_token'];

        //cerrar la session del sitio si la de microservicios esta cerrada
        if ($status_user = $authentication->getLoginStatus($access_token_external)) {

        }
        else {
       //   user_logout();
        }
      }
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
