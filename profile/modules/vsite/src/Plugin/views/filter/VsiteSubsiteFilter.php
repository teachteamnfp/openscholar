<?php

namespace Drupal\vsite\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Drupal\vsite\Plugin\VsiteContextManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Filter a View for any subsite that's child of the current vsite.
 *
 * How to use:
 * 1. Create View for groups entity
 * 2. Advanced > Relationships, Add Relationship to "field_parent_site: Group"
 * 3. Set this Relationship to required (INNER JOIN)
 * 4. Add this filter. There are no settings to configure.
 * 5. Done.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("vsite_subsite_filter")
 */
class VsiteSubsiteFilter extends FilterPluginBase {

  /**
   * Vsite Context Manager.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManager
   */
  private $vsiteContextManager;

  /**
   * Constructs a new LanguageFilter instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\vsite\Plugin\VsiteContextManager $vsite_context_manager
   *   Vsite context manager.
   */
  public function __construct(array $configuration, string $plugin_id, $plugin_definition, VsiteContextManager $vsite_context_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

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
      $container->get('vsite.context_manager')
    );
  }

  /**
   * Alter the query.
   */
  public function query() {
    /** @var \Drupal\group\Entity\GroupInterface $group */
    if ($group = $this->vsiteContextManager->getActiveVsite()) {
      $this->query->addWhere('vsite', 'field_parent_site_target_id', $group->id());
    }
  }

}
