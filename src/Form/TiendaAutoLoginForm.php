<?php
  namespace Drupal\tienda_autologin\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\Role;
use Drupal\field\Entity\FieldStorageConfig;

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
    $form['roles_exclude'] = [
      '#type' => 'select',
      '#title' => $this->t('Roles exclude'),
      '#description' => $this->t('Los usuarios que posean los roles seleccionado podran loguearse sin conectarse al microservicio si existen en la DB del sitio'),
      '#default_value' => $config->get('roles_exclude'),
      '#options' => $options
    ];

    $form['certificate_url'] = [
      '#type' => 'radios',
      '#title' => t('Verificar el certificado del HOST'),
      '#options' => [true => t("SI"), false => t('NO')],
      '#default_value' => $config->get('certificate_url')
    ];

    //$fields_options = [0 => t('Ninguno')];
    if ($all_bundle_fields = \Drupal::service('entity_field.manager')->getFieldDefinitions('user', 'user')) {
      foreach ($all_bundle_fields as $field_name => $field) {
        if(substr($field_name, 0, 6) === "field_") {
          $fields_options[$field_name] = $field->getLabel();
        }
      }
    }
    $form['campos_disponibles_usuario_autologin'] = [
      '#type' => 'checkboxes',
      '#title' => t('Campos permitidos en el usuario'),
      '#description' => t('Campos permitidos del usuario que se han de enviar al servicio de microservicio durante el registro'),
      '#options' => $fields_options,
      '#default_value' => (array) json_decode($config->get('campos_disponibles_usuario_autologin')),
      '#required' => TRUE 
    ];
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
        'certificate_url'
      ];

      //set values
      foreach ($keys as $key) {
        $this->config('tienda_autologin.configuration')->set($key, $form_state->getValue($key))->save();
      }
      $this->config('tienda_autologin.configuration')->set('campos_disponibles_usuario_autologin', json_encode($form_state->getValue('campos_disponibles_usuario_autologin')))->save();
    }
  }
}
