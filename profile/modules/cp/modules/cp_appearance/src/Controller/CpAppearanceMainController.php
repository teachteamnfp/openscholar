<?php

namespace Drupal\cp_appearance\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Url;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for the cp_users page.
 *
 * Also invokes the modals.
 */
class CpAppearanceMainController extends ControllerBase {

  /**
   * Vsite Context Manager.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The form builder service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The theme handler service.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('vsite.context_manager'),
      $container->get('entity_type.manager'),
      $container->get('form_builder'),
      $container->get('theme_handler'),
      $container->get('config.factory')
    );
  }

  /**
   * CpUserMainController constructor.
   *
   * @param \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsiteContextManager
   *   Vsite Context Manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Type Manager.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(VsiteContextManagerInterface $vsiteContextManager, EntityTypeManagerInterface $entityTypeManager, FormBuilderInterface $form_builder, ThemeHandlerInterface $theme_handler, ConfigFactoryInterface $config_factory) {
    $this->vsiteContextManager = $vsiteContextManager;
    $this->entityTypeManager = $entityTypeManager;
    $this->formBuilder = $form_builder;
    $this->themeHandler = $theme_handler;
    $this->configFactory = $config_factory;

  }

  /**
   * Entry point for cp/users.
   */
  public function main() {
    $group = $this->vsiteContextManager->getActiveVsite();
    if (!$group) {
      throw new AccessDeniedHttpException();
    }

    $config = $this->config('system.theme');
    // Get all available themes.
    $themes = $this->themeHandler->rebuildThemeData();
    uasort($themes, 'system_sort_modules_by_info_name');

    $theme_default = $config->get('default');
    $theme_groups = ['featured' => [], 'basic' => []];

    // Use for simple dropdown for now.
    $basic_theme_options = [];

    foreach ($themes as &$theme) {
      if (!empty($theme->info['hidden']) || empty($theme->status) || empty($theme->info['base theme'])) {
        continue;
      }

      // Only show themes derived from os_base for now,
      // we should add a custom param in the info.
      if ($theme->info['base theme'] != 'os_base') {
        continue;
      }

      $theme->is_default = ($theme->getName() == $theme_default);

      // Identify theme screenshot.
      $theme->screenshot = NULL;
      // Create a list which includes the current theme and all its base themes.
      if (isset($themes[$theme->getName()]->base_themes)) {
        $theme_keys = array_keys($themes[$theme->getName()]->base_themes);
        $theme_keys[] = $theme->getName();
      }
      else {
        $theme_keys = [$theme->getName()];
      }
      // Look for a screenshot in the current theme or in its closest ancestor.
      foreach (array_reverse($theme_keys) as $theme_key) {
        if (isset($themes[$theme_key]) && file_exists($themes[$theme_key]->info['screenshot'])) {
          $theme->screenshot = [
            'uri' => $themes[$theme_key]->info['screenshot'],
            'alt' => $this->t('Screenshot for @theme theme', ['@theme' => $theme->info['name']]),
            'title' => $this->t('Screenshot for @theme theme', ['@theme' => $theme->info['name']]),
            'attributes' => ['class' => ['screenshot']],
          ];
          break;
        }
      }

      $theme->operations = [];
      if (!empty($theme->status)) {
        // Create the operations links.
        $query['theme'] = $theme->getName();

        if (!$theme->is_default) {
          $theme->operations[] = [
            'title' => $this->t('Set as theme.'),
            'url' => Url::fromRoute('cp_appearance.cp_select_theme'),
            'query' => $query,
            'attributes' => ['title' => $this->t('Set @theme as your theme', ['@theme' => $theme->info['name']])],
          ];
        }
        $basic_theme_options[$theme->getName()] = $theme->info['name'];
      }

      // Add notes to default and administration theme.
      $theme->notes = [];
      if ($theme->is_default) {
        $theme->notes[] = $this->t('current theme');
      }

      // Sort installed and uninstalled themes into their own groups.
      $theme_groups['featured'][] = $theme;
    }

    // There are two possible theme groups.
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

    $build[] = $this->formBuilder->getForm('Drupal\cp_appearance\Form\ThemeForm', $basic_theme_options);

    return $build;
  }

  /**
   * Set the theme.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   A request object containing a theme name.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirects back to the appearance admin page.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   *   Throws access denied when no theme is set in the request.
   */
  public function setTheme(Request $request) {
    $config = $this->configFactory->getEditable('system.theme');
    $theme = $request->query->get('theme');

    if (isset($theme)) {
      // Get current list of themes.
      $themes = $this->themeHandler->listInfo();

      // Check if the specified theme is one recognized by the system.
      // Or try to install the theme.
      if (isset($themes[$theme])) {

        // Set the default theme.
        $config->set('default', $theme)->save();

        $this->messenger()->addStatus($this->t('%theme is now your theme.', ['%theme' => $themes[$theme]->info['name']]));
      }
      else {
        $this->messenger()->addError($this->t('The %theme theme was not found.', ['%theme' => $theme]));
      }

      return $this->redirect('cp.appearance');

    }
    throw new AccessDeniedHttpException();
  }

}
