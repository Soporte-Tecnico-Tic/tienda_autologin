<?php
  namespace Drupal\tienda_autologin\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\Role;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Component\Serialization\Json;

/**
 * Configure example settings for this site.
 */
class TiendaAutoLoginForm extends ConfigFormBase {
  /**  
   * {@inheritdoc}  
   */  
  protected function getEditableConfigNames() {  
    return [  
      'tienda_autologin.configuration',  
    ];  
  } 

  /** 
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tienda_autologin_configuration_form';
  }


  /** 
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('tienda_autologin.configuration');  

    $form['backend_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Url to Back end'),
      '#description' => $this->t('La url del backedn, algo parecido a <em>https://myfrontend.com</em>'),
      '#default_value' => $config->get('backend_url'),
      '#attributes' => [
        'placeholder' => 'https://example.com',
      ],
    ];

    $roles = Role::loadMultiple();
    $options = ['-- Ninguno --'];
    foreach ($roles as $rol) {
      $options[$rol->id()] = $rol->label();
    }
    $options['administrator'] = 'Administrator';

    $form['roles_exclude'] = [
      '#type' => 'select',
      '#title' => $this->t('Roles exclude'),
      '#description' => $this->t('Los usuarios que posean los roles seleccionado podran loguearse sin conectarse al microservicio si existen en la DB del sitio'),
      '#default_value' => $config->get('roles_exclude'),
      '#options' => $options,
      '#multiple' => true
    ];

    $form['validation_message_site_autologin'] = [
      '#title' => t('Mensaje de validación - error en el inicio de session'),
      '#type' => 'text_format',
      '#description' => t('Mensaje que se ha de mostrar al usuario cuando se loguee y se presente un error'),
      '#default_value' => $config->get('validation_message_site_autologin.value'),
      '#format' => $config->get('validation_message_site_autologin.format'),
      '#rows' => 5,
      '#required' => TRUE,
    ];

    $form['validation_message_site_account_missing'] = [
      '#title' => t('Mensaje de validación - Cuenta no existe al hacer login'),
      '#type' => 'text_format',
      '#description' => t('Mensaje que se ha de mostrar al usuario cuando se intente loguear con una cuenta no existente'),
      '#default_value' => $config->get('validation_message_site_account_missing.value'),
      '#format' => $config->get('validation_message_site_account_missing.format'),
      '#rows' => 5,
      '#required' => TRUE,
    ];


    $form['certificate_url'] = [
      '#type' => 'radios',
      '#title' => t('Verificar el certificado del HOST'),
      '#options' => [true => t("SI"), false => t('NO')],
      '#default_value' => $config->get('certificate_url')
    ];

    $form['debug_site_autologin'] = [
      '#type' => 'radios',
      '#title' => t('Guardar logs para debug del sitio'),
      '#options' => [true => t("SI"), false => t('NO')],
      '#default_value' => $config->get('debug_site_autologin')
    ];

    //$fields_options = [0 => t('Ninguno')];
    if ($all_bundle_fields = \Drupal::service('entity_field.manager')->getFieldDefinitions('user', 'user')) {
      foreach ($all_bundle_fields as $field_name => $field) {
        if(substr($field_name, 0, 6) === "field_") {
          $fields_options[$field_name] = $field->getLabel();
        }
      }
    }

    $form['pagina_ok_autologin'] = array(
      '#title' => t('Url de pagina OK'),
      '#type' => 'textfield',
      '#description' => t('Url de la pagina OK que se ha de mostrar durante el registro, por ejemplo /mi-sitio-pagina-ok'),
      '#required' => TRUE,
      '#default_value' => $config->get('pagina_ok_autologin')
    );

    $form['pagina_ok_resetpassword'] = array(
      '#title' => t('Url de pagina OK reset password'),
      '#type' => 'textfield',
      '#description' => t('Url de la pagina OK que se ha de mostrar luego del reset password, por ejemplo /mi-sitio-pagina-ok'),
      '#required' => TRUE,
      '#default_value' => $config->get('pagina_ok_resetpassword')
    );

    $form['pagina_ok_edituser'] = array(
      '#title' => t('Url de pagina OK de editar usuario'),
      '#type' => 'textfield',
      '#description' => t('Url de la pagina OK que se ha de mostrar luego del editar el usuario, por ejemplo /mi-sitio-pagina-ok'),
      '#required' => TRUE,
      '#default_value' => $config->get('pagina_ok_edituser')
    );
    return parent::buildForm($form, $form_state);
  }

  /** 
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);  
  
    $config = $this->config('tienda_autologin.configuration');
    if ($form_state->hasValue('backend_url')) {
      $keys = [
        'backend_url',
        'roles_exclude',
        'certificate_url',
        'validation_message_site_autologin',
        'validation_message_site_account_missing',
        'debug_site_autologin',
        'pagina_ok_autologin',
        'pagina_ok_resetpassword',
        'pagina_ok_edituser'
      ];

      //set values
      foreach ($keys as $key) {
        $this->config('tienda_autologin.configuration')->set($key, $form_state->getValue($key))->save();
      }
    }
  }
}
