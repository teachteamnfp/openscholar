<?php

namespace Drupal\os_widgets\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Config\Entity\ConfigEntityType;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\os_widgets\LayoutContextInterface;

/**
 * @ConfigEntityType(
 *   id = "layout_context",
 *   label = @Translation("Layout Context", context = "Layout context entity type"),
 *   label_collection = @Translation("Layout contexts", context = "Layout context entity type"),
 *   label_singular = @Translation("layout context", context = "Layout context entity type"),
 *   label_plural = @Translation("layout contexts", context = "Layout context entity type"),
 *   label_count = @PluralTranslation(
 *     singular = "@count layout context",
 *     plural = "@count layout contexts",
 *     context = "Layout context entity type",
 *   ),
 *   admin_permission = "administer layout contexts",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "status" = "status"
 *   },
 *   handlers = {
 *     "route_provider" = {
 *       "layout_context" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider"
 *     },
 *     "form" = {
 *       "add" = "Drupal\os_widgets\Form\LayoutContextForm",
 *       "edit" = "Drupal\os_widgets\Form\LayoutContextForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     },
 *     "list_builder" = "Drupal\os_widgets\LayoutContextListBuilder",
 *   },
 *   fieldable = true,
 *   links = {
 *     "add-form" = "/admin/structure/layout-context/add",
 *     "edit-form" = "/admin/structure/layout-context/manage/{layout_context}",
 *     "delete-form" = "/admin/structure/layout-context/manage/{layout_context}/delete",
 *     "collection" = "/admin/structure/layout-context",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "module",
 *     "description"
 *   }
 * )
 */
class LayoutContext extends ConfigEntityBase implements LayoutContextInterface {

  protected $id;

  protected $label;

  protected $description;

  protected $status;

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

}