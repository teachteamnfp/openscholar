<?php

namespace Drupal\vsite\Plugin;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\TypedData\TypedDataManager;
use Drupal\Core\Validation\ConstraintManager;
use Drupal\vsite\Event\VsiteActivatedEvent;
use Drupal\vsite\VsiteEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class VsiteTypedDataManager.
 *
 * @package Drupal\vsite\Plugin
 */
class VsiteTypedDataManager extends TypedDataManager implements EventSubscriberInterface {

  /**
   * Store the active Vsite.
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $activeVsite;

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler, ClassResolverInterface $class_resolver) {
    parent::__construct($namespaces, $cache_backend, $module_handler, $class_resolver);
    $this->setValidationConstraintManager(new ConstraintManager($namespaces, $cache_backend, $module_handler));
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return array_merge(parent::getCacheContexts(), ['vsite']);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[VsiteEvents::VSITE_ACTIVATED][] = ['onVsiteActivated', 1];
    return $events;
  }

  /**
   * Change the cache key to include the vsite.
   *
   * @param \Drupal\vsite\Event\VsiteActivatedEvent $vsite
   *   The activation event.
   */
  public function onVsiteActivated(VsiteActivatedEvent $vsite) {
    if (strpos($this->cacheKey, 'vsite') === FALSE) {
      $this->activeVsite = $vsite->getGroup();
      $this->cacheKey .= '&vsite=' . $this->activeVsite->id();
      $this->definitions = NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function findDefinitions() {
    $definitions = parent::findDefinitions();

    if ($this->activeVsite != NULL) {
      $template = [
        'class' => 'Drupal\Core\Entity\Plugin\DataType\EntityAdapter',
        'label' => '',
        'constraints' => [
          'EntityChanged' => NULL,
          'EntityUntranslatableFields' => NULL,
        ],
        'definition_class' => '\Drupal\Core\Entity\TypedData\EntityDataDefinition',
        'list_class' => '\Drupal\Core\TypedData\Plugin\DataType\ItemList',
        'list_definition_class' => '\Drupal\Core\TypedData\ListDataDefinition',
        'unwrap_for_canonical_representation' => TRUE,
        'id' => 'entity',
        'description' => t('All kind of entities, e.g. nodes, comments or users.'),
        'deriver' => '\Drupal\Core\Entity\Plugin\DataType\Deriver\EntityDeriver',
        'provider' => 'core',
      ];
      $configs = \Drupal::configFactory()->listAll('taxonomy.vocabulary');

      foreach ($configs as $config_name) {
        $definitionName = str_replace('taxonomy.vocabulary.', 'entity:taxonomy_term:', $config_name);
        $definitions[$definitionName] = [
          'label' => $config_name,
        ] + $template;
      }
    }

    return $definitions;
  }

}
