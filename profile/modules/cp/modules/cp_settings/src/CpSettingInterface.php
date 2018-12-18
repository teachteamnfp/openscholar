<?php

namespace Drupal\cp_settings;

use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

interface CpSettingInterface {

  public function getEditableConfigNames() : array;

  public function getForm(array &$form, ConfigFactoryInterface $configFactory);

  public function submitForm(FormStateInterface $formState, ConfigFactoryInterface $configFactory);

  public function access(AccountInterface $account) : AccessResultInterface;
}