<?php

namespace Drupal\vsite_privacy;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\group\Entity\Group;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;

/**
 * VsitePrivacyHelper service.
 */
class VsitePrivacyHelper implements VsitePrivacyHelperInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The vsite.context_manager service.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * Constructs a VsitePrivacyHelper object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager
   *   The vsite.context_manager service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, VsiteContextManagerInterface $vsite_context_manager) {
    $this->configFactory = $config_factory;
    $this->vsiteContextManager = $vsite_context_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function updateRobotstxtDirectives(Group $group) : void {
    /** @var \Drupal\Core\Field\FieldItemList $privacy_list */
    $privacy_list = $group->get('field_privacy_level');
    $privacy = $privacy_list->getValue()[0]['value'];

    $purl = $this->vsiteContextManager->getActivePurl();
    if (empty($purl)) {
      return;
    }
    $gid = $group->id();

    /** @var \Drupal\Core\Config\ImmutableConfig $os_publication_settings */
    $os_publication_settings = $this->configFactory->getEditable('vsite_privacy.settings');
    $directives = $os_publication_settings->get('robotstxt_directives');

    // Disallows robots in sites with privacy settings not public.
    $disallow_purl = ($privacy != 'public');
    if ($disallow_purl) {
      $directives[$gid] = "Disallow: /$purl/";
    }
    else {
      // Undoes any previous private vsite settings (see above).
      // Unsets any Disallow directives for this vsite.
      if (isset($directives[$gid])) {
        unset($directives[$gid]);
      }
    }
    if (empty($directives)) {
      return;
    }
    $os_publication_settings
      ->set('robotstxt_directives', $directives)
      ->save();
  }

  /**
   * Get Robotstxt directives.
   */
  public function getRobotstxtDirectives(): array {
    /** @var \Drupal\Core\Config\ImmutableConfig $os_publication_settings */
    $os_publication_settings = $this->configFactory->get('vsite_privacy.settings');
    $robots_settings = $os_publication_settings->get('robotstxt_directives');
    if (empty($robots_settings)) {
      return [];
    }
    return $robots_settings;
  }

}
