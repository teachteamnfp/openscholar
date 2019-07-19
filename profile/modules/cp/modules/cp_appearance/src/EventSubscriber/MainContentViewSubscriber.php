<?php

namespace Drupal\cp_appearance\EventSubscriber;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheableResponseInterface;
use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\Core\EventSubscriber\MainContentViewSubscriber as CoreMainContentViewSubscriber;

/**
 * View subscriber rendering main content render arrays into responses.
 *
 * This is similar to \Drupal\Core\EventSubscriber\MainContentViewSubscriber.
 * Difference is that, it alters the cache tags depending on whether a vsite is
 * active.
 * Note that is responsible for handling HTML responses, and
 * \Drupal\Core\EventSubscriber\MainContentViewSubscriber will never be called.
 *
 * @see \Drupal\Core\EventSubscriber\MainContentViewSubscriber
 */
class MainContentViewSubscriber implements EventSubscriberInterface {

  /**
   * The class resolver service.
   *
   * @var \Drupal\Core\Controller\ControllerResolverInterface
   */
  protected $classResolver;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The available main content renderer services, keyed per format.
   *
   * @var array
   */
  protected $mainContentRenderers;

  /**
   * Vsite context manager.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * Creates a new MainContentViewSubscriber object.
   *
   * @param \Drupal\Core\DependencyInjection\ClassResolverInterface $class_resolver
   *   The class resolver service.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param array $main_content_renderers
   *   The available main content renderer service IDs, keyed by format.
   * @param \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager
   *   Vsite context manager.
   */
  public function __construct(ClassResolverInterface $class_resolver, RouteMatchInterface $route_match, array $main_content_renderers, VsiteContextManagerInterface $vsite_context_manager) {
    $this->classResolver = $class_resolver;
    $this->routeMatch = $route_match;
    $this->mainContentRenderers = $main_content_renderers;
    $this->vsiteContextManager = $vsite_context_manager;
  }

  /**
   * Sets a response given a (main content) render array.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent $event
   *   The event to process.
   */
  public function onViewRenderArray(GetResponseForControllerResultEvent $event): void {
    $request = $event->getRequest();
    $result = $event->getControllerResult();

    // Render the controller result into a response if it's a render array.
    if (\is_array($result) &&
      ($request->query->has(CoreMainContentViewSubscriber::WRAPPER_FORMAT) || $request->getRequestFormat() === 'html')) {
      $wrapper = $request->query->get(CoreMainContentViewSubscriber::WRAPPER_FORMAT, 'html');

      // Fall back to HTML if the requested wrapper envelope is not available.
      $wrapper = isset($this->mainContentRenderers[$wrapper]) ? $wrapper : 'html';

      $renderer = $this->classResolver->getInstanceFromDefinition($this->mainContentRenderers[$wrapper]);
      $response = $renderer->renderResponse($result, $request, $this->routeMatch);

      /** @var \Drupal\group\Entity\GroupInterface|null $vsite */
      $vsite = $this->vsiteContextManager->getActiveVsite();

      // Alters cache tags if response is for an active vsite.
      if ($vsite) {
        /** @var \Drupal\Core\Cache\CacheableMetadata $cached_metadata */
        $cached_metadata = $response->getCacheableMetadata();
        /** @var array $existing_cache_tags */
        $existing_cache_tags = $cached_metadata->getCacheTags();
        $rendered_tag_position = array_search('rendered', $existing_cache_tags, TRUE);

        if ($rendered_tag_position !== FALSE) {
          unset($existing_cache_tags[$rendered_tag_position]);
        }

        $existing_cache_tags[] = "rendered:vsite:{$vsite->id()}";
        $cached_metadata->setCacheTags($existing_cache_tags);
      }

      // The main content render array is rendered into a different Response
      // object, depending on the specified wrapper format.
      if ($response instanceof CacheableResponseInterface) {
        $main_content_view_subscriber_cacheability = (new CacheableMetadata())->setCacheContexts(['url.query_args:' . CoreMainContentViewSubscriber::WRAPPER_FORMAT]);
        $response->addCacheableDependency($main_content_view_subscriber_cacheability);
      }
      $event->setResponse($response);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Making sure that this is called before
    // \Drupal\Core\EventSubscriber\MainContentViewSubscriber::onViewRenderArray.
    $events[KernelEvents::VIEW][] = ['onViewRenderArray', 10];

    return $events;
  }

}
