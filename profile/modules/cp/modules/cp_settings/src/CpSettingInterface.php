<?php

namespace Drupal\cp_settings;

use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * CP setting interface.
 */
interface CpSettingInterface {

  /**
   * Returns editable settings.
   *
   * @return array
   *   Setting names.
   */
  public function getEditableConfigNames() : array;

  /**
   * Returns the setting form.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory.
   */
  public function getForm(array &$form, ConfigFactoryInterface $configFactory);

  /**
   * Validates the form.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   */
  public function validateForm(array &$form, FormStateInterface $formState);

  /**
   * Submit form callback.
   *
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory.
   */
  public function submitForm(FormStateInterface $formState, ConfigFactoryInterface $configFactory);

  /**
   * Checks whether the settings are accessible to the user.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access.
   */
  public function access(AccountInterface $account) : AccessResultInterface;

}
