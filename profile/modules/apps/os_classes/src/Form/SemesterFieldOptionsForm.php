<?php

namespace Drupal\os_classes\Form;

use Drupal\Component\Transliteration\PhpTransliteration;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\OptGroup;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements a semester field options form.
 */
class SemesterFieldOptionsForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  private $languageManager;

  /**
   * Class constructor.
   */
  public function __construct(EntityTypeManager $entityTypeManager, LanguageManagerInterface $languageManager, ConfigFactoryInterface $configFactory) {
    $this->entityTypeManager = $entityTypeManager;
    $this->languageManager = $languageManager;
    $this->configFactory = $configFactory;
  }

  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
    // Load the service required to construct this class.
      $container->get('entity_type.manager'),
      $container->get('language_manager'),
      $container->get('config.factory')
    );
  }

  /**
   * Create machine key.
   *
   * @param string $text
   *   Original text.
   * @param string $langcode
   *   Langcode.
   *
   * @return string|string[]|null
   *   Transliterated and replaced string.
   */
  protected static function createMachineKey($text, string $langcode) {
    $transliteration = new PhpTransliteration();
    $transliterated = $transliteration->transliterate($text, $langcode, '_');
    $transliterated = mb_strtolower($transliterated);
    $key = preg_replace('@[^a-z0-9_]+@', '_', $transliterated);
    return $key;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'semester_field_options_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $description = '<p>' . t('The possible values this field can contain. Enter one value per line, in the format key|label.');
    $description .= '<br/>' . t('The key is the stored value. The label will be used in displayed values and edit forms.');
    $description .= '<br/>' . t('The label is optional: if a line contains a single string, it will be used as key and label.');
    $description .= '</p>';
    $allowedValues = $this->config('os_classes.settings')->get('field_semester_allowed_values');
    $flattenOptions = OptGroup::flattenOptions($allowedValues);
    $field = $this->entityTypeManager->getStorage('field_storage_config')->load('node.field_semester');
    if ($field->hasData()) {
      \Drupal::messenger()->addWarning(t('There are already contents in the class content type!'));
    }
    $form['semester_field_options'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Semester field options'),
      '#required' => TRUE,
      '#description' => $description,
      '#default_value' => !empty($flattenOptions) ? $this->allowedValuesString($flattenOptions) : '',
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
    $values = $this->extractAllowedValues($form_state->getValue('semester_field_options'));

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
  protected function extractAllowedValues($string) {
    $langCode = $this->languageManager->getCurrentLanguage()->getId();
    $values = [];

    $list = explode("\n", $string);
    $list = array_map('trim', $list);
    $list = array_filter($list, 'strlen');

    $generatedKeys = $explicitKeys = FALSE;
    foreach ($list as $position => $text) {
      // Check for an explicit key.
      $matches = [];
      if (preg_match('/(.*)\|(.*)/', $text, $matches)) {
        // Trim key and value to avoid unwanted spaces issues.
        $key = trim($matches[1]);
        $value = trim($matches[2]);
        $explicitKeys = TRUE;
      }
      // Otherwise see if we can use the value as the key.
      elseif (!static::validateAllowedValue($text)) {
        $key = self::createMachineKey($text, $langCode);
        $value = $text;
        $explicitKeys = TRUE;
      }
      else {
        return;
      }

      $values[$key] = $value;
    }

    // We generate keys only if the list contains no explicit key at all.
    if ($explicitKeys && $generatedKeys) {
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
  protected static function validateAllowedValue($option) : string {
    if (mb_strlen($option) > 255) {
      return t('Allowed values list: each key must be a string at most 255 characters long.');
    }
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $options = $form_state->getValue('semester_field_options');
    $this->configFactory->getEditable('os_classes.settings')
      ->set('field_semester_allowed_values', $options)
      ->save();
    \Drupal::messenger()->addMessage('Values are updated');
  }

  /**
   * Generates a string representation of an array of 'allowed values'.
   *
   * This string format is suitable for edition in a textarea.
   *
   * @param array $values
   *   An array of values, where array keys are values and array values are
   *   labels.
   *
   * @return string
   *   The string representation of the $values array:
   *    - Values are separated by a carriage return.
   *    - Each value is in the format "value|label" or "value".
   */
  public function allowedValuesString(array $values) {
    $lines = [];
    foreach ($values as $key => $value) {
      $lines[] = "$key|$value";
    }
    return implode("\n", $lines);
  }

}
