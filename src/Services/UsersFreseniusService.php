<?php
namespace Drupal\tienda_autologin\Services;

class UsersFreseniusService {

    public function post(array $data) {
        if(!empty($data)) {
            //Connection info:
          $prod_endpoint = 'https://www.usuariosfresenius.com/api/v1/user/register';
          $dev_endpoint = 'https://usuariosfresenius.creacionwebprofesional.com/api/v1/user/register';

            //Prepare data
            $body = json_encode($data);

            $client = \Drupal::httpClient();

            $headers = [
                'Content-Type' => 'application/json'
            ];

            $response = $client->post($dev_endpoint, [
                'body' => $body,
                'headers' => $headers
            ]);


          return $response->getStatusCode();
        }else {
            return "Empty data.";
        }
    }
}
