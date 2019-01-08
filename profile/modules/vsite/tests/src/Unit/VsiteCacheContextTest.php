<?php

namespace Drupal\vsite\Tests\Unit;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\vsite\Cache\VsiteCacheContext;

/**
 * Tests vsite cache context.
 *
 * @group vsite
 * @coversDefaultClass \Drupal\vsite\Cache\VsiteCacheContext
 */
class VsiteCacheContextTest extends UnitTestCase {

  /**
   * Tests vsite cache context.
   */
  public function testGetContext() {
    $group = $this->createMock(GroupInterface::class);
    $entity_storage = $this->createMock(EntityStorageInterface::class);
    $entity_type_manager = $this->createMock(EntityTypeManagerInterface::class);

    $group
      ->method('id')
      ->willReturn('1');
    $entity_type_manager
      ->method('getStorage')
      ->with('group')
      ->willReturn($entity_storage);
    $entity_storage
      ->method('load')
      ->with($group->id())
      ->willReturn($group);

    $vsite_cache_context = new VsiteCacheContext($entity_type_manager);

    $this->assertEquals('group:1', $vsite_cache_context->getContext($group->id()));
    $this->assertNull($vsite_cache_context->getContext());
    $this->assertEquals(new CacheableMetadata(), $vsite_cache_context->getCacheableMetadata());
  }

}
