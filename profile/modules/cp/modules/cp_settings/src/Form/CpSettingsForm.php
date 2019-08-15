<?php

namespace Drupal\cp_settings\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\cp_settings\Plugin\CpSettingsManagerInterface;
use Drupal\file\Entity\File;
use Drupal\file\FileInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CpSettingsForm.
 */
class CpSettingsForm extends ConfigFormBase {

  /**
   * CP settings manager.
   *
   * @var \Drupal\cp_settings\Plugin\CpSettingsManagerInterface
   */
  protected $cpSettingsManager;

  /**
   * Plugins.
   *
   * @var \Drupal\cp_settings\Annotation\CpSetting[]
   */
  protected $plugins = [];

  /**
   * Created file entity.
   *
   * @var \Drupal\file\Entity\File|null
   */
  protected $file = NULL;

  /**
   * Machine name of current setting group.
   *
   * @var string
   */
  protected $settingGroup = '';

  /**
   * Creates new CpSettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\cp_settings\Plugin\CpSettingsManagerInterface $cpSettingsManager
   *   CP settings manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, CpSettingsManagerInterface $cpSettingsManager) {
    parent::__construct($config_factory);
    $this->cpSettingsManager = $cpSettingsManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('cp_settings.manager')
    );
  }

  /**
   * Returns plugins.
   */
  protected function getPlugins() {
    $group = $this->getSettingGroup();

    if (empty($this->plugins) && $group) {
      $this->plugins = $this->cpSettingsManager->getPluginsForGroup($group);
    }

    return $this->plugins;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cp_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    $plugins = $this->getPlugins();
    $config = [];
    /** @var \Drupal\cp_settings\CpSettingInterface $p */
    foreach ($plugins as $p) {
      $config = array_merge($p->getEditableConfigNames());
    }
    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    /** @var \Drupal\cp_settings\CpSettingInterface[] $plugins */
    $plugins = $this->getPlugins();
    foreach ($plugins as $p) {
      $p->getForm($form, $this->configFactory);
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    /** @var \Drupal\cp_settings\CpSettingInterface[] $plugins */
    $plugins = $this->getPlugins();
    foreach ($plugins as $p) {
      $p->validateForm($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\cp_settings\CpSettingInterface[] $plugins */
    $plugins = $this->getPlugins();
    foreach ($plugins as $p) {
      $p->submitForm($form_state, $this->configFactory);
    }

    parent::submitForm($form, $form_state);
  }

  /**
   * Set the page title to the title of the settings group.
   */
  public function formTitle() {
    $group = $this->getSettingGroup();
    return $this->cpSettingsManager->getTitleForGroup($group);
  }

  /**
   * Helper to expose file entity element.
   *
   * This method is mendatory to works with "buildCropToForm",
   * for unicity with File entity compatibility.
   *
   * @return \Drupal\file\Entity\File|null
   *   File saved by file_manager element.
   *
   * @see \Drupal\image_widget_crop\ImageWidgetCropManager::buildCropToForm
   */
  public function getEntity() {
    return $this->file;
  }

  /**
   * Helper to set file entity element.
   *
   * @param \Drupal\file\FileInterface $file
   *   File entity.
   */
  public function setEntity(FileInterface $file) {
    $this->file = $file;
  }

  /**
   * Getter for setting_group.
   *
   * @return mixed|string
   *   Machine name of setting group.
   */
  public function getSettingGroup() {
    if (empty($this->settingGroup)) {
      $this->settingGroup = $this->getRequest()->get('setting_group');
    }
    return $this->settingGroup;
  }

  /**
   * Setter for setting_group.
   *
   * @param string $setting_group
   *   Machine name of setting group.
   */
  public function setSettingGroup(string $setting_group) {
    $this->settingGroup = $setting_group;
  }

}
