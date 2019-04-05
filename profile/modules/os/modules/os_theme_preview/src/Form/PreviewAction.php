<?php

namespace Drupal\os_theme_preview\Form;

use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\os_theme_preview\HelperInterface;
use Drupal\os_theme_preview\ThemePreviewException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Preview action form.
 */
class PreviewAction extends FormBase {

  /**
   * Theme preview helper.
   *
   * @var \Drupal\os_theme_preview\HelperInterface
   */
  protected $helper;

  /**
   * Data related to theme being previewed.
   *
   * @var \Drupal\os_theme_preview\ThemePreviewInterface
   */
  protected $previewedThemeData;

  /**
   * Theme handler service.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * {@inheritdoc}
   */
  protected $configFactory;

  /**
   * PreviewAction constructor.
   *
   * @param \Drupal\os_theme_preview\HelperInterface $helper
   *   Theme preview helper service.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   Theme handler service.
   */
  public function __construct(HelperInterface $helper, ThemeHandlerInterface $theme_handler) {
    $this->helper = $helper;
    $this->themeHandler = $theme_handler;
    $this->previewedThemeData = $this->helper->getPreviewedThemeData();
    $this->configFactory = $this->configFactory();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('os_theme_preview.helper'), $container->get('theme_handler'));
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'os_theme_preview_preview_action';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    /** @var string $human_name */
    $human_name = $this->themeHandler->getName($this->previewedThemeData->getName());

    $form['name'] = [
      '#type' => 'hidden',
      '#value' => $this->previewedThemeData->getName(),
    ];

    $form['preview'] = [
      '#markup' => '<div class="name">' . $this->t('<strong>Previewing:</strong> @theme_name', [
        '@theme_name' => $human_name,
      ]) . '</div>',
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['save'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    /** @var \Drupal\Core\Config\Config $theme_config */
    $theme_config = $this->configFactory->getEditable('system.theme');

    $theme_config
      ->set('default', $form_state->getValue('name'))
      ->save();

    try {
      $this->helper->stopPreviewMode();
    }
    catch (ThemePreviewException $exception) {
      $this->loggerFactory->get('os_theme_preview')->error($exception->getMessage());
      $this->messenger->addError($this->t('Preview could not be stopped. Check logs for more details.'));
    }
  }

}
