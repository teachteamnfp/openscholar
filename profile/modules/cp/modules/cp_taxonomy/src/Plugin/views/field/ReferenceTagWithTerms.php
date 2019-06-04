<?php

namespace Drupal\cp_taxonomy\Plugin\views\field;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Reference Tag with terms.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("cp_taxonomy_field_taxonomy_terms")
 */
class ReferenceTagWithTerms extends FieldPluginBase {

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Leave empty to do nothing.
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $reference = $values->_entity;
    $config = $this->configFactory->get('cp_taxonomy.settings');
    $display_term_under_content_teaser_types = $config->get('display_term_under_content_teaser_types');
    if (is_null($display_term_under_content_teaser_types) || in_array($reference->getEntityType()->id() . ':' . $reference->bundle(), $display_term_under_content_teaser_types)) {
      return $reference->field_taxonomy_terms->view(['label' => 'hidden']);
    }
    return '';
  }

}
