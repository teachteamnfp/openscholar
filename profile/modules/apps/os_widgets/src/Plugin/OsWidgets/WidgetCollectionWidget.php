<?php

namespace Drupal\os_widgets\Plugin\OsWidgets;


use Drupal\block_content\Entity\BlockContent;
use Drupal\os_widgets\OsWidgetsBase;
use Drupal\os_widgets\OsWidgetsInterface;

/**
 * Class WidgetCollectionWidget
 * @package Drupal\os_widgets\Plugin\OsWidgets
 *
 * @OsWidget(
 *   id = "widget_collection_widget",
 *   title = @Translation("Collection of Widgets")
 * )
 */
class WidgetCollectionWidget extends OsWidgetsBase implements OsWidgetsInterface {

  /**
   * {@inheritdoc}
   */
  public function buildBlock(array &$build, BlockContent $blockContent) {
    $style = $blockContent->get('field_widget_collection_display')->getValue()[0]['value'];

    $build['field_widgets']['#render_style'] = $style;

    switch ($style) {
      case 'tabs':
        $build['#attached']['library'][] = 'os_widgets/tabbedWidget';
        break;
      case 'accordion':
        $build['#attached']['library'][] = 'os_widgets/accordionWidget';
        break;
      case 'random':
        $build['#attached']['library'][] = 'os_widgets/randomWidget';
        break;
    }
  }



}
