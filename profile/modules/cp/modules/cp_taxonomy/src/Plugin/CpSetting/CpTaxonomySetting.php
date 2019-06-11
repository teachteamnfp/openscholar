<?php

namespace Drupal\cp_taxonomy\Plugin\CpSetting;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\cp_settings\CpSettingBase;
use Drupal\cp_taxonomy\CpTaxonomyHelperInterface;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * CP taxonomy setting.
 *
 * @CpSetting(
 *   id = "cp_taxonomy_setting",
 *   title = @Translation("OS Taxonomy"),
 *   group = {
 *    "id" = "taxonomy",
 *    "title" = @Translation("Taxonomy"),
 *    "parent" = "cp.settings.global"
 *   }
 * )
 */
class CpTaxonomySetting extends CpSettingBase {

  protected $cpTaxonomyHelper;

  /**
   * Creates a new CpSettingBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager
   *   Vsite context manager.
   * @param \Drupal\cp_taxonomy\CpTaxonomyHelperInterface $cp_taxonomy_helper
   *   Cp Taxonomy Helper.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, VsiteContextManagerInterface $vsite_context_manager, CpTaxonomyHelperInterface $cp_taxonomy_helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $vsite_context_manager);
    $this->cpTaxonomyHelper = $cp_taxonomy_helper;
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
      $container->get('cp.taxonomy.helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames(): array {
    return ['cp_taxonomy.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array &$form, ConfigFactoryInterface $configFactory) {
    $config = $configFactory->get('cp_taxonomy.settings');
    $form['display_term_under_content'] = [
      '#type' => 'checkbox',
      '#prefix' => '<label class="cp-taxonomy-label-heading">Choose where "See Also" terms display</label>',
      '#title' => $this->t("Under a page's main content area"),
      '#default_value' => $config->get('display_term_under_content'),
    ];
    $default_value = $config->get('display_term_under_content_teaser_types');
    if (empty($default_value)) {
      $default_value = array_keys($this->cpTaxonomyHelper->getSelectableBundles());
    }
    $form['display_term_under_content_teaser_types'] = [
      '#type' => 'checkboxes',
      '#prefix' => '<label>Under these content types when displayed in a list:</label>',
      '#options' => $this->cpTaxonomyHelper->getSelectableBundles(),
      '#default_value' => $default_value,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(FormStateInterface $formState, ConfigFactoryInterface $configFactory) {
    $config = $configFactory->getEditable('cp_taxonomy.settings');
    $config->set('display_term_under_content', $formState->getValue('display_term_under_content'));
    $config->set('display_term_under_content_teaser_types', array_filter($formState->getValue('display_term_under_content_teaser_types')));
    $config->save(TRUE);
  }

}
