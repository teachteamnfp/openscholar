<?php

namespace Drupal\Tests\os_publications\ExistingSiteJavascript;

use Drupal\repec\RepecInterface;
use weitzman\DrupalTestTraits\ExistingSiteWebDriverTestBase;

/**
 * RepecTest.
 *
 * @group functional-javascript
 */
class RepecTest extends ExistingSiteWebDriverTestBase {

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

  /**
   * Waits for the given time or until the given JS condition becomes TRUE.
   *
   * Shamelessly copied from DrupalCommerce.
   *
   * @param string $condition
   *   JS condition to wait until it becomes TRUE.
   * @param int $timeout
   *   (Optional) Timeout in milliseconds, defaults to 1000.
   * @param string $message
   *   (optional) A message to display with the assertion. If left blank, a
   *   default message will be displayed.
   *
   * @see \Behat\Mink\Driver\DriverInterface::evaluateScript()
   *
   * @throws \Behat\Mink\Exception\UnsupportedDriverActionException
   * @throws \Behat\Mink\Exception\DriverException
   */
  protected function assertJsCondition($condition, $timeout = 1000, $message = '') {
    $message = $message ?: "Javascript condition met:\n" . $condition;
    $result = $this->getSession()->getDriver()->wait($timeout, $condition);
    $this->assertNotEmpty($result, $message);
  }

  /**
   * Waits for jQuery to become active and animations to complete.
   *
   * Shamelessly copied from DrupalCommerce.
   *
   * @throws \Behat\Mink\Exception\UnsupportedDriverActionException
   * @throws \Behat\Mink\Exception\DriverException
   */
  protected function waitForAjaxToFinish() {
    $condition = "(0 === jQuery.active && 0 === jQuery(':animated').length)";
    $this->assertJsCondition($condition, 10000);
  }

}
