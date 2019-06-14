<?php

namespace Drupal\bibcite_preview\Access;

use Drupal\bibcite_entity\Entity\ReferenceInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Determines access to reference previews.
 *
 * @ingroup reference_access
 */
class BibciteReferencePreviewAccessCheck implements AccessInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a BibciteReferencePreviewAccessCheck object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Checks access to the reference preview page.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   * @param \Drupal\bibcite_entity\Entity\ReferenceInterface $bibcite_reference_preview
   *   The reference that is being previewed.
   *
   * @return string
   *   A \Drupal\Core\Access\AccessInterface constant value.
   */
  public function access(AccountInterface $account, ReferenceInterface $bibcite_reference_preview) {
    $access_control_handler = $this->entityTypeManager->getAccessControlHandler('bibcite_reference');
    // If checking whether a reference of a particular type may be created.
    if ($bibcite_reference_preview->isNew()) {
      return $access_control_handler->createAccess($bibcite_reference_preview->bundle(), $account, [], TRUE);
    }
    else {
      return $bibcite_reference_preview->access('update', $account, TRUE);
    }
  }

}
