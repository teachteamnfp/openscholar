<?php

namespace Drupal\cp_appearance\Entity\Form;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Extension\ThemeInstallerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\cp_appearance\Entity\CustomTheme;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Custom theme installation form.
 */
final class InstallForm extends ConfirmFormBase implements ContainerInjectionInterface {

  /**
   * Theme installer service.
   *
   * @var \Drupal\Core\Extension\ThemeInstallerInterface
   */
  protected $themeInstaller;

  /**
   * Machine name of the custom theme.
   *
   * @var \Drupal\cp_appearance\Entity\CustomThemeInterface
   */
  protected $customTheme;

  /**
   * Marks if the theme should be made as default.
   *
   * @var bool
   */
  protected $makeDefault;

  /**
   * Theme handler service.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * Creates a new InstallForm object.
   *
   * @param \Drupal\Core\Extension\ThemeInstallerInterface $theme_installer
   *   Theme installer service.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   Theme handler service.
   */
  public function __construct(ThemeInstallerInterface $theme_installer, ThemeHandlerInterface $theme_handler) {
    $this->themeInstaller = $theme_installer;
    $this->themeHandler = $theme_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('theme_installer'), $container->get('theme_handler'));
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Proceed with %name installation.', [
      '%name' => $this->customTheme->label(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return Url::fromRoute('cp.appearance');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cp_appearance_install_custom_theme';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $custom_theme = NULL, $make_default = NULL) {
    $this->customTheme = CustomTheme::load($custom_theme);
    $this->makeDefault = (bool) $make_default;
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->themeInstaller->install([
      $this->customTheme->id(),
    ]);
    $this->themeHandler->refreshInfo();

    $this->messenger()->addMessage($this->t('Custom theme %name successfully installed.', [
      '%name' => $this->customTheme->label(),
    ]));

    if ($this->makeDefault) {
      /** @var \Drupal\Core\Config\Config $theme_setting_mut */
      $theme_setting_mut = $this->configFactory()->getEditable('system.theme');
      $theme_setting_mut->set('default', $this->customTheme->id())->save();

      $this->messenger()->addMessage($this->t('Custom theme %name is made as default.', [
        '%name' => $this->customTheme->label(),
      ]));
    }

    $form_state->setRedirect('cp.appearance');
  }

}
