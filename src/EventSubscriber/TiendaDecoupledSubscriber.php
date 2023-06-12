<?php
namespace Drupal\tienda_decoupled\EventSubscriber;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TiendaDecoupledSubscriber implements EventSubscriberInterface {

  public function externalAuthLoginRegister(GetResponseEvent $event) {
    $config =  \Drupal::config('tienda_decoupled.configuration');
    $api_url = $config->get('backend_url');

    $client = \Drupal::httpClient();

    $user_name = "";
    if (!empty($_COOKIE["tiendadecoupleduser"])) {
      $data = json_decode($_COOKIE["tiendadecoupleduser"]);
      $user_name = $data->u;

      $response = $client->get("{$api_url}/user/login_status?_format=json", ['verify' => false]);
      $status_user = $response->getBody()->getContents();
  
      if ($status_user) {
        $account_data = [];
        //$account = user_load_by_name($data['current_user']['name']);
        $authmap = \Drupal::service('externalauth.authmap');
        $externalauth = \Drupal::service('externalauth.externalauth');
        $provider = 'tienda_decoupled_auth';
      
        // loginRegister will only make a new account if one does not exist.
        $account = $externalauth->loginRegister($user_name, $provider, $account_data);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['externalAuthLoginRegister'];
    return $events;
  }

}
