<?php

namespace Drupal\os_redirect\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\redirect\EventSubscriber\RedirectRequestSubscriber;
use Drupal\redirect\Exception\RedirectLoopException;
use Drupal\redirect\RedirectChecker;
use Drupal\redirect\RedirectRepository;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\RequestContext;

/**
 * OS Redirect subscriber for controller requests.
 */
class OsRedirectRequestSubscriber extends RedirectRequestSubscriber {

  use StringTranslationTrait;
  /**
   * Vsite Context Manager.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManager
   */
  protected $vsiteContextManager;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  protected $logger;

  /**
   * Account.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * Messenger.
   *
   * @var \Drupal\Core\Messenger\Messenger
   */
  protected $messenger;

  /**
   * {@inheritdoc}
   */
  public function __construct(RedirectRepository $redirect_repository, LanguageManagerInterface $language_manager, ConfigFactoryInterface $config, AliasManagerInterface $alias_manager, ModuleHandlerInterface $module_handler, EntityManagerInterface $entity_manager, RedirectChecker $checker, RequestContext $context, InboundPathProcessorInterface $path_processor, VsiteContextManagerInterface $vsite_context_manager, LoggerChannelFactoryInterface $logger, AccountProxy $current_user, Messenger $messenger) {
    $this->vsiteContextManager = $vsite_context_manager;
    $this->logger = $logger;
    $this->currentUser = $current_user;
    $this->messenger = $messenger;
    parent::__construct($redirect_repository, $language_manager, $config, $alias_manager, $module_handler, $entity_manager, $checker, $context, $path_processor);
  }

  /**
   * Handles the vsite redirect if any found.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The event to process.
   */
  public function onKernelRequestCheckRedirect(GetResponseEvent $event) {
    $request = clone $event->getRequest();

    if (!$this->checker->canRedirect($request)) {
      return;
    }

    // Get URL info and process it to be used for hash generation.
    parse_str($request->getQueryString(), $request_query);

    if (strpos($request->getPathInfo(), '/system/files/') === 0 && !$request->query->has('file')) {
      // Private files paths are split by the inbound path processor and the
      // relative file path is moved to the 'file' query string parameter. This
      // is because the route system does not allow an arbitrary amount of
      // parameters. We preserve the path as is returned by the request object.
      // @see \Drupal\system\PathProcessor\PathProcessorFiles::processInbound()
      $path = $request->getPathInfo();
    }
    else {
      // Do the inbound processing so that for example language prefixes are
      // removed.
      $path = $this->pathProcessor->processInbound($request->getPathInfo(), $request);
    }
    $path = trim($path, '/');

    $this->context->fromRequest($request);

    try {
      if ($purl = $this->vsiteContextManager->getActivePurl()) {
        $redirect = $this->redirectRepository->findMatchingRedirect($purl . '/' . $path, $request_query, $this->languageManager->getCurrentLanguage()->getId());
      }
    }
    catch (RedirectLoopException $e) {
      $this->logger->get('redirect')->warning($e->getMessage());
      $response = new Response();
      $response->setStatusCode(503);
      $response->setContent('Service unavailable');
      $event->setResponse($response);
      return;
    }

    if (!empty($redirect)) {
      if ($this->currentUser->hasPermission('ignore redirects')) {
        $this->messenger->addWarning($this->t('You are redirected to: @uri', ['@uri' => $redirect->getRedirectUrl()->getUri()]));
        return;
      }
      // Handle internal path.
      $url = $redirect->getRedirectUrl();
      if ($this->config->get('passthrough_querystring')) {
        $url->setOption('query', (array) $url->getOption('query') + $request_query);
      }
      $headers = [
        'X-Redirect-ID' => $redirect->id(),
      ];
      $response = new TrustedRedirectResponse($url->setAbsolute()->toString(), $redirect->getStatusCode(), $headers);
      $response->addCacheableDependency($redirect);
      $event->setResponse($response);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // This needs to run before RouterListener::onKernelRequest(), which has
    // a priority of 32. Otherwise, that aborts the request if no matching
    // route is found.
    $events[KernelEvents::REQUEST][] = ['onKernelRequestCheckRedirect', 33];
    return $events;
  }

}
