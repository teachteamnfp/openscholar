<?php

namespace Drupal\os_widgets\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\layout_builder\LayoutEntityHelperTrait;
use Drupal\os_widgets\LayoutContextInterface;

/**
 * Data structure for holding arrangements of blocks.
 *
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
 *     "status",
 *     "description",
 *     "activationRules",
 *     "weight",
 *     "data"
 *   }
 * )
 */
class LayoutContext extends ConfigEntityBase implements LayoutContextInterface {

  use LayoutEntityHelperTrait;

  protected $id;

  protected $label;

  protected $description = '';

  protected $status = 1;

  protected $activationRules = '';

  protected $weight = 0;

  protected $data = [];

  /**
   * Returns all contexts applicable to this page.
   *
   * @return \Drupal\os_widgets\LayoutContextInterface[]
   *   Array of applicable contexts.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public static function getApplicable() {

    /** @var \Drupal\os_widgets\LayoutContextInterface[] $contexts */
    $contexts = \Drupal::entityTypeManager()->getStorage('layout_context')->loadMultiple();
    $applicable = [];
    foreach ($contexts as $c) {
      if ($c->applies()) {
        $applicable[] = $c;
      }
    }
    @uasort($applicable, ['ConfigEntityBase', 'sort']);
    $applicable = array_reverse($applicable);

    return $applicable;
  }

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
  public function setActivationRules(string $rules) {
    $this->activationRules = $rules;
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return (int) $this->weight;
  }

  /**
   * {@inheritdoc}
   */
  public function getSections() {
    return $this->data;
  }

  /**
   * {@inheritdoc}
   */
  protected function setSections(array $sections) {
    $this->data = $sections;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(): bool {
    $rules = $this->getActivationRules();

    $rule_lines = preg_split('|[\r\n]|', $rules);
    $route_name = \Drupal::routeMatch()->getRouteName();
    $path = \Drupal::request()->getUri();

    foreach ($rule_lines as $rule) {
      $negate = FALSE;
      if ($rule[0] == '~') {
        $negate = TRUE;
        $rule = substr($rule, 1);
      }
      $rule = '|' . str_replace('*', '[.]*', $rule) . '|';
      if (preg_match($rule, $route_name) || preg_match($rule, $path)) {
        return !$negate;
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getBlockPlacements() {
    return $this->data;
  }

  /**
   * {@inheritdoc}
   */
  public function setBlockPlacements(array $blocks) {
    $this->data = $blocks;
  }

}
