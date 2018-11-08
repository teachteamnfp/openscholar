<?php
/**
 * Created by PhpStorm.
 * User: New User
 * Date: 11/8/2018
 * Time: 2:53 PM
 */

namespace Drupal\vsite_privacy;


use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\vsite\VsiteEvents;
use Drupal\vsite_privacy\Plugin\VsitePrivacyLevelManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class VsitePrivacyAccessCheck implements EventSubscriberInterface {

  /** @var ConfigFactoryInterface */
  protected $configFactory;

  /** @var VsitePrivacyLevelManagerInterface */
  protected $vsitePrivacyLevelManager;

  /** @var bool */
  protected $checked = false;

  public function __construct (ConfigFactoryInterface $configFactory, VsitePrivacyLevelManagerInterface $vsitePrivacyLevelManager) {
    $this->configFactory = $configFactory;
    $this->vsitePrivacyLevelManager = $vsitePrivacyLevelManager;
  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents () {
    $events = [];
    $events[VsiteEvents::VSITE_ACTIVATED][] = ['onVsiteActivated', 0];
    return $events;
  }

  public function onVsiteActivated() {
    if ($this->checked) return;

    $this->checked = true;
    $privacy = $this->configFactory->get ('vsite.privacy');
    $level = $privacy->get ('level');
    if (!isset($level)) {
      $level = 'public';
    }
    if (!$this->vsitePrivacyLevelManager->checkAccessForPlugin (\Drupal::currentUser(), $level)) {
      throw new AccessDeniedHttpException();
    }
  }
}