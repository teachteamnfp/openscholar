<?php

namespace Drupal\vsite;

use Drupal\Core\Form\FormStateInterface;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;

/**
 * FormDeleteDestination service.
 */
class FormDeleteDestination implements FormDeleteDestinationInterface {

  const REDIRECT_MAPPING = [
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

  /**
   * The Vsite Context Manager service.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * Constructs a FormDeleteDestination object.
   *
   * @param \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager
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
      // Init destination.
      $delete_link_options['query']['destination'] = '';
    }
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $form_state->getFormObject()->getEntity();
    $bundle = $entity->bundle();
    if ($entity->getEntityTypeId() == 'bibcite_reference') {
      $bundle = '*';
    }
    if (empty(self::REDIRECT_MAPPING[$entity->getEntityTypeId()][$bundle])) {
      return;
    }
    $newOptionQuery = $delete_link_options['query'];
    $redirectPath = self::REDIRECT_MAPPING[$entity->getEntityTypeId()][$bundle];
    $new_destination = '/' . $redirectPath;
    if ($this->vsiteContextManager->getActiveVsite()) {
      $new_destination = '/' . $this->vsiteContextManager->getActivePurl() . $new_destination;
    }
    $newOptionQuery['destination'] = $new_destination;
    $form['actions']['delete']['#url']->setOption('query', $newOptionQuery);
  }

}
