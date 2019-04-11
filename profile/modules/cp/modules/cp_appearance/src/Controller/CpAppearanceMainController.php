<?php

namespace Drupal\cp_appearance\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\cp_appearance\AppearanceHelperInterface;
use Drupal\cp_appearance\Form\ThemeForm;
use Drupal\os_theme_preview\HandlerInterface;
use Drupal\os_theme_preview\PreviewManagerInterface;
use Drupal\os_theme_preview\ThemePreviewException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for the cp_users page.
 *
 * Also invokes the modals.
 */
class CpAppearanceMainController extends ControllerBase {

  /**
   * The theme handler service.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * Theme appearance helper.
   *
   * @var \Drupal\cp_appearance\AppearanceHelperInterface
   */
  protected $appearanceHelper;

  /**
   * Theme preview handler.
   *
   * @var \Drupal\os_theme_preview\HandlerInterface
   */
  protected $previewHandler;

  /**
   * Theme preview manager.
   *
   * @var \Drupal\os_theme_preview\PreviewManagerInterface
   */
  protected $previewManager;

  /**
   * Alias manager.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('theme_handler'),
      $container->get('config.factory'),
      $container->get('cp_appearance.appearance_helper'),
      $container->get('os_theme_preview.handler'),
      $container->get('os_theme_preview.manager'),
      $container->get('path.alias_manager')
    );
  }

  /**
   * Creates a new CpAppearanceMainController object.
   *
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\cp_appearance\AppearanceHelperInterface $appearance_helper
   *   Theme appearance helper service.
   * @param \Drupal\os_theme_preview\HandlerInterface $handler
   *   Theme preview handler.
   * @param \Drupal\os_theme_preview\PreviewManagerInterface $preview_manager
   *   Theme preview manager.
   * @param \Drupal\Core\Path\AliasManagerInterface $alias_manager
   *   Alias manager.
   */
  public function __construct(ThemeHandlerInterface $theme_handler, ConfigFactoryInterface $config_factory, AppearanceHelperInterface $appearance_helper, HandlerInterface $handler, PreviewManagerInterface $preview_manager, AliasManagerInterface $alias_manager) {
    $this->themeHandler = $theme_handler;
    $this->configFactory = $config_factory;
    $this->appearanceHelper = $appearance_helper;
    $this->previewHandler = $handler;
    $this->previewManager = $preview_manager;
    $this->aliasManager = $alias_manager;
  }

  /**
   * Entry point for cp/users.
   */
  public function main(): array {
    /** @var \Drupal\Core\Extension\Extension[] $themes */
    $themes = $this->appearanceHelper->getThemes();

    // Use for simple dropdown for now.
    $basic_theme_options = [];
    foreach ($themes as $theme) {
      $basic_theme_options[$theme->getName()] = $theme->info['name'];
    }

    // There are two possible theme groups.
    $theme_groups = ['featured' => $themes, 'basic' => []];
    $theme_group_titles = [
      'featured' => $this->formatPlural(count($theme_groups['featured']), 'Featured theme', 'Featured themes'),
    ];

    uasort($theme_groups['featured'], 'system_sort_themes');
    $this->moduleHandler()->alter('cp_appearance_themes_page', $theme_groups);

    $build = [];
    $build[] = [
      '#theme' => 'system_themes_page',
      '#theme_groups' => $theme_groups,
      '#theme_group_titles' => $theme_group_titles,
    ];

    $build[] = $this->formBuilder()->getForm(ThemeForm::class, $basic_theme_options);

    return $build;
  }

  /**
   * Set a theme as default.
   *
   * @param string $theme
   *   The theme name.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect response.
   */
  public function setTheme($theme, Request $request): RedirectResponse {
    $config = $this->configFactory->getEditable('system.theme');
    $themes = $this->themeHandler->listInfo();

    // Check if the specified theme is one recognized by the system.
    // Or try to install the theme.
    if (isset($themes[$theme])) {
      $config->set('default', $theme)->save();

      $this->messenger()->addStatus($this->t('%theme is now your theme.', ['%theme' => $themes[$theme]->info['name']]));
    }
    else {
      $this->messenger()->addError($this->t('The %theme theme was not found.', ['%theme' => $theme]));
    }

    return $this->redirectToAppearanceSettings();
  }

  /**
   * Starts preview mode for a theme.
   *
   * @param string $theme
   *   The theme name.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect response.
   */
  public function previewTheme($theme, Request $request): RedirectResponse {
    try {
      $this->previewHandler->startPreviewMode($theme, $this->previewManager->getActiveVsiteId());
    }
    catch (ThemePreviewException $e) {
      $this->messenger()->addError($this->t('Could not start preview. Please check logs for details.'));
      $this->getLogger('cp_appearance')->error($e->getMessage());
    }

    return $this->redirectToAppearanceSettings();
  }

  /**
   * Redirect to vsite appearance settings page.
   *
   * This makes sure that the redirect is to `/vsite-alias/cp/appearance`,
   * otherwise, the system loses the vsite alias and redirects to
   * `/cp/appearance`.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect response.
   */
  protected function redirectToAppearanceSettings(): RedirectResponse {
    /** @var int $group_id */
    $group_id = $this->previewManager->getActiveVsiteId();
    /** @var string $vsite_alias */
    $vsite_alias = $this->aliasManager->getAliasByPath("/group/{$group_id}");
    return new RedirectResponse("$vsite_alias/cp/appearance");
  }

}
