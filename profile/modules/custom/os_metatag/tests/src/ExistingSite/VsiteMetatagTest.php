<?php

namespace Drupal\Tests\os_metatag\ExistingSite;

/**
 * Vsite metatag tests.
 *
 * @group metatag
 * @group kernel
 * @group other
 */
class VsiteMetatagTest extends OsMetatagTestBase {

  /**
   * Test group.
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $group;

  /**
   * Test file logo.
   *
   * @var \Drupal\file\Entity\File
   */
  protected $fileLogo;

  /**
   * A test user with group creation rights.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $groupCreator;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    /** @var \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager */
    $vsite_context_manager = $this->container->get('vsite.context_manager');

    $this->fileLogo = $this->createFile();
    $this->group = $this->createGroup([
      'path' => [
        'alias' => '/test-alias',
      ],
      'field_site_logo' => [
        'target_id' => $this->fileLogo->id(),
        'alt' => 'lorem',
      ],
      'field_site_description' => '<p>Lorem ipsum dolor</p>',
    ]);
    $vsite_context_manager->activateVsite($this->group);

    $this->groupCreator = $this->createUser([
      'bypass group access',
    ]);
    $this->drupalLogin($this->groupCreator);
  }

  /**
   * Test metatags is exists on vsite frontpage.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testMetatagsOnVsiteFrontPage() {
    $web_assert = $this->assertSession();

    $this->visit("/test-alias/");
    $web_assert->statusCodeEquals(200);
    $expectedHtmlValue = '<meta name="twitter:image" content="http://apache/sites/default/files/styles/large/public/' . $this->fileLogo->getFilename();
    $this->assertContains($expectedHtmlValue, $this->getCurrentPageContent(), 'HTML head not contains twitter image.');
    $expectedHtmlValue = '<meta property="og:image" content="http://apache/sites/default/files/styles/large/public/' . $this->fileLogo->getFilename();
    $this->assertContains($expectedHtmlValue, $this->getCurrentPageContent(), 'HTML head not contains og image.');
    $expectedHtmlValue = '<meta property="og:type" content="personal" />';
    $this->assertContains($expectedHtmlValue, $this->getCurrentPageContent(), 'HTML head not contains og type.');
    $expectedHtmlValue = '<meta name="twitter:description" content="Lorem ipsum dolor" />';
    $this->assertContains($expectedHtmlValue, $this->getCurrentPageContent(), 'HTML head not contains og type.');

  }

  /**
   * Test metatags is changed when you save cp/setting.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testMetatagChangeOnVsiteFrontPage() {
    $this->group->addMember($this->groupCreator);

    $this->visit("/test-alias/cp/settings/seo");
    $this->assertSession()->statusCodeEquals(200);
    $this->getCurrentPage()->findField('meta_description')->setValue('Find the Door');
    $this->getCurrentPage()->pressButton('Save configuration');

    $this->visit("/test-alias/");
    $this->assertSession()->statusCodeEquals(200);
    $expectedHtmlValue = '<meta name="twitter:description" content="Find the Door" />';
    $this->assertContains($expectedHtmlValue, $this->getCurrentPageContent(), 'HTML head not contains twitter description.');

  }

}
