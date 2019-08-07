<?php

namespace Drupal\Tests\cp_taxonomy\ExistingSiteJavascript;

use Drupal\Tests\openscholar\ExistingSiteJavascript\OsExistingSiteJavascriptTestBase;

/**
 * Class CpTaxonomySettingsTest.
 *
 * @group cp
 * @group functional-javascript
 *
 * @package Drupal\Tests\cp_taxonomy\ExistingSite
 */
class CpTaxonomySettingsTest extends OsExistingSiteJavascriptTestBase {

  protected $group;
  protected $configTaxonomy;

  /**
   * The admin user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $groupAdmin;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $configFactory = $this->container->get('config.factory');
    $this->configTaxonomy = $configFactory->getEditable('cp_taxonomy.settings');
    $this->groupAdmin = $this->createUser();
    $this->addGroupAdmin($this->groupAdmin, $this->group);
  }

  /**
   * Test group admin settings form.
   */
  public function testCpSettingsTaxonomyFormSelectNone() {
    $this->configTaxonomy->set('display_term_under_content_teaser_types', NULL);
    $this->configTaxonomy->save(TRUE);

    $this->drupalLogin($this->groupAdmin);
    $this->visitViaVsite('cp/settings/taxonomy', $this->group);
    $this->assertSession()->statusCodeEquals(200);
    $page = $this->getCurrentPageContent();
    // Assert checkboxes are checked.
    $this->assertContains('name="display_term_under_content_teaser_types[bibcite_reference:*]" value="bibcite_reference:*" checked="checked" class="form-checkbox"', $page);
    $this->assertContains('name="display_term_under_content_teaser_types[media:*]" value="media:*" checked="checked" class="form-checkbox"', $page);

    $edit = [
      'display_term_under_content_teaser_types[bibcite_reference:*]' => 0,
      'display_term_under_content_teaser_types[media:*]' => 0,
    ];
    $this->drupalPostForm(NULL, $edit, 'Save configuration');
    $this->assertSession()->statusCodeEquals(200);
    $page = $this->getCurrentPageContent();
    // Assert checkboxes are unchecked.
    $this->assertContains('name="display_term_under_content_teaser_types[bibcite_reference:*]" value="bibcite_reference:*" class="form-checkbox"', $page);
    $this->assertContains('name="display_term_under_content_teaser_types[media:*]" value="media:*" class="form-checkbox"', $page);
  }

  /**
   * Test group admin settings form.
   */
  public function testCpSettingsTaxonomyForm() {
    $this->configTaxonomy->set('display_term_under_content', '1');
    $this->configTaxonomy->set('display_term_under_content_teaser_types', NULL);
    $this->configTaxonomy->save(TRUE);

    $this->drupalLogin($this->groupAdmin);
    $this->visitViaVsite('cp/settings/taxonomy', $this->group);
    $this->assertSession()->statusCodeEquals(200);
    $page = $this->getCurrentPageContent();
    // Assert checkboxes are checked.
    $this->assertContains('name="display_term_under_content" value="1" checked="checked" class="form-checkbox"', $page);
    $this->assertContains('name="display_term_under_content_teaser_types[bibcite_reference:*]" value="bibcite_reference:*" checked="checked" class="form-checkbox"', $page);

    $edit = [
      'display_term_under_content' => '0',
      'display_term_under_content_teaser_types[bibcite_reference:*]' => '',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save configuration');
    $this->assertSession()->statusCodeEquals(200);
    $page = $this->getCurrentPageContent();
    // Assert checkboxes are unchecked.
    $this->assertContains('name="display_term_under_content" value="1" class="form-checkbox"', $page);
    $this->assertContains('name="display_term_under_content_teaser_types[bibcite_reference:*]" value="bibcite_reference:*" class="form-checkbox"', $page);
  }

}
