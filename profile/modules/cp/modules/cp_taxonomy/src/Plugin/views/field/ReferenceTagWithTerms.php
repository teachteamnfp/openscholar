<?php

namespace Drupal\cp_taxonomy\Plugin\views\field;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;
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
  private $vsiteContextManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, VsiteContextManagerInterface $vsite_context_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->vsiteContextManager = $vsite_context_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('vsite.context_manager')
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
    $reference = $this->getEntity($values);
    $config = $this->configFactory->get('cp_taxonomy.settings');
    $display_term_under_content_teaser_types = $config->get('display_term_under_content_teaser_types');
    $build = [];
    if (is_null($display_term_under_content_teaser_types) || in_array($reference->getEntityType()->id() . ':*', $display_term_under_content_teaser_types)) {
      return $reference->field_taxonomy_terms->view(['label' => 'hidden']);
    }
    if (!empty($reference->field_taxonomy_terms->referencedEntities())) {
      $group = $this->vsiteContextManager->getActiveVsite();
      $build['#cache']['tags'][] = 'entity-with-taxonomy-terms:' . $group->id();
    }
    return $build;
  }

}
