<?php

namespace Drupal\os_widgets\EventSubscriber;

use Drupal\Core\Render\PageDisplayVariantSelectionEvent;
use Drupal\Core\Render\RenderEvents;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Session\AccountInterface;

/**
<<<<<<< HEAD
 * Copy of core's block_place event subscriber that uses the group permission.
=======
 * Event Subscriber to set the plugin id for block place.
>>>>>>> 8.x-1.x-dev
 *
 * @see \Drupal\block_place\Plugin\DisplayVariant\PlaceBlockPageVariant
 */
class BlockPlaceEventSubscriber implements EventSubscriberInterface {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The vsite context manager.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * Constructs a \Drupal\block_place\EventSubscriber\BlockPlaceEventSubscriber object.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack used to retrieve the current request.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user.
   * @param \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsiteContextManager
<<<<<<< HEAD
   *   The vsite context manager.
=======
   *   Vsite context manager.
>>>>>>> 8.x-1.x-dev
   */
  public function __construct(RequestStack $request_stack, AccountInterface $account, VsiteContextManagerInterface $vsiteContextManager) {
    $this->requestStack = $request_stack;
    $this->account = $account;
    $this->vsiteContextManager = $vsiteContextManager;
  }

  /**
   * Selects the block place override of the block page display variant.
   *
   * @param \Drupal\Core\Render\PageDisplayVariantSelectionEvent $event
   *   The event to process.
   */
  public function onBlockPageDisplayVariantSelected(PageDisplayVariantSelectionEvent $event) {
    if ($event->getPluginId() === 'block_page' && $group = $this->vsiteContextManager->getActiveVsite()) {
      if ($membership = $group->getMember($this->account)) {
        if ($this->requestStack->getCurrentRequest()->query->has('block-place') && $membership->hasPermission('manage layout')) {
          $event->setPluginId('block_place_page');
        }
      }
      $event->addCacheContexts(['user.permissions', 'url.query_args']);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Set a very low priority, so that it runs last.
    $events[RenderEvents::SELECT_PAGE_DISPLAY_VARIANT][] = ['onBlockPageDisplayVariantSelected', -1000];
    return $events;
  }

}
