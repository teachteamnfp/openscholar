<?php

namespace Drupal\cp_appearance;

use Drupal\Core\Asset\AssetCollectionOptimizerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ConfigInstallerInterface;
use Drupal\Core\Extension\Exception\UnknownExtensionException;
use Drupal\Core\Extension\ExtensionNameLengthException;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\State\StateInterface;
use Psr\Log\LoggerInterface;

/**
 * Manages custom theme installation/uninstallation.
 */
class CustomThemeInstaller implements CustomThemeInstallerInterface {

  /**
   * Theme handler service.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Config installer service.
   *
   * @var \Drupal\Core\Config\ConfigInstallerInterface
   */
  protected $configInstaller;

  /**
   * Module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The state store.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * The CSS asset collection optimizer service.
   *
   * @var \Drupal\Core\Asset\AssetCollectionOptimizerInterface
   */
  protected $cssCollectionOptimizer;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a new ThemeInstaller.
   *
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory to get the installed themes.
   * @param \Drupal\Core\Config\ConfigInstallerInterface $config_installer
   *   The config installer to install configuration.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to fire themes_installed/themes_uninstalled hooks.
   * @param \Drupal\Core\Asset\AssetCollectionOptimizerInterface $css_collection_optimizer
   *   The CSS asset collection optimizer service.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state store.
   */
  public function __construct(ThemeHandlerInterface $theme_handler, ConfigFactoryInterface $config_factory, ConfigInstallerInterface $config_installer, ModuleHandlerInterface $module_handler, AssetCollectionOptimizerInterface $css_collection_optimizer, LoggerInterface $logger, StateInterface $state) {
    $this->themeHandler = $theme_handler;
    $this->configFactory = $config_factory;
    $this->configInstaller = $config_installer;
    $this->moduleHandler = $module_handler;
    $this->cssCollectionOptimizer = $css_collection_optimizer;
    $this->logger = $logger;
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public function install(array $theme_list, $install_dependencies = TRUE): bool {
    $extension_config = $this->configFactory->getEditable('core.extension');

    $theme_data = $this->themeHandler->rebuildThemeData();

    if ($install_dependencies) {
      $theme_list = array_combine($theme_list, $theme_list);
      $missing = array_diff_key($theme_list, $theme_data);

      if ($missing) {
        // One or more of the given themes doesn't exist.
        throw new UnknownExtensionException('Unknown themes: ' . implode(', ', $missing) . '.');
      }

      // Only process themes that are not installed currently.
      $installed_themes = $extension_config->get('theme') ?: [];
      if (!$theme_list = array_diff_key($theme_list, $installed_themes)) {
        // Nothing to do. All themes already installed.
        return TRUE;
      }

      foreach ($theme_list as $theme => $value) {
        // Add dependencies to the list. The new themes will be processed as
        // the parent foreach loop continues.
        foreach (array_keys($theme_data[$theme]->requires) as $dependency) {
          if (!isset($theme_data[$dependency])) {
            // The dependency does not exist.
            return FALSE;
          }

          // Skip already installed themes.
          if (!isset($theme_list[$dependency]) && !isset($installed_themes[$dependency])) {
            $theme_list[$dependency] = $dependency;
          }
        }
      }

      // Set the actual theme weights.
      $theme_list = array_map(function ($theme) use ($theme_data) {
        return $theme_data[$theme]->sort;
      }, $theme_list);

      // Sort the theme list by their weights (reverse).
      arsort($theme_list);
      $theme_list = array_keys($theme_list);
    }
    else {
      $installed_themes = $extension_config->get('theme') ?: [];
    }

    $themes_installed = [];
    foreach ($theme_list as $key) {
      // Only process themes that are not already installed.
      $installed = $extension_config->get("theme.$key") !== NULL;
      if ($installed) {
        continue;
      }

      // Throw an exception if the theme name is too long.
      if (\strlen($key) > DRUPAL_EXTENSION_NAME_MAX_LENGTH) {
        throw new ExtensionNameLengthException("Theme name $key is over the maximum allowed length of " . DRUPAL_EXTENSION_NAME_MAX_LENGTH . ' characters.');
      }

      // Validate default configuration of the theme. If there is existing
      // configuration then stop installing.
      $this->configInstaller->checkConfigurationToInstall('theme', $key);

      // The value is not used; the weight is ignored for themes currently. Do
      // not check schema when saving the configuration.
      $extension_config
        ->set("theme.$key", 0)
        ->save(TRUE);

      // Add the theme to the current list.
      $theme_data[$key]->status = 1;
      $this->themeHandler->addTheme($theme_data[$key]);

      // Update the current theme data accordingly.
      $current_theme_data = $this->state->get('system.theme.data', []);
      $current_theme_data[$key] = $theme_data[$key];
      $this->state->set('system.theme.data', $current_theme_data);

      // Reset theme settings.
      $theme_settings = &drupal_static('theme_get_setting');
      unset($theme_settings[$key]);

      // Only install default configuration if this theme has not been installed
      // already.
      if (!isset($installed_themes[$key])) {
        // Install default configuration of the theme.
        $this->configInstaller->installDefaultConfig('theme', $key);
      }

      $themes_installed[] = $key;

      // Record the fact that it was installed.
      $this->logger->info('%theme theme installed.', ['%theme' => $key]);
    }

    $this->cssCollectionOptimizer->deleteAll();

    // Invoke hook_themes_installed() after the themes have been installed.
    $this->moduleHandler->invokeAll('themes_installed', [$themes_installed]);

    return !empty($themes_installed);
  }

}
