<?php

namespace Drupal\os_widgets\Plugin\OsWidgets;

use Drupal\os_widgets\OsWidgetsBase;
use Drupal\os_widgets\OsWidgetsInterface;
use Drupal\views\Views;

/**
 * Class PublicationAuthorsWidget.
 *
 * @OsWidget(
 *   id = "publication_authors_widget",
 *   title = @Translation("Publication authors")
 * )
 */
class PublicationAuthorsWidget extends OsWidgetsBase implements OsWidgetsInterface {

  /**
   * {@inheritdoc}
   */
  public function buildBlock(&$build, $block_content) {
    $view = Views::getView('publication_contributors');
    if (is_object($view)) {
      $view->storage->addCacheTags([$block_content->getVsiteCacheTag()]);
      $view->setDisplay('default');
      $field_display_count_values = $block_content->get('field_display_count')->getValue();
      // Hide count field if display count is disabled.
      if (empty($field_display_count_values[0]['value'])) {
        $view->removeHandler('default', 'field', 'id_1');
      }
      $view->preExecute();
      $view->preview();
      $view->execute();
      $build['authors_list'] = $view->buildRenderable('default');
    }
  }

}
