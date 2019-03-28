<?php

namespace Drupal\Tests\os_widgets\ExistingSite;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Base class for os_widgets tests.
 */
class OsWidgetsExistingSiteTestBase extends ExistingSiteBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Handle all widgets with plugin manager.
   *
   * @var \Drupal\os_widgets\OsWidgetsInterface
   *   Os Widgets Interface.
   */
  protected $osWidgets;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->osWidgets = $this->container->get('plugin.manager.os_widgets');
  }

  /**
   * Creates a block content.
   *
   * @param array $values
   *   (optional) The values used to create the entity.
   *
   * @return \Drupal\block_content\Entity\BlockContent
   *   The created block content entity.
   */
  protected function createBlockContent(array $values = []) {
    $block_content = $this->entityTypeManager->getStorage('block_content')->create($values + [
      'type' => 'basic',
    ]);
    $block_content->enforceIsNew();
    $block_content->save();

    $this->markEntityForCleanup($block_content);

    return $block_content;
  }

}
