<?php

namespace Drupal\openscholar\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements a configuration form.
 */
class ConfigurationForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'configuration_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#title'] = 'OpenScholar configuration';

    $description = '<p>' . t('The possible values this field can contain. Enter one value per line, in the format key|label.');
    $description .= '<br/>' . t('The key is the stored value. The label will be used in displayed values and edit forms.');
    $description .= '<br/>' . t('The label is optional: if a line contains a single string, it will be used as key and label.');
    $description .= '</p>';
    $form['semester_field_options'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Semester field options'),
      '#required' => TRUE,
      '#description' => $description,
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $element = $form['semester_field_options'];
    $values = static::extractAllowedValues($form_state->getValue('semester_field_options'));

    if (!is_array($values)) {
      $form_state->setError($element, t('Allowed values list: invalid input.'));
    }
    else {
      // Check that keys are valid for the field type.
      foreach ($values as $key => $value) {
        if ($error = static::validateAllowedValue($key)) {
          $form_state->setError($element, $error);
          break;
        }
      }

      $form_state->setValueForElement($element, $values);
    }
  }

  /**
   * Extracts the allowed values array from the allowed_values element.
   *
   * @param string $string
   *   The raw string to extract values from.
   *
   * @return array|null
   *   The array of extracted key/value pairs, or NULL if the string is invalid.
   *
   * @see \Drupal\options\Plugin\Field\FieldType\ListItemBase::allowedValuesString()
   */
  protected static function extractAllowedValues($string) {
    $values = [];

    $list = explode("\n", $string);
    $list = array_map('trim', $list);
    $list = array_filter($list, 'strlen');

    $generated_keys = $explicit_keys = FALSE;
    foreach ($list as $position => $text) {
      // Check for an explicit key.
      $matches = [];
      if (preg_match('/(.*)\|(.*)/', $text, $matches)) {
        // Trim key and value to avoid unwanted spaces issues.
        $key = trim($matches[1]);
        $value = trim($matches[2]);
        $explicit_keys = TRUE;
      }
      // Otherwise see if we can use the value as the key.
      elseif (!static::validateAllowedValue($text)) {
        $key = $value = $text;
        $explicit_keys = TRUE;
      }
      else {
        return;
      }

      $values[$key] = $value;
    }

    // We generate keys only if the list contains no explicit key at all.
    if ($explicit_keys && $generated_keys) {
      return;
    }

    return $values;
  }

  /**
   * Checks whether a candidate allowed value is valid.
   *
   * @param string $option
   *   The option value entered by the user.
   *
   * @return string
   *   The error message if the specified value is invalid, NULL otherwise.
   */
  protected static function validateAllowedValue($option) {
    if (mb_strlen($option) > 255) {
      return t('Allowed values list: each key must be a string at most 255 characters long.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $options = $form_state->getValue('semester_field_options');
    \Drupal::configFactory()->getEditable('os_classes.settings')
      ->set('field_semester_allowed_values', $options)
      ->save();
  }

}
