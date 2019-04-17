<?php

namespace Drupal\Tests\os_publications\ExistingSiteJavascript;

use Drupal\repec\RepecInterface;
use Drupal\Tests\openscholar\ExistingSiteJavascript\OsExistingSiteJavascriptTestBase;

/**
 * RepecTest.
 *
 * @group functional-javascript
 */
class RepecTest extends OsExistingSiteJavascriptTestBase {

  /**
   * Admin user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $admin;

  /**
   * Repec service.
   *
   * @var \Drupal\repec\RepecInterface
   */
  protected $repec;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->admin = $this->createUser([], '', TRUE);
    $this->repec = $this->container->get('repec');
  }

  /**
   * Checks whether mapping alters when a series type is selected.
   *
   * @covers \Drupal\repec\Form\EntityTypeSettingsForm::alterTemplateFieldMappingSettings
   *
   * @throws \Behat\Mink\Exception\DriverException
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\UnsupportedDriverActionException
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testReferenceMappingAlter() {
    $this->drupalLogin($this->admin);
    $web_assert = $this->assertSession();

    $series_type = RepecInterface::SERIES_WORKING_PAPER;
    /** @var array $template_fields */
    $template_fields = $this->repec->getTemplateFields($series_type);

    $this->visit('/admin/config/bibcite/settings/reference/types/artwork/repec');
    $this->getCurrentPage()->fillField('serie_type', $series_type);
    $this->waitForAjaxToFinish();

    foreach ($template_fields as $field_label) {
      $web_assert->pageTextContains($field_label);
    }
  }

}
