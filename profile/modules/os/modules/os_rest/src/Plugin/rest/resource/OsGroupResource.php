<?php

namespace Drupal\os_rest\Plugin\rest\resource;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\vsite\Config\HierarchicalStorageInterface;
use Drupal\vsite\Config\VsiteStorageDefinition;
use Drupal\Core\Entity\EntityInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Overrides the standard entity resource interface.
 *
 * Necessary to set config for a group, and to return the batch id.
 */
class OsGroupResource extends OsEntityResource {


  /**
   * Hierarchical storage.
   *
   * @var HierarchicalStorageInterface
   */
  protected $hierarchicalStorage;

  /**
   * Constructs a Drupal\rest\Plugin\rest\resource\EntityResource object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $link_relation_type_manager
   *   The link relation type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, $serializer_formats, LoggerInterface $logger, ConfigFactoryInterface $config_factory, PluginManagerInterface $link_relation_type_manager, HierarchicalStorageInterface $hierarchicalStorage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $serializer_formats, $logger, $config_factory, $link_relation_type_manager);
    $this->entityType = $entity_type_manager->getDefinition($plugin_definition['entity_type']);
    $this->configFactory = $config_factory;
    $this->linkRelationTypeManager = $link_relation_type_manager;
    $this->hierarchicalStorage = $hierarchicalStorage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest'),
      $container->get('config.factory'),
      $container->get('plugin.manager.link_relation_type'),
      $container->get('hierarchical.storage')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function get(EntityInterface $entity) {
    return parent::get($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function post(EntityInterface $entity = NULL) {

    // Values on the entity get destroyed when the entity is saved.
    // We look for them here to preserve them.
    if (!is_null($entity) && isset($entity->_data_extra['theme'])) {
      $theme = $entity->_data_extra['theme'];
    }

    $response = parent::post($entity);

    // Set the theme.
    if (isset($theme)) {
      $this->hierarchicalStorage->clearWriteOverride();
      $storage = $this->hierarchicalStorage->createCollection('vsite:' . $entity->id());
      $this->hierarchicalStorage->addStorage($storage, VsiteStorageDefinition::VSITE_STORAGE);
      $config = \Drupal::configFactory()->getEditable('system.theme');
      $config->set('default', $theme);
      $config->save();
    }

    // Send the batch ID as a header so the client can handle it properly.
    if ($batch = batch_get()) {
      $response->headers->set('X-Drupal-Batch-Id', $batch['id']);
    }

    return $response;
  }

}
