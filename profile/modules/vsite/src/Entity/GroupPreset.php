<?php

namespace Drupal\vsite\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\vsite\Config\VsiteStorageDefinition;

/**
 * Group Preset Config Entity.
 *
 * @ConfigEntityType(
 *   id = "group_preset",
 *   label = @Translation("Group Preset"),
 *   label_singular = @Translation("group preset"),
 *   label_plural = @Translation("group presets"),
 *   label_count = @PluralTranslation(
 *     singular = "@count group preset",
 *     plural = "@count group presets"
 *   ),
 *   handlers = {
 *     "form" = {
 *       "add" = "Drupal\vsite\Entity\Form\GroupPresetForm",
 *       "edit" = "Drupal\vsite\Entity\Form\GroupPresetForm",
 *       "delete" = "Drupal\vsite\Entity\Form\GroupPresetDeleteForm"
 *     },
 *     "list_builder" = "Drupal\vsite\Entity\Controller\GroupPresetListBuilder",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider"
 *     }
 *   },
 *   admin_permission = "administer group preset",
 *   config_prefix = "type",
 *   static_cache = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   links = {
 *     "add-form" = "/admin/group/presets/add",
 *     "collection" = "/admin/group/presets",
 *     "delete-form" = "/admin/group/presets/manage/{group_preset}/delete",
 *     "edit-form" = "/admin/group/presets/manage/{group_preset}",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *     "applicableTo",
 *     "creationTasks"
 *   }
 * )
 */
class GroupPreset extends ConfigEntityBase implements GroupPresetInterface {

  protected $id;

  protected $label;

  protected $description;

  protected $applicableTo;

  protected $creationTasks;

  /**
   * {@inheritdoc}
   */
  public function getPresetStorage(): StorageInterface {
    $collection = 'preset:' . $this->id();
    /** @var \Drupal\Core\Config\StorageInterface $storage */
    $storage = \Drupal::service('config.storage');
    return $storage->createCollection($collection);
  }

  /**
   * {@inheritdoc}
   */
  public function getCreationTasks() {
    return $this->creationTasks;
  }

  /**
   * {@inheritdoc}
   */
  public function saveConfig($form, FormStateInterface $formState) {

    /** @var \Drupal\vsite\Config\HierarchicalStorageInterface $storage */
    $storage = \Drupal::service('config.storage');

    $storage->overrideWriteLevel(VsiteStorageDefinition::PRESET_STORAGE);
    $formState->getFormObject()->submitForm($form, $formState);
    $storage->clearWriteOverride();
  }

}
