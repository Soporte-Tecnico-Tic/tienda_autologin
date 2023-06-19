<?php
namespace Drupal\tienda_autologin\EventSubscriber;

use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TiendaAutoLoginSubscriber implements EventSubscriberInterface {

  /**
   * Auto login del usuario
   */
  public function externalAuthLoginRegister(GetResponseEvent $event) {
    $config =  \Drupal::config('tienda_autologin.configuration');

    $authentication = \Drupal::service('tienda_autologin.externalauth');
    if (!empty($_COOKIE['tienda_autologin'])) {
      $cookie_value = $_COOKIE['tienda_autologin'];
      $status_user = $this->getLoginStatus($cookie_value);
  
      if (!$status_user) {
        user_logout();
      }
      else {
        $user_values = $authentication->getCurrentUser($cookie_value);
        $user_values = reset($user_values);

        //actualizar la session si el usuario es distinto
        if (\Drupal::currentUser()->getEmail() != $user_values['mail'][0]['value']) {
          $email = $user_values['mail'][0]['value'];
          if ($account = user_load_by_mail($email)) {
            user_login_finalize($account);
          }
          else {
            $account = User::create();
            foreach ($user_values as $key => $datas) {
              foreach ($datas as $data) {
                $account->set($key, $data['value']);
              }
            }
  
            $account->enforceIsNew();
            $account->save();
            user_login_finalize($account);
          }
        }
      }
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
        'verify' => $url_scheme['scheme'] == 'https' ? true : false
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
