<?php

namespace Drupal\Tests\os_redirect\ExistingSite;

/**
 * Tests os_redirect module.
 *
 * @group redirect
 * @group kernel
 */
class ControllerTest extends OsRedirectTestBase {

  protected $siteUser;

  /**
   * Vsite Context Manager.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->siteUser = $this->createUser([
      'access control panel',
      'administer control panel redirects',
    ]);

    // Set the current user so group creation can rely on it.
    $this->container->get('current_user')->setAccount($this->createUser());

    // Enable the user_as_content plugin on the default group type.
    /** @var \Drupal\group\Entity\Storage\GroupContentTypeStorageInterface $storage */
    $storage = $this->entityTypeManager->getStorage('group_content_type');
    /** @var \Drupal\group\Entity\GroupContentTypeInterface[] $plugin */
    $plugins = $storage->loadByContentPluginId('group_entity:redirect');
    /** @var \Drupal\group\Entity\GroupContentTypeInterface $plugin */
    $plugin = reset($plugins);

    $redirect = $this->createRedirect([
      'source' => [
        'path' => 'lorem1',
      ],
      'redirect' => 'http://example.com',
    ]);
    $this->group->addContent($redirect, $plugin->getContentPluginId());

    $this->vsiteContextManager = $this->container->get('vsite.context_manager');
    $this->vsiteContextManager->activateVsite($this->group);
  }

  /**
   * Tests cp redirects listing.
   */
  public function testCpRedirectListing() {
    $web_assert = $this->assertSession();
    $this->drupalLogin($this->siteUser);

    $this->visit("/cp/redirects/list");
    $web_assert->statusCodeEquals(200);
    $expectedHtmlValue = 'lorem1';
    $this->assertNotContains($expectedHtmlValue, $this->getCurrentPageContent(), 'Test redirect entity not visible.');
  }

}
