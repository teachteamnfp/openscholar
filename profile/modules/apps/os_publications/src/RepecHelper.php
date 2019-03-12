<?php

namespace Drupal\os_publications;

use Drupal\bibcite_entity\Entity\Contributor;
use Drupal\bibcite_entity\Entity\Keyword;
use Drupal\bibcite_entity\Entity\ReferenceInterface;

/**
 * RepecHelper.
 */
final class RepecHelper implements RepecHelperInterface {

  /**
   * The reference entity.
   *
   * @var \Drupal\bibcite_entity\Entity\ReferenceInterface
   */
  protected $reference;

  /**
   * RepecHelper constructor.
   *
   * @param \Drupal\bibcite_entity\Entity\ReferenceInterface $reference
   *   The reference entity.
   */
  public function __construct(ReferenceInterface $reference) {
    $this->reference = $reference;
  }

  /**
   * {@inheritdoc}
   */
  public function getContributor() : array {
    /** @var \Drupal\Core\Field\FieldItemListInterface $items */
    $items = $this->reference->get('author');
    if ($items->isEmpty()) {
      return [];
    }

    $contributor_ids = [];
    foreach ($items as $item) {
      $contributor_ids[] = $item->getValue()['target_id'];
    }

    return array_map(function ($id) {
      return Contributor::load($id);
    }, $contributor_ids);
  }

  /**
   * {@inheritdoc}
   */
  public function getKeywords() : array {
    /** @var \Drupal\Core\Field\FieldItemListInterface $items */
    $items = $this->reference->get('keywords');

    if ($items->isEmpty()) {
      return [];
    }

    $keyword_ids = [];
    foreach ($items as $item) {
      $keyword_ids[] = $item->getValue()['target_id'];
    }

    return array_map(function ($id) {
      return Keyword::load($id);
    }, $keyword_ids);
  }

}
