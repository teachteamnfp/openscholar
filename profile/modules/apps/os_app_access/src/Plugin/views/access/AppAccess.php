<?php

namespace Drupal\os_app_access\Plugin\views\access;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\os_app_access\AppAccessLevels;
use Drupal\views\Plugin\views\access\AccessPluginBase;
use Drupal\vsite\Plugin\AppManangerInterface;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;
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
   * Vsite context manager.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('vsite.app.manager'),
      $container->get('config.factory'),
      $container->get('vsite.context_manager')
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
   * @param \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager
   *   Vsite context manager.
   */
  public function __construct(array $configuration, string $plugin_id, $plugin_definition, AppManangerInterface $app_manager, ConfigFactoryInterface $config_factory, VsiteContextManagerInterface $vsite_context_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->appManager = $app_manager;
    $this->configFactory = $config_factory;
    $this->vsiteContextManager = $vsite_context_manager;
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
    /** @var \Drupal\group\Entity\GroupInterface|null $active_vsite */
    $active_vsite = $this->vsiteContextManager->getActiveVsite();

    if (!$active_vsite) {
      return FALSE;
    }

    $app_levels = $this->configFactory->get('os_app_access.access');
    $app = $this->options['app'];
    $level = (int) $app_levels->get($app);
    if (!isset($level)) {
      $level = AppAccessLevels::PUBLIC;
    }

    if ($level === AppAccessLevels::DISABLED) {
      return FALSE;
    }

    /** @var array $group_permissions */
    $group_permissions = $this->appManager->getViewContentGroupPermissionsForApp($this->options['app']);
    $default_access = FALSE;

    if ($level === AppAccessLevels::PUBLIC || $level === AppAccessLevels::PRIVATE) {
      foreach ($group_permissions as $group_permission) {
        $default_access = $active_vsite->hasPermission($group_permission, $account);
      }
    }

    if ($level === AppAccessLevels::PUBLIC) {
      return $default_access;
    }

    if ($level === AppAccessLevels::PRIVATE) {
      return ($default_access && $active_vsite->hasPermission('access private apps', $account));
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function alterRouteDefinition(Route $route) {
    $route->setRequirement('_custom_access', '\Drupal\os_app_access\Access\AppAccess::accessFromRouteMatch');
  }

}
