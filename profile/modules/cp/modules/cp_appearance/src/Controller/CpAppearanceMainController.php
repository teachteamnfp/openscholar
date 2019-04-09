<?php

namespace Drupal\cp_appearance\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\cp_appearance\AppearanceHelperInterface;
use Drupal\cp_appearance\Form\ThemeForm;
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
   * Theme appearance helper.
   *
   * @var \Drupal\cp_appearance\AppearanceHelperInterface
   */
  protected $appearanceHelper;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('form_builder'),
      $container->get('theme_handler'),
      $container->get('config.factory'),
      $container->get('cp_appearance.appearance_helper')
    );
  }

  /**
   * CpAppearanceMainController constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Type Manager.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\cp_appearance\AppearanceHelperInterface $appearance_helper
   *   Theme appearance helper service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, FormBuilderInterface $form_builder, ThemeHandlerInterface $theme_handler, ConfigFactoryInterface $config_factory, AppearanceHelperInterface $appearance_helper) {
    $this->entityTypeManager = $entityTypeManager;
    $this->formBuilder = $form_builder;
    $this->themeHandler = $theme_handler;
    $this->configFactory = $config_factory;
    $this->appearanceHelper = $appearance_helper;
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

    $build = [
      '#theme' => 'system_themes_page',
      '#theme_groups' => $theme_groups,
      '#theme_group_titles' => $theme_group_titles,
    ];

    $build[] = $this->formBuilder->getForm(ThemeForm::class, $basic_theme_options);

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
