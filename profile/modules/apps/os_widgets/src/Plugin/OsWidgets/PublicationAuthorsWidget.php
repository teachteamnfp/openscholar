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
    if (empty($block_content)) {
      return;
    }
    $view = Views::getView('publication_contributors');
    if (is_object($view)) {
      $view->setDisplay('default');
      $view->preExecute();
      $view->execute();
      $build['authors_list'] = $view->buildRenderable('default');
    }
  }

}
