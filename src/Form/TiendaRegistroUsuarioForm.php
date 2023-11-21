<?php
namespace Drupal\tienda_autologin\Form;

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
    $form['#prefix'] = '<div id="my-form-wrapper">';
    $form['#suffix'] = '</div>';

    $form['field_nombre'] =  array(
      '#type' => 'textfield',
      '#title' => $this->t('Nombre'),
      '#title_display' => FALSE,
      '#attributes' => [
        'placeholder' => 'Nombre*',
        'class' => ['form-element']
      ],
      '#required' => TRUE
    );
    $form['field_apellidos'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Apellidos'),
      '#title_display' => FALSE,
      '#attributes' => [
        'placeholder' => 'Apellidos*',
        'class' => ['form-element']
      ],
      '#required' => TRUE
    ];
    $options = $this->_getTerminos('genero');
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
        'class' => ['form-element']
      ],
      '#required' => TRUE
    ];
    $options = $this->_getTerminos('especialidad');
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
        'class' => ['form-element']
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
        'class' => ['form-element']
      ],
      '#required' => TRUE
    ];
    $form['field_provincia'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Provincia'),
      '#title_display' => FALSE,
      '#attributes' => [
        'placeholder' => 'Provincia*',
        'class' => ['form-element']
      ],
      '#required' => TRUE
    ];
    $form['field_telefono'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Teléfono'),
      '#title_display' => FALSE,
      '#attributes' => [
        'placeholder' => 'Teléfono',
        'class' => ['form-element']
      ]
    ];
    $form['field_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Correo electrónico'),
      '#title_display' => FALSE,
      '#attributes' => [
        'placeholder' => 'Correo electrónico*',
        'class' => ['form-element']
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
    $genero_tid = $this->_getTid('genero', $values['field_genero']); // ej 2
    $especialidad_tid = $this->_getTid('especialidad', $values['field_especialidad']);

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
      'bool_numero_colegiado' => $bool_colegiado,
      'pais' => $values['field_pais'],
      'provincia' => $values['field_provincia'],
      'telefono' => $values['field_telefono'] ?? NULL,
      'bool_tratamiento_datos' => 'true',//$values['field_tratamiento_de_datos'][0]['value'],
      'especialidad' => $especialidad_tid,
      'genero' => $genero_tid,
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

  private function _getTerminos($vid): array
  {
    $terms = \Drupal::entityQuery('taxonomy_term')
      ->condition('status', 1)
      ->condition('vid', $vid)
      ->execute();

    $terminos = [];
    if (sizeof($terms) > 0) {
      foreach ($terms as $_term) {
        $term = Term::load($_term);

        if (!is_null($term)) {
          $terminos[$term->id()] = $term->getName();

        }
      }
    }

    return $terminos;
  }
  function _getTid($vid, $real_tid) {
    //Get Tid
    $custom_field = 'field_api_id';
    $tid = 0;

    $terms =\Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadTree($vid, 0, 2, TRUE);
    foreach ($terms as $term) {
      foreach ($term->getFields(false) as $field) {
        if ($field->getName() == $custom_field && $term->get('tid')->value == $real_tid) {
          $tid = $term->get('field_api_id')->value;
        }
      }
    }

    return $tid;
  }

}
