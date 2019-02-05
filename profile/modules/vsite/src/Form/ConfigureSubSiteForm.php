<?php

namespace Drupal\vsite\Form;

use Drupal\Core\Entity\EntityTypeBundleInfo;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\Messenger;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements a configuration form.
 */
class ConfigureSubSiteForm extends FormBase {

  /**
   * Entity type bundle info.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfo
   */
  private $entityTypeBundleInfo;

  /**
   * Drupal core messenger.
   *
   * @var \Drupal\Core\Messenger\Messenger
   */
  private $coreMessenger;

  /**
   * Class constructor.
   */
  public function __construct(EntityTypeBundleInfo $entity_type_bundle_info, Messenger $coreMessenger) {
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
    $this->coreMessenger = $coreMessenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
    // Load the service required to construct this class.
      $container->get('entity_type.bundle.info'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'configuration_sub_site_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $group_bundles = $this->entityTypeBundleInfo->getBundleInfo('group');

    $allowed_parent_site_options = [];
    $defaultAllowedValues = [];
    foreach ($group_bundles as $bundle_name => $bundle) {
      $allowed_parent_site_options[$bundle_name] = $bundle['label'];

      $field_config = $this->config('field.field.group.' . $bundle_name . '.field_parent_site')->get('settings');
      if (empty($field_config)) {
        $this->coreMessenger->addWarning(t('Group type %type is missing field_parent_site!', ['%type' => $bundle['label']]));
      }
      if (!empty($field_config['handler_settings']['target_bundles'])) {
        foreach ($field_config['handler_settings']['target_bundles'] as $target_bundle) {
          $defaultAllowedValues[$target_bundle] = $target_bundle;
        }
      }
    }

    $form['allowed_parent_sites'] = [
      '#title' => $this->t('Allowed parent sites group bundles'),
      '#type' => 'checkboxes',
      '#options' => $allowed_parent_site_options,
      '#default_value' => $defaultAllowedValues,
      '#required' => TRUE,
    ];

    $vsite_config = $this->config('vsite.settings')->get('allowed_subsite_group_types');

    $form['allowed_sub_sites'] = [
      '#title' => $this->t('Allowed sub sites group bundles'),
      '#type' => 'checkboxes',
      '#options' => $allowed_parent_site_options,
      '#default_value' => !empty($vsite_config) ? $vsite_config : [],
    ];

    $form['submit'] = [
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
    parent::validateForm($form, $form_state);
    $allowed_parent_values = $this->normalizeValues($form_state->getValue('allowed_parent_sites'));
    $allowed_subsite_values = $this->normalizeValues($form_state->getValue('allowed_sub_sites'));
    $intersect = array_intersect($allowed_parent_values, $allowed_subsite_values);
    if (!empty($intersect)) {
      $form_state->setError($form['allowed_parent_sites'], $this->t('Group types can not be both parent and sub site at the same time.'));
    }
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
    $allowed_parent_values = $this->normalizeValues($form_state->getValue('allowed_parent_sites'));

    $group_bundles = $this->entityTypeBundleInfo->getBundleInfo('group');

    // Update field parent site field instance config
    // in all group entity bundle.
    foreach ($group_bundles as $bundle_name => $bundle) {
      $field_config = $this->config('field.field.group.' . $bundle_name . '.field_parent_site')->get('settings');
      if (empty($field_config)) {
        continue;
      }
      $field_config['handler_settings']['target_bundles'] = $allowed_parent_values;
      $this->configFactory->getEditable('field.field.group.' . $bundle_name . '.field_parent_site')
        ->set('settings', $field_config)
        ->save();
    }

    $allowed_subsite_values = $this->normalizeValues($form_state->getValue('allowed_sub_sites'));
    $this->configFactory->getEditable('vsite.settings')
      ->set('allowed_subsite_group_types', $allowed_subsite_values)
      ->save();

    $this->coreMessenger->addMessage(t('Allowed values settings saved successful.'));
  }

  /**
   * Remove items with zero value.
   *
   * @param array $values
   *   Input values.
   *
   * @return array
   *   Normalized values.
   */
  private function normalizeValues(array $values) {
    foreach ($values as $key => $value) {
      if ($value == '0') {
        unset($values[$key]);
      }
    }
    return $values;
  }

}
