<?php

namespace Drupal\os_widgets\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Config\Entity\ConfigEntityType;
use Drupal\Core\Entity\Display\EntityDisplayInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\layout_builder\Entity\LayoutEntityDisplayInterface;
use Drupal\layout_builder\LayoutBuilderEnabledInterface;
use Drupal\layout_builder\LayoutEntityHelperTrait;
use Drupal\layout_builder\SectionStorage\SectionStorageTrait;
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
 *     "description",
 *     "activate_on"
 *   }
 * )
 */
class LayoutContext extends ConfigEntityBase implements LayoutContextInterface {

  use SectionStorageTrait;
  use LayoutEntityHelperTrait;

  protected $id;

  protected $label;

  protected $description;

  protected $status;

  protected $activationRules;

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

  /**
   * {@inheritdoc}
   */
  public function getActivationRules() {
    return $this->activationRules;
  }

  /**
   * {@inheritdoc}
   */
  public function isOverridable() {
    return $this->isLayoutBuilderEnabled() && $this->getThirdPartySetting('layout_builder', 'allow_custom', FALSE);
  }

  /**
   * {@inheritdoc}
   */
  public function setOverridable($overridable = TRUE) {
    $this->setThirdPartySetting('layout_builder', 'allow_custom', $overridable);
    // Enable Layout Builder if it's not already enabled and overriding.
    if ($overridable && !$this->isLayoutBuilderEnabled()) {
      $this->enableLayoutBuilder();
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isLayoutBuilderEnabled() {
    // To prevent infinite recursion, Layout Builder must not be enabled for the
    // '_custom' view mode that is used for on-the-fly rendering of fields in
    // isolation from the entity.
    return (bool) $this->getThirdPartySetting('layout_builder', 'enabled');
  }

  /**
   * {@inheritdoc}
   */
  public function enableLayoutBuilder() {
    $this->setThirdPartySetting('layout_builder', 'enabled', TRUE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function disableLayoutBuilder() {
    $this->setOverridable(FALSE);
    $this->setThirdPartySetting('layout_builder', 'enabled', FALSE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSections() {
    return $this->getThirdPartySetting('layout_builder', 'sections', []);
  }

  /**
   * {@inheritdoc}
   */
  protected function setSections(array $sections) {
    // Third-party settings must be completely unset instead of stored as an
    // empty array.
    if (!$sections) {
      $this->unsetThirdPartySetting('layout_builder', 'sections');
    }
    else {
      $this->setThirdPartySetting('layout_builder', 'sections', array_values($sections));
    }
    return $this;
  }

}
