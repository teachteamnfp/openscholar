<?php

namespace Drupal\os_app_access\Plugin\views\access;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
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

  /** @var AppManangerInterface $app_manager */
  protected $app_manager;

  /** @var ConfigFactoryInterface $config_factory */
  protected $config_factory;

  public static function create (ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static (
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('vsite.app.manager'),
      $container->get('config.factory')
    );
  }

  public function __construct (array $configuration, string $plugin_id, $plugin_definition, AppManangerInterface $app_manager, ConfigFactoryInterface $config_factory) {
    parent::__construct ($configuration, $plugin_id, $plugin_definition);
    $this->app_manager = $app_manager;
    $this->config_factory = $config_factory;
  }

  public function summaryTitle() {
    return $this->t('App Access');
  }

  protected function defineOptions () {
    $options = parent::defineOptions ();
    $options['app'] = ['default' => []];
    return $options;
  }

  public function buildOptionsForm (&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm ($form, $form_state);

    $appList = $this->app_manager->getDefinitions ();
    $options = [];
    foreach ($appList as $name => $def) {
      /** @var TranslatableMarkup $title */
      $title = $def['title'];
      $options[$name] = $title->render();
    }

    $form['app'] = [
      '#type' => 'select',
      '#title' => t('App to Check Access For'),
      '#options' => $options,
      '#default_value' => $this->options['app'],
      '#description' => t('If the selected app is private or disabled, access will be restricted.')
    ];
  }

  /**
   * @inheritDoc
   */
  public function access (AccountInterface $account) {
    $app_levels = $this->config_factory->get('app.access');
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
      return $account->hasPermission ('access private apps');
    }
  }
  /**
   * @inheritDoc
   */
  public function alterRouteDefinition (Route $route) {
    $route->setRequirement ('_custom_access', '\Drupal\os_app_access\Access\AppAccess::accessFromRouteMatch');
  }
}