<?php

namespace Drupal\Tests\vsite_favicon\ExistingSite;

use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * Vsite favicon tests.
 *
 * @group vsite
 * @group kernel
 */
class VsiteFaviconTest extends OsExistingSiteTestBase {

  /**
   * Test file favicon.
   *
   * @var \Drupal\file\Entity\File
   */
  protected $fileFavicon;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->fileFavicon = $this->createFile('image');
  }

  /**
   * Test favicon default is exists on vsite.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testFaviconOnVsiteDefault(): void {
    $web_assert = $this->assertSession();

    $this->visitViaVsite('', $this->group);
    $web_assert->statusCodeEquals(200);
    $expectedHtmlValue = 'profiles/contrib/openscholar/themes/os_base/favicon.ico" />';
    $this->assertContains($expectedHtmlValue, $this->getCurrentPageContent(), 'HTML head not contains favicon image.');
  }

  /**
   * Test favicon modified is exists on vsite.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testFaviconOnVsiteModified(): void {
    $web_assert = $this->assertSession();

    $config = $this->container->get('config.factory')->getEditable('vsite.settings');
    $config->set('favicon_fid', $this->fileFavicon->id());
    $config->save(TRUE);

    $this->visitViaVsite('', $this->group);
    $web_assert->statusCodeEquals(200);
    $expectedHtmlValue = 'sites/default/files/' . $this->fileFavicon->getFilename() . '" />';
    $this->assertContains($expectedHtmlValue, $this->getCurrentPageContent(), 'HTML head not contains favicon image.');
  }

}
