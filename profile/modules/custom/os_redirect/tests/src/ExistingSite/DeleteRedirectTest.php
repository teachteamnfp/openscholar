<?php

namespace Drupal\Tests\os_redirect\ExistingSite;

/**
 * Tests os_redirect module.
 *
 * @group redirect
 * @group kernel
 */
class DeleteRedirectTest extends OsRedirectTestBase {

  protected $siteUser;
  protected $deletableRedirect;
  protected $otherGroup;

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
    $this->container->get('current_user')->setAccount($this->siteUser);

    // Enable the user_as_content plugin on the default group type.
    /** @var \Drupal\group\Entity\Storage\GroupContentTypeStorageInterface $storage */
    $storage = $this->entityTypeManager->getStorage('group_content_type');
    /** @var \Drupal\group\Entity\GroupContentTypeInterface[] $plugin */
    $plugins = $storage->loadByContentPluginId('group_entity:redirect');
    /** @var \Drupal\group\Entity\GroupContentTypeInterface $plugin */
    $plugin = reset($plugins);

    $this->deletableRedirect = $this->createRedirect([
      'redirect_source' => [
        'path' => 'deletable',
      ],
      'redirect_redirect' => [
        'uri' => 'http://example.com',
      ],
    ]);
    $this->group->addContent($this->deletableRedirect, $plugin->getContentPluginId());
    $this->otherGroup = $this->createGroup([
      'path' => '/other-group',
    ]);
  }

  /**
   * Tests delete redirect.
   */
  public function testDeleteRedirectInVsite() {
    $web_assert = $this->assertSession();
    $this->drupalLogin($this->siteUser);

    $this->visit($this->group->get('path')->getValue()[0]['alias'] . "/cp/redirects/delete/" . $this->deletableRedirect->id());
    $web_assert->statusCodeEquals(200);

    $this->drupalPostForm(NULL, [], 'Delete');
    $this->assertContains('The redirect <em class="placeholder">http://example.com</em> has been deleted.', $this->getCurrentPageContent());
  }

  /**
   * Tests delete redirect.
   */
  public function testDeleteRedirectOtherGroup() {
    $web_assert = $this->assertSession();
    $this->drupalLogin($this->siteUser);

    $this->visit($this->otherGroup->get('path')->getValue()[0]['alias'] . "/cp/redirects/delete/" . $this->deletableRedirect->id());
    $web_assert->statusCodeEquals(403);
  }

  /**
   * Tests delete redirect.
   */
  public function testDeleteRedirectNotFound() {
    $web_assert = $this->assertSession();
    $this->drupalLogin($this->siteUser);

    $this->visit($this->group->get('path')->getValue()[0]['alias'] . "/cp/redirects/delete/99999");
    $web_assert->statusCodeEquals(404);
  }

}
