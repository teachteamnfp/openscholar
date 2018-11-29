<?php

namespace Drupal\group_entity\Plugin\GroupContentEnabler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\group\Plugin\GroupContentEnablerBase;
use Drupal\group\Entity\GroupInterface;

/**
 * Provides a content enabler for nodes.
 *
 * @GroupContentEnabler(
 *   id = "group_entity",
 *   label = @Translation("Group Entity"),
 *   description = @Translation("Adds entities to groups both publicly and privately."),
 *   entity_access = TRUE,
 *   reference_label = @Translation("Title"),
 *   reference_description = @Translation("The title of the entity to add to the group"),
 *   deriver = "Drupal\group_entity\Plugin\GroupContentEnabler\GroupEntityDeriver",
 *   enforced = TRUE
 * )
 */
class GroupEntityEnabler extends GroupContentEnablerBase {

  /**
   * {@inheritdoc}
   */
  public function getGroupOperations(GroupInterface $group) {
    $account = \Drupal::currentUser();
    $plugin_id = $this->getPluginId();
    $type = $this->getEntityTypeId();
    $bundle = $this->getEntityBundle();
    $operations = [];

    if ($group->hasPermission("create $plugin_id entity", $account)) {
      $route_params = ['group' => $group->id(), 'plugin_id' => $plugin_id];
      $operations["group_entity-create-$type-$bundle"] = [
        'title' => $this->t('Create @type: @bundle', [
          '@type' => $this->getEntityType()->getLabel(),
          '@bundle' => $bundle
        ]),
        'url' => new Url('entity.group_content.create_form', $route_params),
        'weight' => 30,
      ];
    }

    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $config = parent::defaultConfiguration();
    $config['group_cardinality'] = 1;
    $config['entity_cardinality'] = 1;
    $config['use_creation_wizard'] = 0;
    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $fields = [
      'group_cardinality',
      'entity_cardinality',
      'use_creation_wizard',
    ];

    // Disable the entity cardinality field as the functionality of this module
    // relies on a cardinality of 1. We don't just hide it, though, to keep a UI
    // that's consistent with other content enabler plugins.
    foreach ($fields as $f) {
      $info = $this->t("This field has been disabled by the plugin to guarantee the functionality that's expected of it.");
      $form[$f]['#disabled'] = TRUE;
      $form[$f]['#description'] .= '<br /><em>' . $info . '</em>';
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $dependencies = parent::calculateDependencies();
    // $dependencies['config'][] = 'node.type.' . $this->getEntityBundle(); @todo: Figure out what these are supposed to be for all other entity types.
    return $dependencies;
  }

}
