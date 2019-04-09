<?php

namespace Drupal\os_theme_preview\Form;

use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\os_theme_preview\HandlerInterface;
use Drupal\os_theme_preview\ThemePreviewException;
use Drupal\purl\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Preview action form.
 */
class PreviewAction extends FormBase {

  /**
   * Theme preview handler.
   *
   * @var \Drupal\os_theme_preview\HandlerInterface
   */
  protected $handler;

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
   * Alias manager.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * PreviewAction constructor.
   *
   * @param \Drupal\os_theme_preview\HandlerInterface $handler
   *   Theme preview handler.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   Theme handler service.
   * @param \Drupal\Core\Path\AliasManagerInterface $alias_manager
   *   Alias manager.
   */
  public function __construct(HandlerInterface $handler, ThemeHandlerInterface $theme_handler, AliasManagerInterface $alias_manager) {
    $this->handler = $handler;
    $this->themeHandler = $theme_handler;
    $this->aliasManager = $alias_manager;
    $this->previewedThemeData = $this->handler->getPreviewedThemeData();
    $this->configFactory = $this->configFactory();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('os_theme_preview.handler'), $container->get('theme_handler'), $container->get('path.alias_manager'));
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
      '#name' => 'save',
      '#value' => $this->t('Save'),
    ];

    $form['actions']['cancel'] = [
      '#type' => 'submit',
      '#name' => 'cancel',
      '#value' => $this->t('Cancel'),
      '#submit' => ['::cancelPreview'],
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
      $this->handler->stopPreviewMode();
    }
    catch (ThemePreviewException $exception) {
      $this->loggerFactory->get('os_theme_preview')->error($exception->getMessage());
      $this->messenger->addError($this->t('Preview could not be stopped. Check logs for more details.'));
    }
  }

  /**
   * Submit handler for exiting preview mode.
   *
   * @ingroup forms
   */
  public function cancelPreview(array &$form, FormStateInterface $form_state): void {
    try {
      $this->handler->stopPreviewMode();
    }
    catch (ThemePreviewException $exception) {
      $this->loggerFactory->get('os_theme_preview')->error($exception->getMessage());
      $this->messenger->addError($this->t('Preview could not be stopped. Check logs for more details.'));
    }

    $form_state->setRedirectUrl(Url::fromRoute('system.themes_page'));
  }

}
