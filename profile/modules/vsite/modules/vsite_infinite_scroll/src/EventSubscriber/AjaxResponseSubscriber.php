<?php

namespace Drupal\vsite_infinite_scroll\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\views\Ajax\ViewAjaxResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Response subscriber to handle AJAX responses.
 */
class AjaxResponseSubscriber implements EventSubscriberInterface {

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory Interface.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * Alter the views AJAX response commands only for the infinite pager.
   *
   * @param array $commands
   *   An array of commands to alter.
   */
  protected function alterPaginationCommands(array &$commands) {
    foreach ($commands as $delta => &$command) {
      // Substitute the 'replace' method without our custom jQuery method which
      // will allow views content to be injected one after the other.
      if (isset($command['method']) && $command['method'] === 'replaceWith') {
        $command['method'] = 'infiniteScrollInsertView';
      }
      // Stop the view from scrolling to the top of the page.
      if ($command['command'] === 'viewsScrollTop') {
        unset($commands[$delta]);
      }
    }
  }

  /**
   * Renders the ajax commands right before preparing the result.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   The response event, which contains the possible AjaxResponse object.
   */
  public function onResponse(FilterResponseEvent $event) {
    $response = $event->getResponse();

    // Only alter views ajax responses.
    if (!($response instanceof ViewAjaxResponse)) {
      return;
    }

    $view = $response->getView();
    // Only alter commands if the user has selected our pager and it attempting
    // to move beyond page 0.
    if ($view->getPager()->getPluginId() !== 'vsite_infinite_scroll' || $view->getCurrentPage() === 0) {
      return;
    }
    $config = $this->configFactory->get('vsite_infinite_scroll.settings');
    $long_list_content_pagination = $config->get('long_list_content_pagination');
    // If pager is set, no need to alter commands.
    if ($long_list_content_pagination == 'pager') {
      return;
    }

    $commands = &$response->getCommands();
    $this->alterPaginationCommands($commands);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [KernelEvents::RESPONSE => [['onResponse']]];
  }

}
