<?php
namespace Drupal\tienda_autologin\Form;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\AlertCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\taxonomy\Entity\Term;
use Exception;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Configure example settings for this site.
 */
class TiendaRegistroUsuarioForm extends FormBase {


  public function getFormId() {
    // TODO: Implement getFormId() method.
    return 'tienda_autologin_registro_usuario';
  }

  function buildForm(array $form, FormStateInterface $form_state) {
    //para consumir los logos de fresenius y el sitio actual
    $config =  \Drupal::config('tienda_autologin.configuration');
    $url_api = $config->get('backend_url');
    $logo_fresenius = "";

    try {
      $cliente = \Drupal::httpClient();
      $request = $cliente->get($url_api . '/api/v1/logos');
      $response = $request->getBody()->getContents();
      $result = Json::decode($response);

      if (!empty($result)) {
        foreach ($result as $item) {
          if ($item['tipo'] == 'secundario') {
            $logo_fresenius = $item['url'];
          }
        }
      }
    }
    catch (\Exception $e) {
      \Drupal::logger('api_logos')->error($e->getMessage());
    }


    $form['#logo_fresenius_header'] = [
      '#type' => 'value',
      '#value' => $logo_fresenius
    ];


    $form['#prefix'] = '<div id="my-form-wrapper">';
    $form['#suffix'] = '</div>';

    $form['field_nombre'] =  array(
      '#type' => 'textfield',
      '#title' => $this->t('Nombre'),
      '#title_display' => FALSE,
      '#attributes' => [
        'placeholder' => 'Nombre*',
        'class' => ['form-element', 'form-item-name']
      ],
      '#required' => TRUE
    );
    $form['field_apellidos'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Apellidos'),
      '#title_display' => FALSE,
      '#attributes' => [
        'placeholder' => 'Apellidos*',
        'class' => ['form-element', 'form-item-apellidos']
      ],
      '#required' => TRUE
    ];
    $options = $this->getGeneros();
    $form['field_genero'] = [
      '#type' => 'select',
      '#title' => "Genero",
      '#title_display' => FALSE,
      '#options' => $options,
      '#empty_option' => 'Genero*',
      '#attributes' => [
        'class' => ['form-element', 'form-element--type-select']
      ],
      '#required' => TRUE
    ];
    $form['field_hospital'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Hospital'),
      '#title_display' => FALSE,
      '#attributes' => [
        'placeholder' => 'Hospital*',
        'class' => ['form-element', 'form-item-hospital']
      ],
      '#required' => TRUE
    ];
    $options = $this->getEspecialidades();
    $form['field_especialidad'] = [
      '#type' => 'select',
      '#title' => "Especialidad",
      '#title_display' => FALSE,
      '#options' => $options,
      '#empty_option' => 'Especialidad*',
      '#attributes' => [
        'class' => ['form-element', 'form-element--type-select']
      ],
      '#required' => TRUE
    ];
    $form['field_numero_de_colegiado'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Número de colegiado'),
      '#title_display' => FALSE,
      '#attributes' => [
        'placeholder' => 'Número de colegiado*',
        'class' => ['form-element', 'form-item-field-numero-de-colegiado-1-0-value']
      ],
      '#states' => [
        'invisible' => [
          ':input[name="field_no_tengo_numero_de_colegia"]' => ['checked' => TRUE],
        ],
        '#required' => TRUE
      ],
    ];
    $form['field_no_tengo_numero_de_colegia'] = [
      '#type' => 'checkbox',
      '#default_value' => false,
      '#description' => $this->t('No tengo número de colegiado'),
//      '#ajax' => [
//        'callback' => '::toggleTextField',
//        'wrapper' => 'edit-textfield-wrapper',
//      ],
    ];
    $form['field_pais'] = [
      '#type' => 'textfield',
      '#title' => $this->t('País'),
      '#title_display' => FALSE,
      '#attributes' => [
        'placeholder' => 'País*',
        'class' => ['form-element', 'form-item-pais']
      ],
      '#required' => TRUE
    ];
    $form['field_provincia'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Provincia'),
      '#title_display' => FALSE,
      '#attributes' => [
        'placeholder' => 'Provincia*',
        'class' => ['form-element', 'form-item-provincia']
      ],
      '#required' => TRUE
    ];
    $form['field_telefono'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Teléfono'),
      '#title_display' => FALSE,
      '#attributes' => [
        'placeholder' => 'Teléfono',
        'class' => ['form-element', 'form-type-tel']
      ]
    ];
    $form['field_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Correo electrónico'),
      '#title_display' => FALSE,
      '#attributes' => [
        'placeholder' => 'Correo electrónico*',
        'class' => ['form-element', 'form-item-mail']
      ],
      '#required' => TRUE
    ];
    $form['field_password'] = [
      '#type' => 'password',
      '#title' => $this->t('Contraseña'),
      '#title_display' => FALSE,
      '#attributes' => [
        'placeholder' => 'Contraseña*',
        'class' => ['form-element']
      ],
      '#required' => TRUE
    ];
    $form['field_acepto_la_politica_de_priv'] = [
      '#type' => 'checkbox',
      '#default_value' => 0,
      '#attributes' => [
        'class' => ['form-item__description']
      ],
      '#description' => $this->t('Consiento el tratamiento de mis datos. Fresenius SE, tratará sus datos con la finalidad de gestionar su solicitud de registro. Tiene derecho a acceder, rectificar y suprimir los datos , así como otros derechos, como se explica en la ') . Markup::create('<a href="/politica-de-privacidad">política de privacidad</a>')
    ];
    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => 'Crear nueva cuenta',
      '#attributes' => ['id' => 'submit_crear', 'class' => ['register-button', 'button--secondary']],
    ];

    $form['#theme'] = 'crear_tienda_autologin_form_template';

    return $form;
  }

  function submitForm(array &$form, FormStateInterface $form_state) {
//    parent::submitForm($form, $form_state); // TODO: Change the autogenerated stub
    $values = $form_state->getValues();

    $bool_colegiado = 'false';
    if($values['field_no_tengo_numero_de_colegia']) {
      if($values['field_no_tengo_numero_de_colegia'] == 1) {
        $bool_colegiado = 'true';
      }
    }

    $user_data = [
      'mail' => $values['field_email'],
      'pass' => $values['field_password'],
      'nombre' => $values['field_nombre'] ?? NULL,
      'apellidos' => $values['field_apellidos'] ?? NULL,
      'hospital' => $values['field_hospital'],
      'numero_colegiado' => $values['field_numero_de_colegiado'] ?? NULL,
      'bool_numero_colegiado' => $values['field_no_tengo_numero_de_colegia'],
      'pais' => $values['field_pais'],
      'provincia' => $values['field_provincia'],
      'telefono' => $values['field_telefono'] ?? NULL,
      'bool_tratamiento_datos' => $values['field_acepto_la_politica_de_priv'],
      'especialidad' => $values['field_especialidad'],
      'genero' => $values['field_genero'],
    ];

    try {
      $createUserFresenius = \Drupal::service('tienda_autologin.user_register');
      $createUserFresenius = $createUserFresenius->post($user_data);

      if($createUserFresenius == 200 ) {
        \Drupal::logger('enviar_registro_usuario')->notice($createUserFresenius);
        \Drupal::messenger()->addMessage('El usuario ha sido creado correctamente.');
      }
      elseif ($createUserFresenius == 405)
      {
        \Drupal::messenger()->addError('Ya existe un usuario con ese correo electrónico.');
      }
      else{
        \Drupal::messenger()->addError('El usuario no ha sido creado.');
      }

      $url = '/usuario/acceso';
      $response = new RedirectResponse($url);
      $response->send();
      exit();

    } catch (Exception $e) {
      \Drupal::logger('enviar_registro_usuario')->error($e->getMessage());
      \Drupal::messenger()->addError('El usuario no ha sido creado.');
    }
  }

  public function getGeneros() {
    $config =  \Drupal::config('tienda_autologin.configuration');
    $url_api = $config->get('backend_url');

    $cliente = \Drupal::httpClient();
    $request = $cliente->get($url_api . '/api/v1/genero');
    $response = $request->getBody()->getContents();
    $result = Json::decode($response);

    $data = [];
    if (!empty($result)) {
      foreach ($result as $res) {
        $data[$res['id']] = $res['name'];
      }
    }

    return $data;
  }

  public function getEspecialidades() {
    $config =  \Drupal::config('tienda_autologin.configuration');
    $url_api = $config->get('backend_url');

    $cliente = \Drupal::httpClient();
    $request = $cliente->get($url_api . '/api/v1/especialidad');
    $response = $request->getBody()->getContents();
    $result = Json::decode($response);

    $data = [];
    if (!empty($result)) {
      foreach ($result as $res) {
        $data[$res['id']] = $res['name'];
      }
    }

    return $data;
  }

}
