<?php

/**
 * @file
 * Theme helpers.
 */

use Drupal\Component\Utility\Html;
use Drupal\Core\Template\Attribute;

/**
 * Prepares variables for appearance page templates.
 *
 * The default operations section only expects links. There can be other
 * operations which cannot be rendered as link.
 * \template_preprocess_system_themes_page does not lets you to alter the
 * default operations. Therefore, it is handled in a custom template.
 *
 * Default template: cp-appearance-themes-page.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - theme_groups: An associative array containing groups of themes.
 *   - theme_group_titles: An associative array containing titles of themes.
 *
 * @see \template_preprocess_system_themes_page
 */
function template_preprocess_cp_appearance_themes_page(array &$variables) {
  $groups = [];
  $theme_groups = $variables['theme_groups'];
  $variables['attributes']['id'] = 'system-themes-page';

  foreach ($variables['theme_group_titles'] as $state => $title) {
    if (!count($theme_groups[$state])) {
      // Skip this group of themes if no theme is there.
      continue;
    }
    // Start new theme group.
    $theme_group = [];
    $theme_group['state'] = $state;
    $theme_group['title'] = $title;
    $theme_group['themes'] = [];
    $theme_group['attributes'] = new Attribute();

    foreach ($theme_groups[$state] as $theme) {
      $current_theme = [];

      // Screenshot depicting the theme.
      if ($theme->screenshot) {
        $current_theme['screenshot'] = [
          '#theme' => 'image',
          '#uri' => $theme->screenshot['uri'],
          '#alt' => $theme->screenshot['alt'],
          '#title' => $theme->screenshot['title'],
          '#attributes' => $theme->screenshot['attributes'],
        ];
      }
      else {
        $current_theme['screenshot'] = [
          '#theme' => 'image',
          '#uri' => drupal_get_path('module', 'system') . '/images/no_screenshot.png',
          '#alt' => t('No screenshot'),
          '#title' => t('No screenshot'),
          '#attributes' => new Attribute(['class' => ['no-screenshot']]),
        ];
      }

      // Localize the theme description.
      $current_theme['description'] = $theme->info['description'];

      $current_theme['attributes'] = new Attribute([
        'id' => Html::cleanCssIdentifier("theme-selector-{$theme->getName()}"),
      ]);
      $current_theme['name'] = $theme->info['name'];
      $current_theme['version'] = $theme->info['version'] ?? '';
      $current_theme['notes'] = $theme->notes;
      $current_theme['is_default'] = $theme->is_default;
      $current_theme['is_admin'] = $theme->is_admin;

      // Make sure to provide feedback on compatibility.
      $current_theme['incompatible'] = '';
      if (!empty($theme->incompatible_core)) {
        $current_theme['incompatible'] = t("This theme is not compatible with Drupal @core_version. Check that the .info.yml file contains the correct 'core' value.", ['@core_version' => Drupal::CORE_COMPATIBILITY]);
      }
      elseif (!empty($theme->incompatible_region)) {
        $current_theme['incompatible'] = t("This theme is missing a 'content' region.");
      }
      elseif (!empty($theme->incompatible_php)) {
        if (substr_count($theme->info['php'], '.') < 2) {
          $theme->info['php'] .= '.*';
        }
        $current_theme['incompatible'] = t('This theme requires PHP version @php_required and is incompatible with PHP version @php_version.', ['@php_required' => $theme->info['php'], '@php_version' => PHP_VERSION]);
      }
      elseif (!empty($theme->incompatible_base)) {
        $current_theme['incompatible'] = t('This theme requires the base theme @base_theme to operate correctly.', ['@base_theme' => $theme->info['base theme']]);
      }
      elseif (!empty($theme->incompatible_engine)) {
        $current_theme['incompatible'] = t('This theme requires the theme engine @theme_engine to operate correctly.', ['@theme_engine' => $theme->info['engine']]);
      }

      // Build operation links.
      $current_theme['operations'] = [
        '#theme' => 'links',
        '#links' => $theme->operations,
        '#attributes' => [
          'class' => ['operations', 'clearfix'],
        ],
      ];
      $current_theme['more_operations'] = $theme->more_operations;
      $theme_group['themes'][] = $current_theme;
    }
    $groups[] = $theme_group;
  }
  $variables['theme_groups'] = $groups;
}
