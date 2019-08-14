<?php

namespace Drupal\os_software;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\NodeInterface;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Helper functions to handle software project and release.
 */
class OsSoftwareHelper implements OsSoftwareHelperInterface {

  use StringTranslationTrait;

  private $vsiteContextManager;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Entity Type Manager Interface.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructor.
   *
   * @param \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager
   *   Vsite context manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(VsiteContextManagerInterface $vsite_context_manager, EntityTypeManagerInterface $entity_type_manager, RequestStack $request_stack) {
    $this->vsiteContextManager = $vsite_context_manager;
    $this->requestStack = $request_stack;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareReleaseTitle(NodeInterface $release_node) : string {
    $project_title = 'Project Release';

    $projects = $release_node->get('field_software_project')->referencedEntities();
    if (!empty($projects)) {
      $project_node = array_shift($projects);
      $project_title = $project_node->label();
    }

    /** @var \Drupal\Core\Field\FieldItemList $version_field */
    $version_field = $release_node->get('field_software_version');
    $version = trim($version_field->getString());

    $title_parts = [
      $project_title,
      $version,
    ];

    return implode(' ', $title_parts);
  }

  /**
   * {@inheritdoc}
   */
  public function prePopulateSoftwareProjectField(&$form): void {
    $request = $this->requestStack->getCurrentRequest();
    /** @var \Symfony\Component\HttpFoundation\ParameterBag $query */
    $query = $request->query;
    $field_software_project = $query->get('field_software_project');
    if (empty($field_software_project)) {
      return;
    }
    $group = $this->vsiteContextManager->getActiveVsite();
    if (!$group) {
      return;
    }
    $storage = $this->entityTypeManager->getStorage('node');
    $node = $storage->load($field_software_project);
    if (empty($node) || !$node->access('view') || $node->bundle() != 'software_project') {
      return;
    }
    $group_content = $group->getContentByEntityId('group_node:' . $node->bundle(), $node->id());
    if (empty($group_content)) {
      return;
    }
    $form['field_software_project']['widget'][0]['target_id']['#default_value'] = $node;
  }

}
