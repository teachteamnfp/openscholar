<?php

namespace Drupal\os_publications\Plugin\CitationDistribution;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\repec\RepecInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Citation Distribute GoogleScholar service.
 *
 * @CitationDistribute(
 *   id = "citation_distribute_repec",
 *   title = @Translation("RePEc citation distribute service."),
 *   name = "RePEc",
 *   href = "https://repec.org",
 *   description = "Searchable index of citations in RePEc",
 * )
 */
class CitationDistributeRepec extends PluginBase implements CitationDistributionInterface, ContainerFactoryPluginInterface {

  /**
   * Repec service.
   *
   * @var \Drupal\repec\RepecInterface
   */
  protected $repec;

  /**
   * CitationDistributeRepec constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\repec\RepecInterface $repec
   *   Repec service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RepecInterface $repec) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->repec = $repec;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('repec'));
  }

  /**
   * {@inheritdoc}
   */
  public function render($id): array {
    // Repec does not renders anything.
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function mapMetadata($id): array {
    // The mapping is handled by the repec module.
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function save($id): bool {
    return TRUE;
  }

}
