<?php
/**
 * Created by PhpStorm.
 * User: New User
 * Date: 11/5/2018
 * Time: 10:42 AM
 */

namespace Drupal\os_app_access\Form;


use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\os_app_access\AppAccessLevels;
use Drupal\vsite\AppInterface;
use Drupal\vsite\Plugin\AppManangerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AppAccessForm extends ConfigFormBase {

  /** @var AppManangerInterface */
  protected $app_manager;


  public function __construct (ConfigFactoryInterface $config_factory, AppManangerInterface $app_manager) {
    parent::__construct ($config_factory);
    $this->app_manager = $app_manager;
  }

  /**
   * @inheritDoc
   */
  public static function create (ContainerInterface $container) {
    return new static (
      $container->get('config.factory'),
      $container->get('vsite.app.manager')
    );
  }

  /**
   * @inheritDoc
   */
  protected function getEditableConfigNames () {
    return ['app.access'];
  }

  /**
   * @inheritDoc
   */
  public function getFormId () {
    return 'app_access';
  }

  /**
   * @inheritDoc
   */
  public function buildForm (array $form, FormStateInterface $form_state) {
    $form = parent::buildForm ($form, $form_state);

    $app_access = $this->config('app.access');
    /** @var AppInterface[] $apps */
    $apps = $this->app_manager->getDefinitions();

    $enabled = [];
    $disabled = [];
    foreach ($apps as $name => $app) {
      $level = $app_access->get($name);
      if (is_int($level)) {
        if ($app_access->get($name) != AppAccessLevels::DISABLED) {
          $enabled[] = $name;
        }
        else {
          $disabled[] = $name;
        }
      }
      else {
        $enabled[] = $name;
      }
    }

    $header_en = [
      'name' => t('Name'),
      'privacy' => t('Visibility'),
      'disable' => t('Disable')
    ];

    $header_dis = [
      'name' => t('Name'),
      'enable' => t('Enable')
    ];

    $form['enabled'] = [
      '#type' => 'table',
      '#header' => $header_en,
      '#empty' => t('No Apps Enabled'),
    ];

    $form['disabled'] = [
      '#type' => 'table',
      '#header' => $header_dis,
      '#empty' => t('No Apps Disabled'),
    ];

    foreach ($enabled as $k) {
      /** @var TranslatableMarkup $title */
      $title = $apps[$k]['title'];
      $form['enabled'][$k] = [
        'name' => [
          '#markup' => $title->render()
        ],
        'privacy' => [
          '#type' => 'select',
          '#options' => [
            AppAccessLevels::PUBLIC => t('Everyone'),
            AppAccessLevels::PRIVATE => t('Site Members Only')
          ],
          '#default_value' => $app_access->get($name),
        ],
        'disable' => [
          '#type' => 'checkbox',
          '#default_value' => false,
        ]
      ];
    }

    foreach ($disabled as $k) {
      /** @var TranslatableMarkup $title */
      $title = $apps[$k]['title'];
      $form['disabled'][$k] = [
        'name' => [
          '#markup' => $title->render(),
        ],
        'enable' => [
          '#type' => 'checkbox',
          '#default_value' => false,
        ]
      ];
    }

    return $form;
  }

  /**
   * @inheritDoc
   */
  public function submitForm (array &$form, FormStateInterface $form_state) {
    $app_access = $this->config ('app.access');
    $values = $form_state->getValues ();

    if (is_array($values['enabled'])) {
      foreach ($values['enabled'] as $app => $v) {
        if ($v['disable']) {
          $app_access->set($app, AppAccessLevels::DISABLED);
        }
        else {
          $app_access->set($app, $v['privacy']);
        }
      }
    }

    if (is_array($values['disabled'])) {
      foreach ($values['disabled'] as $app => $v) {
        if ($v['enable']) {
          $app_access->set($app, AppAccessLevels::PUBLIC);
        }
      }
    }

    $app_access->save(true);
    Cache::invalidateTags (['app:access_changed', 'config:system.menu.main']);

    parent::submitForm ($form, $form_state);
  }
}