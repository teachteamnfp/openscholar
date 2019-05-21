<?php

namespace Drupal\os_metatag\Plugin\CpSetting;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\cp_settings\CpSettingBase;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * CP metatag setting.
 *
 * @CpSetting(
 *   id = "os_metatag_setting",
 *   title = @Translation("OS Metatag"),
 *   group = {
 *    "id" = "seo",
 *    "title" = @Translation("SEO"),
 *    "parent" = "cp.settings.global"
 *   }
 * )
 */
class OsMetatagSetting extends CpSettingBase {

  /**
   * CacheBackend Service.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $renderCache;

  /**
   * OsMetatagSetting constructor.
   *
   * @param array $configuration
   *   Plugin Configuration.
   * @param string $plugin_id
   *   Plugin Id.
   * @param mixed $plugin_definition
   *   Plugin Definition.
   * @param \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager
   *   Vsite Context Manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, VsiteContextManagerInterface $vsite_context_manager, CacheBackendInterface $cache) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $vsite_context_manager);
    $this->renderCache = $cache;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('vsite.context_manager'),
      $container->get('cache.render')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames(): array {
    return ['os_metatag.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array &$form, ConfigFactoryInterface $configFactory) {
    $config = $configFactory->get('os_metatag.settings');
    $form['site_title'] = [
      '#type' => 'textfield',
      '#title' => t('Site Title'),
      '#default_value' => $config->get('site_title'),
    ];
    $form['meta_description'] = [
      '#type' => 'textarea',
      '#title' => t('Meta Description'),
      '#default_value' => $config->get('meta_description'),
    ];
    $form['publisher_url'] = [
      '#type' => 'textfield',
      '#title' => t('Publisher URL'),
      '#default_value' => $config->get('publisher_url'),
    ];
    $form['author_url'] = [
      '#type' => 'textfield',
      '#title' => t('Author URL'),
      '#default_value' => $config->get('author_url'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(
    FormStateInterface $formState,
    ConfigFactoryInterface $configFactory
  ) {
    $config = $configFactory->getEditable('os_metatag.settings');
    $config->set('site_title', $formState->getValue('site_title'));
    $config->set('meta_description', $formState->getValue('meta_description'));
    $config->set('publisher_url', $formState->getValue('publisher_url'));
    $config->set('author_url', $formState->getValue('author_url'));
    $config->save(TRUE);
    $this->renderCache->invalidateAll();
  }

}
