<?php

namespace Drupal\cp_appearance\EventSubscriber;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\cp_appearance\Entity\CustomTheme;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscriber for invalidating cache tags when theme setting is saved.
 */
class ThemeConfigSubscriber implements EventSubscriberInterface {

  /**
   * The cache tags invalidator.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheTagsInvalidator;

  /**
   * Vsite context manager.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * Creates a new ThemeConfigSubscriber object.
   *
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cache_tags_invalidator
   *   The cache tags invalidator.
   * @param \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager
   *   Vsite context manager.
   */
  public function __construct(CacheTagsInvalidatorInterface $cache_tags_invalidator, VsiteContextManagerInterface $vsite_context_manager) {
    $this->cacheTagsInvalidator = $cache_tags_invalidator;
    $this->vsiteContextManager = $vsite_context_manager;
  }

  /**
   * Invalidates cache tags when theme setting is saved.
   *
   * @param \Drupal\Core\Config\ConfigCrudEvent $event
   *   The Event to process.
   */
  public function onSave(ConfigCrudEvent $event): void {
    /** @var \Drupal\group\Entity\GroupInterface|null $vsite */
    $vsite = $this->vsiteContextManager->getActiveVsite();
    $saved_config = $event->getConfig();

    if ($vsite &&
      $saved_config->getName() === 'system.theme' &&
      strpos($saved_config->get('default'), CustomTheme::CUSTOM_THEME_ID_PREFIX) !== FALSE) {
      $this->cacheTagsInvalidator->invalidateTags(["rendered:vsite:{$vsite->id()}"]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ConfigEvents::SAVE][] = ['onSave'];
    return $events;
  }

}
