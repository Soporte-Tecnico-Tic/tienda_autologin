<?php
namespace Drupal\tienda_autologin\Services;

use Drupal\Component\Serialization\Json;

class UsersFreseniusService {

  public function post(array $data) {
    if (!empty($data)) {
      $config = \Drupal::config('tienda_autologin.configuration');
      $url_api = $config->get('backend_url');

      //Prepare data
      $body = Json::encode($data);
      $client = \Drupal::httpClient();

      $headers = [
        'Content-Type' => 'application/json',
      ];

      $response = $client->post($url_api . '/api/v1/user', [
        'body' => $body,
        'headers' => $headers,
      ]);

      return $response->getStatusCode();
    }
    else {
      return "Empty data.";
    }
  }

}
