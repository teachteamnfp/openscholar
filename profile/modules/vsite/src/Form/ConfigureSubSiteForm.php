<?php

namespace Drupal\vsite\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements a configuration form.
 */
class ConfigureSubSiteForm extends FormBase {

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
    $groupBundles = \Drupal::service('entity_type.bundle.info')->getBundleInfo('group');

    $allowedParentSiteOptions = [];
    $defaultAllowedValues = [];
    foreach ($groupBundles as $bundleName => $bundle) {
      $allowedParentSiteOptions[$bundleName] = $bundle['label'];

      $fieldConfig = \Drupal::config('field.field.group.' . $bundleName . '.field_parent_site')->get('settings');
      if (!empty($fieldConfig['handler_settings']['target_bundles'])) {
        foreach ($fieldConfig['handler_settings']['target_bundles'] as $target_bundle) {
          $defaultAllowedValues[$target_bundle] = $target_bundle;
        }
      }
    }

    $form['allowed_parent_sites'] = [
      '#title' => $this->t('Allowed parent sites group bundles'),
      '#type' => 'checkboxes',
      '#options' => $allowedParentSiteOptions,
      '#default_value' => $defaultAllowedValues,
      '#required' => TRUE,
    ];

    $vsiteConfig = \Drupal::config('vsite.settings')->get('allowed_subsite_group_types');

    $form['allowed_sub_sites'] = [
      '#title' => $this->t('Allowed sub sites group bundles'),
      '#type' => 'checkboxes',
      '#options' => $allowedParentSiteOptions,
      '#default_value' => !empty($vsiteConfig) ? $vsiteConfig : [],
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
    $allowedParentValues = $form_state->getValue('allowed_parent_sites');
    $allowedParentValues = $this->normalizeValues($allowedParentValues);
    $allowedSubSiteValues = $form_state->getValue('allowed_sub_sites');
    $allowedSubSiteValues = $this->normalizeValues($allowedSubSiteValues);
    $intersect = array_intersect($allowedParentValues, $allowedSubSiteValues);
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
    $allowedParentValues = $form_state->getValue('allowed_parent_sites');
    $allowedParentValues = $this->normalizeValues($allowedParentValues);

    $groupBundles = \Drupal::service('entity_type.bundle.info')->getBundleInfo('group');

    // Update field parent site field instance config
    // in all group entity bundle.
    foreach ($groupBundles as $bundleName => $bundle) {
      $fieldConfig = \Drupal::config('field.field.group.' . $bundleName . '.field_parent_site')->get('settings');
      $fieldConfig['handler_settings']['target_bundles'] = $allowedParentValues;
      \Drupal::configFactory()->getEditable('field.field.group.' . $bundleName . '.field_parent_site')
        ->set('settings', $fieldConfig)
        ->save();
    }

    $allowedSubSiteValues = $form_state->getValue('allowed_sub_sites');
    $allowedSubSiteValues = $this->normalizeValues($allowedSubSiteValues);
    \Drupal::configFactory()->getEditable('vsite.settings')
      ->set('allowed_subsite_group_types', $allowedSubSiteValues)
      ->save();

    \Drupal::messenger()->addMessage(t('Allowed values settings saved successful.'));
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
    if (empty($values)) {
      return $values;
    }
    foreach ($values as $key => $value) {
      if ($value == '0') {
        unset($values[$key]);
      }
    }
    return $values;
  }

}
