<?php
  namespace Drupal\tienda_autologin\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\Role;

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

    $form['certificate_url'] = array(
      '#type' => 'radios',
      '#title' => t('Verificar el certificado del HOST'),
      '#options' => [true => t("SI"), false => t('NO')],
      '#default_value' => $config->get('certificate_url')
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
        'certificate_url'
      ];

      //set values
      foreach ($keys as $key) {
        $this->config('tienda_autologin.configuration')->set($key, $form_state->getValue($key))->save();
      }
    }
  }
}
