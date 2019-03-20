<?php

namespace Drupal\os_widgets\Plugin\OsWidgets;

use Drupal\os_widgets\OsWidgetsBase;
use Drupal\os_widgets\OsWidgetsInterface;

/**
 * Class AddThisWidget.
 *
 * @OsWidget(
 *   id = "addthis_widget",
 *   title = @Translation("AddThis")
 * )
 */
class AddThisWidget extends OsWidgetsBase implements OsWidgetsInterface {

  /**
   * {@inheritdoc}
   */
  public function buildBlock(&$build, $block_content) {
    if (empty($block_content)) {
      return;
    }
    $field_addthis_display_style_values = $block_content->get('field_addthis_display_style')->getValue();
    $display_style = $field_addthis_display_style_values[0]['value'];
    switch ($display_style) {
      case 'buttons':
        $image_path = '/' . $this->getModulePath() . '/images/addthis/addthis_smallbar.png';
        $build['addthis'] = [
          '#theme' => 'os_widgets_addthis_buttons',
          '#image_path' => $image_path,
        ];
        break;

      case 'toolbox_small':
      case 'toolbox_large':
      case 'numeric':
      case 'counter':
        $build['addthis'] = [
          '#theme' => 'os_widgets_addthis_' . $display_style,
        ];
        break;
    }
    $build['addthis']['#attached']['library'][] = 'os_widgets/addthis';
  }

  /**
   * Get module path, able to create Mock.
   */
  public function getModulePath() {
    return drupal_get_path('module', 'os_widgets');
  }

}
