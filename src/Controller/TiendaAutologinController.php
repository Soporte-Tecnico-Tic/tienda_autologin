<?php
namespace Drupal\tienda_autologin\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\user\Entity\User;

/**
 * Provides route responses for the tienda_autologin module.
 */
class TiendaAutologinController extends ControllerBase {
  /**
   * Redirect user page
   */
  public function RedirectSiteExternal() {
    $config =  \Drupal::config('tienda_autologin.configuration');
    $session = \Drupal::request()->getSession();

    $current_user = \Drupal::currentUser();
    $user = User::load($current_user->id());

    $current_url = \Drupal::request()->getUri();
    $url_host_redirect = urlencode(\Drupal::request()->getSchemeAndHttpHost());
    $url_host_external = $config->get('backend_url');
    $pagina_ok_edituser = $config->get('pagina_ok_edituser');

    $url_redirect_external = "/";
    if (!\Drupal::currentUser()->isAnonymous()) {
      if ($reponse_external = $session->get('tienda_autologin_response_external', null)) {
        $jwt_token = urlencode(base64_encode($reponse_external["access_token"]));
        $url_redirect_external = "$url_host_external/syncautenticacionjwt/{$jwt_token}/edituser?redirect={$pagina_ok_edituser}&autologin=login";

        if ($config->get('debug_site_autologin')) {
          \Drupal::logger('tienda_autologin')->notice("url host redirect: " . print_r($url_redirect_external, 1));
          \Drupal::logger('tienda_autologin')->notice("url redirect external: " . print_r($url_host_redirect, 1));
          \Drupal::logger('tienda_autologin')->notice("jwt token: " . print_r($jwt_token, 1));
        }
      }
    }

    return new TrustedRedirectResponse($url_redirect_external);
  }
}