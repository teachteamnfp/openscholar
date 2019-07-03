<?php

namespace Drupal\Tests\cp_appearance\ExistingSiteJavascript;

use Drupal\cp_appearance\Entity\CustomTheme;
use Drupal\Tests\cp_appearance\Traits\CpAppearanceTestTrait;
use Drupal\Tests\openscholar\ExistingSiteJavascript\OsExistingSiteJavascriptTestBase;

/**
 * Test base for cp appearance functional javascript tests.
 */
abstract class CpAppearanceExistingSiteJavascriptTestBase extends OsExistingSiteJavascriptTestBase {

  use CpAppearanceTestTrait;

  /**
   * Default theme name.
   *
   * @var string
   */
  protected $defaultTheme;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    /** @var \Drupal\Core\Config\ImmutableConfig $theme_config */
    $theme_config = $this->container->get('config.factory')->get('system.theme');
    $this->defaultTheme = $theme_config->get('default');

    chmod(CustomTheme::ABSOLUTE_CUSTOM_THEMES_LOCATION, 0777);
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    /** @var \Drupal\Core\Config\Config $theme_setting_mut */
    $theme_setting_mut = $this->container->get('config.factory')->getEditable('system.theme');
    $theme_setting_mut->set('default', $this->defaultTheme)->save();

    parent::tearDown();
  }

}
