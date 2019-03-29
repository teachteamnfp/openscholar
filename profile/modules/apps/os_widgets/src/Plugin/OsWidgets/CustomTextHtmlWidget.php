<?php

namespace Drupal\os_widgets\Plugin\OsWidgets;

use Drupal\Component\Utility\Html;
use Drupal\os_widgets\OsWidgetsBase;
use Drupal\os_widgets\OsWidgetsInterface;

/**
 * Class CustomTextHtmlWidget.
 *
 * @OsWidget(
 *   id = "custom_text_html_widget",
 *   title = @Translation("Custom Text/HTML")
 * )
 */
class CustomTextHtmlWidget extends OsWidgetsBase implements OsWidgetsInterface {

  /**
   * {@inheritdoc}
   */
  public function buildBlock(&$build, $block_content) {
    if (empty($block_content)) {
      return;
    }
    $body_values = $block_content->get('body')->getValue();
    // Force to filtered_html.
    $body_values[0]['format'] = 'filtered_html';
    $block_content->set('body', $body_values);
    $build['custom_text_html']['body'] = $block_content->get('body')->view([
      'label' => 'hidden',
    ]);

    $field_css_classes_values = $block_content->get('field_css_classes')->getValue();
    if (!empty($field_css_classes_values[0]['value'])) {
      $build['#extra_classes'] = $this->parseCssClasses($field_css_classes_values[0]['value']);
    }
  }

  /**
   * Convert css classes user input string to array.
   *
   * @param string $css_string
   *   Classes in string.
   *
   * @return array
   *   Classes in trimmed array.
   */
  public function parseCssClasses($css_string) {
    $classes = [];
    $user_classes = explode(' ', $css_string);
    foreach ($user_classes as $user_class) {
      $classes[] = Html::cleanCssIdentifier($user_class);
    }
    return $classes;
  }

}
