<?php

namespace Drupal\vsite;

use Drupal\Core\Form\FormStateInterface;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;

/**
 * FormDeleteDestination service.
 */
class FormDeleteDestination implements FormDeleteDestinationInterface {

  /**
   * The vsite.context_manager service.
   *
   * @var VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * Constructs a FormDeleteDestination object.
   *
   * @param VsiteContextManagerInterface $vsite_context_manager
   *   The vsite.context_manager service.
   */
  public function __construct(VsiteContextManagerInterface $vsite_context_manager) {
    $this->vsiteContextManager = $vsite_context_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function setDeleteButtonDestination(array &$form, FormStateInterface $form_state) : void {
    if (empty($form['actions']['delete']['#url']) || empty($form['actions']['delete']['#url']->getOptions())) {
      return;
    }
    $delete_link_options = $form['actions']['delete']['#url']->getOptions();
    if (empty($delete_link_options['query']['destination'])) {
      // Init destination
      $delete_link_options['query']['destination'] = '';
    }
    $mapping = $this->getRedirectMapping();
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $form_state->getFormObject()->getEntity();
    $bundle = $entity->bundle();
    if ($entity->getEntityTypeId() == 'bibcite_reference') {
      $bundle = '*';
    }
    if (empty($mapping[$entity->getEntityTypeId()][$bundle])) {
      return;
    }
    $newOptionQuery = $delete_link_options['query'];
    $redirectPath = $mapping[$entity->getEntityTypeId()][$bundle];
    $new_destination = '/';
    if ($this->vsiteContextManager->getActiveVsite()) {
      $new_destination = '/' . $this->vsiteContextManager->getActivePurl();
    }
    $new_destination .= '/' . $redirectPath;
    $newOptionQuery['destination'] = $new_destination;
    $form['actions']['delete']['#url']->setOption('query', $newOptionQuery);
  }

  public function getRedirectMapping(): array {
    $mapping = [
      'node' => [
        'blog' => 'blog',
        'events' => 'calendar',
        'class' => 'classes',
        'link' => 'links',
        'news' => 'news',
        'person' => 'people',
        'presentation' => 'presentations',
        'software_project' => 'software',
      ],
      'bibcite_reference' => [
        '*' => 'publications',
      ],
    ];
    return $mapping;
  }
}
