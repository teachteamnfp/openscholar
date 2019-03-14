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
      'redirect_source' => [
        'path' => 'lorem1',
      ],
      'redirect_redirect' => [
        'uri' => 'http://example.com',
      ],
    ]);
    $this->group->addContent($redirect, $plugin->getContentPluginId());
  }

  /**
   * Tests cp redirects listing.
   */
  public function testCpRedirectListing() {
    $web_assert = $this->assertSession();
    $this->drupalLogin($this->siteUser);

    $this->visit($this->group->get('path')->getValue()[0]['alias'] . "/cp/redirects/list");
    $web_assert->statusCodeEquals(200);
    $this->assertContains('lorem1', $this->getCurrentPageContent(), 'Test redirect is source not visible.');
    $this->assertContains('http://example.com', $this->getCurrentPageContent(), 'Test redirect uri is not visible.');

    // Check global list visibility.
    $this->visit("/cp/redirects/list");
    $web_assert->statusCodeEquals(200);
    $this->assertNotContains('lorem1', $this->getCurrentPageContent(), 'Test redirect is source visible.');
    $this->assertNotContains('http://example.com', $this->getCurrentPageContent(), 'Test redirect uri is visible.');
  }

}
