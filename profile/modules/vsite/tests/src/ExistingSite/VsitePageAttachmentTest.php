<?php

namespace Drupal\Tests\vsite\ExistingSite;

use Drupal\Component\Serialization\Json;

/**
 * VsitePageAttachmentTest.
 *
 * @group functional
 * @group vsite
 */
class VsitePageAttachmentTest extends VsiteExistingSiteTestBase {

  /**
   * Administrator user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $admin;

  /**
   * Test vsite.
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $vsite;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->admin = $this->createUser([
      'view the administration theme',
      'access administration pages',
      'create personal group',
    ], NULL, TRUE);
    $this->vsite = $this->createGroup([
      'type' => 'personal',
      'path' => [
        'alias' => '/test-alias',
      ],
    ]);
  }

  /**
   * Tests vsite page attachments.
   *
   * @covers ::vsite_page_attachments
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testAttachments() {
    $this->drupalLogin($this->admin);

    $this->drupalGet('/test-alias');
    $this->assertSession()->responseContains(Json::encode([
      'id' => $this->vsite->id(),
      'label' => $this->vsite->label(),
      'purl' => 'test-alias',
      'url' => '/test-alias/',
    ]));

    $this->drupalGet('/group/add/personal');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseNotContains(Json::encode([
      'id' => $this->vsite->id(),
      'label' => $this->vsite->label(),
      'purl' => 'test-alias',
      'url' => '/test-alias/',
    ]));
  }

}
