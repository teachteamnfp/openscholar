<?php

namespace Drupal\Tests\vsite_infinite_scroll\ExistingSite;

use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;
use Drupal\views\Views;
use Drupal\vsite_infinite_scroll\Plugin\views\pager\VsiteInfiniteScroll;

/**
 * Test the Infinite Scroll Vsite Views pager.
 *
 * @package Drupal\Tests\vsite\Kernel
 * @group vsite
 * @group kernel
 * @coversDefaultClass \Drupal\vsite_infinite_scroll\Plugin\views\pager\VsiteInfiniteScroll
 */
class VsiteInfiniteScrollTest extends OsExistingSiteTestBase {

  /**
   * Group parent test entity.
   *
   * @var \Drupal\group\Entity\Group
   */
  protected $group;

  /**
   * Vsite Context Manager.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * Drupal renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   *   Renderer.
   */
  protected $renderer;

  /**
   * Vsite infinite scroll config.
   *
   * @var \Drupal\Core\Config\Config
   *   Config.
   */
  protected $config;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $this->container->get('entity_type.manager');

    // Set the current user so group creation can rely on it.
    $this->container->get('current_user')->setAccount($this->createUser());

    // Enable the user_as_content plugin on the default group type.
    /** @var \Drupal\group\Entity\Storage\GroupContentTypeStorageInterface $storage */
    $storage = $entity_type_manager->getStorage('group_content_type');
    /** @var \Drupal\group\Entity\GroupContentTypeInterface[] $plugin */
    $plugins = $storage->loadByContentPluginId('group_node:person');
    /** @var \Drupal\group\Entity\GroupContentTypeInterface $plugin */
    $plugin = reset($plugins);

    $this->group = $this->createGroup([
      'type' => 'personal',
      'title' => 'Site01',
    ]);
    $this->group->save();

    $i = 0;
    while ($i < 11) {
      $person = $this->createNode([
        'type' => 'person',
        'status' => 1,
      ]);
      $this->group->addContent($person, $plugin->getContentPluginId());
      $i++;
    }

    $this->vsiteContextManager = $this->container->get('vsite.context_manager');
    $this->vsiteContextManager->activateVsite($this->group);
    $this->renderer = \Drupal::service('renderer');
    $this->config = \Drupal::configFactory()->getEditable('vsite_infinite_scroll.settings');
  }

  /**
   * Check views pager settings and default result.
   */
  public function testRenderedDefaultPager(): void {
    // Modify config to empty string.
    $this->config->set('long_list_content_pagination', '');
    $this->config->save(TRUE);

    $render_view = $this->renderPeopleView();
    $this->assertInstanceOf(VsiteInfiniteScroll::class, $render_view['#view']->pager);
    $html = $this->renderer->renderPlain($render_view)->__toString();
    $this->assertContains('Load More', $html, 'Vsite infinite scroll is not visible. (null config)');

    $this->config->set('long_list_content_pagination', 'infinite_scroll');
    $this->config->save(TRUE);

    $render_view = $this->renderPeopleView();
    $html = $this->renderer->renderPlain($render_view)->__toString();
    $this->assertContains('Load More', $html, 'Vsite infinite scroll is not visible. (config set to infinite_scroll)');
  }

  /**
   * Check views pager settings and default result.
   */
  public function testRenderedModifiedPager(): void {
    // Modify default config to pager.
    $this->config->set('long_list_content_pagination', 'pager');
    $this->config->save(TRUE);

    $render_view = $this->renderPeopleView();
    $this->assertInstanceOf(VsiteInfiniteScroll::class, $render_view['#view']->pager);
    $html = $this->renderer->renderPlain($render_view)->__toString();
    $this->assertContains('Next page', $html, 'Default pager is not visible.');
  }

  /**
   * Return rendered array of people view.
   *
   * @return array|null
   *   Rendered array.
   */
  protected function renderPeopleView(): ?array {
    $view = Views::getView('people');
    $view->setDisplay('page_1');
    return $view->render();
  }

}
