<?php

namespace Drupal\publicate\Form;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;

/**
 * PublicatePushSettings class extending FormBase.
 */
class PublicatePushSettings extends FormBase {

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'publicate_settings';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $endpoints = $this->getCurrentEndpoints();
    $total_existing_endpoints = count($endpoints);

      $form['remote_site']['endpoint'] = array(
        '#type' => 'fieldset',
        '#open' => TRUE,
        '#title' => t('Publicate Endpoints'),
        '#collapsed' => FALSE,
        '#collapsible' => FALSE,
        '#description' => t('URL\'s and credentials for sites to send data to.'),
        '#prefix' => '<div id="enpoints-wrapper">',
        '#suffix' => '</div>',
      );

      $max = $form_state->get('fields_count');
      if (is_null($max)) {
        $max = ($total_existing_endpoints > 0) ? $total_existing_endpoints - 1 : 0;
        $form_state->set('fields_count', $max);
      }

      // Add elements that don't already exist
      for ($delta = 0; $delta <= $max; $delta++) {

        if (!isset($form['remote_site']['endpoint'][$delta])) {

          $element = array(
            '#type' => 'textfield',
            '#title' => t('Name'),
            '#default_value' => $endpoints[$delta]['name'],
          );
          $form['remote_site']['endpoint'][$delta]['name_' . $delta] = $element;

          $element = array(
            '#type' => 'textfield',
            '#title' => t('URL'),
            '#default_value' => $endpoints[$delta]['url'],
          );
          $form['remote_site']['endpoint'][$delta]['url_' . $delta] = $element;

          $element = array(
            '#type' => 'textfield',
            '#title' => t('Username'),
            '#required' => FALSE,
            '#default_value' => $endpoints[$delta]['username'],
          );

          $form['remote_site']['endpoint'][$delta]['username_' . $delta] = $element;
          $element = array(
            '#type' => 'textfield',
            '#title' => t('Password'),
            '#default_value' => $endpoints[$delta]['password'],
            '#required' => FALSE,
            '#suffix' => '<hr />'
          );

          $form['remote_site']['endpoint'][$delta]['password_' . $delta] = $element;
        }
      }


      $form['remote_site']['endpoint']['add'] = array(
        '#type' => 'submit',
        '#name' => 'addfield',
        '#value' => t('Add more'),
        '#submit' => array(array($this, 'addfieldsubmit')),
        '#ajax' => array(
          'callback' => array($this, 'addfieldCallback'),
          'wrapper' => 'enpoints-wrapper',
          'effect' => 'fade',
        ),
      );

      $form['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Save Endpoints'),
      );


    return $form;
  }

  /**
   * Ajax submit to add new field.
   */
  public function addfieldsubmit(array &$form, FormStateInterface &$form_state) {
    $max = $form_state->get('fields_count') + 1;
    $form_state->set('fields_count',$max);
    $form_state->setRebuild(TRUE);
  }

  /**
   * Ajax callback to add new field.
   */
  public function addfieldCallback(array &$form, FormStateInterface &$form_state) {
    return $form['remote_site']['endpoint'];
  }

  /**
   * Form validation handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    /*
    if (!UrlHelper::isValid($form_state->getValues()['url'], TRUE)) {
    //  $form_state->setErrorByName('remote_site_url', t('Please enter a valid URL.'));
    }

    if ($form_state->getValues()['remote_site_url_password'] == '' && \Drupal::state()->get('remote_site_url_password') == '') {
    //    $form_state->setErrorByName('remote_site_url_password', t('Remote site credentials (username and password) are required.'));
    }

    return $form;
*/
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $total_values = $form_state->get('fields_count');
    for($i=0; $i<=$total_values; $i++) {
      db_merge('publicate')
        ->key(array('url' => $form_state->getValue('url_' . $i)))
        ->fields(array(
          'name' => $form_state->getValue('name_' . $i),
          'url' => $form_state->getValue('url_' . $i),
          'username' => $form_state->getValue('username_' . $i),
          'password' => $form_state->getValue('password_' . $i),
        ))
        ->execute();
    }

  }

  public function getCurrentEndpoints() {

    $result = db_select('publicate', 'p')
      ->fields('p', array('pid', 'name', 'url', 'username', 'password'))
      ->execute();

    foreach ($result as $record) {
      $endpoints[] = array(
        'pid' => $record->pid,
        'name' => $record->name,
        'url' => $record->url,
        'username' => $record->username,
        'password' => $record->password,
      );
    }
    if(isset($endpoints)) {
      return $endpoints;
    } else {
      return;
    }
  }

}
