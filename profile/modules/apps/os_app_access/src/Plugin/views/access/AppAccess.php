<?php

namespace Drupal\os_app_access\Plugin\views\access;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\os_app_access\AppAccessLevels;
use Drupal\views\Plugin\views\access\AccessPluginBase;
use Drupal\vsite\Plugin\AppManangerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;

/**
 * Access plugin that provides role-based access control.
 *
 * @ingroup views_access_plugins
 *
 * @ViewsAccess(
 *   id = "app",
 *   title = @Translation("App"),
 *   help = @Translation("Access will be granted to users with any of the specified roles.")
 * )
 */
class AppAccess extends AccessPluginBase {

  /**
   * {@inheritdoc}
   */
  protected $usesOptions = TRUE;

  /**
   * App manager.
   *
   * @var \Drupal\vsite\Plugin\AppManangerInterface
   */
  protected $appManager;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('vsite.app.manager'),
      $container->get('config.factory')
    );
  }

  /**
   * Create new AppAccess object.
   *
   * @param array $configuration
   *   Configuration.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\vsite\Plugin\AppManangerInterface $app_manager
   *   App manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   */
  public function __construct(array $configuration, string $plugin_id, $plugin_definition, AppManangerInterface $app_manager, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->appManager = $app_manager;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function summaryTitle() {
    return $this->t('App Access');
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['app'] = ['default' => []];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $appList = $this->appManager->getDefinitions();
    $options = [];
    foreach ($appList as $name => $def) {
      /** @var \Drupal\Core\StringTranslation\TranslatableMarkup $title */
      $title = $def['title'];
      $options[$name] = $title->render();
    }

    $form['app'] = [
      '#type' => 'select',
      '#title' => t('App to Check Access For'),
      '#options' => $options,
      '#default_value' => $this->options['app'],
      '#description' => t('If the selected app is private or disabled, access will be restricted.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account) {
    $app_levels = $this->configFactory->get('os_app_access.access');
    $app = $this->options['app'];
    $level = $app_levels->get($app);
    if (!isset($level)) {
      $level = AppAccessLevels::PUBLIC;
    }

    if ($level == AppAccessLevels::DISABLED) {
      return FALSE;
    }
    elseif ($level == AppAccessLevels::PUBLIC) {
      return TRUE;
    }
    elseif ($level == AppAccessLevels::PRIVATE) {
      return $account->hasPermission('access private apps');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function alterRouteDefinition(Route $route) {
    $route->setRequirement('_custom_access', '\Drupal\os_app_access\Access\AppAccess::accessFromRouteMatch');
  }

}
