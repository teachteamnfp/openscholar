<?php

namespace Drupal\Tests\vsite\ExistingSite;

use Drupal\Core\Asset\AttachedAssets;
use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * Tests contextual link alterations for vsites.
 *
 * @group vsite
 * @group kernel
 */
class VsiteContextualLinksTest extends OsExistingSiteTestBase {

  /**
   * Tests whether the destination parameter is valid.
   *
   * @covers ::vsite_js_settings_alter
   */
  public function testDestinationParameter(): void {
    // Setup.
    /** @var \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager */
    $vsite_context_manager = $this->container->get('vsite.context_manager');
    $vsite_context_manager->activateVsite($this->group);
    /** @var \Drupal\Core\Asset\AssetResolverInterface $asset_resolver */
    $asset_resolver = $this->container->get('asset.resolver');
    $build['#attached']['library'][] = 'core/drupalSettings';
    $assets = AttachedAssets::createFromRenderArray($build);
    $javascript = $asset_resolver->getJsAssets($assets, FALSE)[1];

    // Tests.
    $this->assertEquals('', $javascript['drupalSettings']['data']['path']['currentPath']);
  }

}
