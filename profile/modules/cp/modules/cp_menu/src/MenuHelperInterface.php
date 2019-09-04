<?php

namespace Drupal\cp_menu;

use Drupal\bibcite_entity\Entity\ReferenceInterface;
use Drupal\group\Entity\GroupInterface;

/**
 * MenuHelperInterface.
 */
interface MenuHelperInterface {

  /**
   * Creates new vsite specific menus and returns the primary menu tree.
   *
   * @param \Drupal\group\Entity\GroupInterface $vsite
   *   The vsite in context.
   *
   * @return array
   *   The primary menu tree.
   */
  public function createVsiteMenus(GroupInterface $vsite) : array;

  /**
   * Creates new vsite specific menu with limited/no links.
   *
   * @param \Drupal\group\Entity\GroupInterface $vsite
   *   The vsite in context.
   * @param bool $secondary
   *   If secondary menu's reset button is clicked.
   */
  public function resetVsiteMenus(GroupInterface $vsite, $secondary = FALSE) : void;

  /**
   * Invalidates Menu block caches when changes are made.
   *
   * @param \Drupal\group\Entity\GroupInterface $vsite
   *   Current vsite.
   * @param mixed $ids
   *   Single id or array of menu ids.
   * @param bool $buildForm
   *   If called from main buildForm changes.
   */
  public function invalidateBlockCache(GroupInterface $vsite, $ids, $buildForm = FALSE) : void;

  /**
   * Get Menu Link and its data for publication edit page.
   *
   * @param \Drupal\bibcite_entity\Entity\ReferenceInterface $reference
   *   The publication entity in context.
   * @param \Drupal\group\Entity\GroupInterface $vsite
   *   Current vsite.
   *
   * @return array
   *   Default menu link values for publication entity.
   */
  public function getMenuLinkDefaults(ReferenceInterface $reference, GroupInterface $vsite): array;

  /**
   * Performs alterations on menu link associated with a publication.
   *
   * @param array $values
   *   Reference submit form values.
   * @param \Drupal\bibcite_entity\Entity\ReferenceInterface $reference
   *   The reference entity in context.
   * @param \Drupal\group\Entity\GroupInterface $vsite
   *   Current vsite.
   */
  public function publicationInFormMenuAlterations(array $values, ReferenceInterface $reference, GroupInterface $vsite): void;

}
