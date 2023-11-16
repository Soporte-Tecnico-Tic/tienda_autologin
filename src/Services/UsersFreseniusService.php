<?php
namespace Drupal\tienda_autologin\Services;

class UsersFreseniusService {

    public function post(array $data) {
        if(!empty($data)) {
            //Connection info:
          $prod_endpoint = 'https://www.usuariosfresenius.com/api/v1/user/register';
//          $dev_endpoint = 'https://usuariosfresenius.creacionwebprofesional.com/api/v1/user/register';
          $dev_endpoint = 'https://usuariosfresenius.ddev.site/api/v1/user/register';

            //Prepare data
            $body = json_encode($data);
          $token_jwt = 'vhQjw7CaceTMhfGGpYI+tGKXBI7BSc926QzKnhMdlUr/XhL+3RDABf6XeNRbQ2QsyiKljXJbdKjwa/6smmVF2w==';

            $client = \Drupal::httpClient();
            $headers = [
                'Authorization: Bearer ' . $token_jwt,
                'Content-Type' => 'application/json'
            ];
                      $response = $client->post($dev_endpoint, [
                'body' => $body,
                'headers' => $headers
            ]);


            $code = $response->getStatusCode();


            return $code;

        }else {
            return "Empty data.";
        }
    }
}
