<?php

namespace Drupal\Tests\cp_appearance\ExistingSite;

use Drupal\Tests\cp_appearance\Traits\CpAppearanceTestTrait;
use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * TestBase for cp_appearance tests.
 */
abstract class TestBase extends OsExistingSiteTestBase {

  use CpAppearanceTestTrait;

  public const TEST_CUSTOM_THEME_1_NAME = 'os_ct_cp_appearance_test_1';

  public const TEST_CUSTOM_THEME_2_NAME = 'os_ct_cp_appearance_test_2';

  public const TEST_CUSTOM_THEME_3_NAME = 'os_ct_cp_appearance_test_3';

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Vsite context manager service.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Appearance settings builder service.
   *
   * @var \Drupal\cp_appearance\AppearanceSettingsBuilderInterface
   */
  protected $appearanceSettingsBuilder;

  /**
   * Default theme.
   *
   * @var string
   */
  protected $defaultTheme;

  /**
   * Group administrator.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $groupAdmin;

  /**
   * Theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->groupAdmin = $this->createUser();

    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->vsiteContextManager = $this->container->get('vsite.context_manager');
    $this->configFactory = $this->container->get('config.factory');
    $this->appearanceSettingsBuilder = $this->container->get('cp_appearance.appearance_settings_builder');
    /** @var \Drupal\Core\Config\ImmutableConfig $theme_config */
    $theme_config = $this->configFactory->get('system.theme');
    $this->defaultTheme = $theme_config->get('default');
    $this->themeHandler = $this->container->get('theme_handler');
    /** @var \Drupal\cp_appearance\CustomThemeInstaller $custom_theme_installer */
    $custom_theme_installer = $this->container->get('cp_appearance.custom_theme_installer');
    $custom_theme_installer->install([
      self::TEST_CUSTOM_THEME_1_NAME,
      self::TEST_CUSTOM_THEME_2_NAME,
    ]);
    // If cache not cleared, then the system fails to identify the theme that is
    // just installed in the test.
    $this->themeHandler->refreshInfo();
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    // This is part of the cleanup.
    // If this is not done, then it leads to deadlock errors in Travis
    // https://travis-ci.org/openscholar/openscholar/jobs/540643382.
    // My understanding, big_pipe initiates some sort of request in background,
    // which puts a lock in the database. That lock hinders the test cleanup.
    // Putting this to sleep for arbitrary amount of time seems to fix
    // the problem.
    \sleep(5);

    /** @var \Drupal\Core\Config\Config $theme_config_mut */
    $theme_config_mut = $this->configFactory->getEditable('system.theme');
    $theme_config_mut->set('default', $this->defaultTheme)->save();

    /** @var \Drupal\cp_appearance\CustomThemeInstaller $custom_theme_installer */
    $custom_theme_installer = $this->container->get('cp_appearance.custom_theme_installer');
    $custom_theme_installer->uninstall([
      self::TEST_CUSTOM_THEME_1_NAME,
      self::TEST_CUSTOM_THEME_2_NAME,
    ]);

    parent::tearDown();
  }

}
