<?php

namespace Drupal\Tests\os_widgets\Traits;

use Drupal\block_content\BlockContentInterface;

trait WidgetCreationTrait {

  /**
   * Creates a block content.
   *
   * @param array $values
   *   (optional) The values used to create the entity.
   *
   * @return BlockContentInterface
   *   The created block content entity.
   */
  protected function createBlockContent(array $values = []) {
    /** @var \Drupal\block_content\Entity\BlockContent $block_content */
    $block_content = $this->container->get('entity_type.manager')->getStorage('block_content')->create($values + [
        'type' => 'basic',
      ]);
    $block_content->enforceIsNew();
    $block_content->save();

    $this->markEntityForCleanup($block_content);

    return $block_content;
  }

}