<?php

namespace Drupal\bibcite_preview\Controller;

use Drupal\Core\Entity\Controller\EntityViewController;
use Drupal\Core\Entity\EntityInterface;

/**
 * Defines a controller to render a single bibcite entity in preview.
 */
class BibciteEntityPreviewController extends EntityViewController {

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $bibcite_reference_preview, $view_mode_id = 'full', $langcode = NULL) {
    $bibcite_reference_preview->preview_view_mode = $view_mode_id;
    $build = parent::view($bibcite_reference_preview, $view_mode_id);

    $build['#attached']['library'][] = 'node/drupal.node.preview';

    // Don't render cache previews.
    unset($build['#cache']);

    return $build;
  }

  /**
   * The _title_callback for the page that renders a reference in preview.
   *
   * @param \Drupal\Core\Entity\EntityInterface $bibcite_reference_preview
   *   The current reference entity.
   *
   * @return string
   *   The page title.
   */
  public function title(EntityInterface $bibcite_reference_preview) {
    return $this->entityManager->getTranslationFromContext($bibcite_reference_preview)->label();
  }

}
