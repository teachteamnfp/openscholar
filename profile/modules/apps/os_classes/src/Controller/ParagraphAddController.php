<?php

namespace Drupal\os_classes\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Add a new Paragraph to Class.
 */
class ParagraphAddController extends ControllerBase {


  /**
   * The request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * ParagraphAddController constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   The request stack service.
   */
  public function __construct(RequestStack $request) {
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
        $container->get('request_stack')
    );
  }

  /**
   * Builds Paragraph Add Form.
   *
   * @param string $type
   *   The bundle type.
   *
   * @return array
   *   The form itself.
   */
  public function buildAddForm($type) {

    $paragraph = Paragraph::create([
      'type' => $type,
      'bundle' => $type,
    ]);
    $form = $this->entityFormBuilder()->getForm($paragraph, 'default');

    return $form;
  }

  /**
   * Builds paragraph view page.
   *
   * @return array
   *   The rendered output.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function buildMaterialView() {

    $mid = $this->request->getCurrentRequest()->query->get('mid');
    $entity_type = 'paragraph';
    $view_mode = 'full';

    $entity = $this->entityTypeManager()->getStorage($entity_type)->load($mid);
    $view_builder = $this->entityTypeManager()->getViewBuilder('paragraph');
    $render = $view_builder->view($entity, $view_mode);

    return $render;
  }

  /**
   * Returns page title.
   *
   * @return string
   *   Page title.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getTitle() {
    $paragraph = $this->getParagraph();
    return $paragraph->field_title->value;
  }

  /**
   * Cached paragraph instance.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $paragraph = NULL;

  /**
   * Loads the paragraph.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The paragraph itself.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getParagraph() : EntityInterface {
    if (!$this->paragraph) {
      $mid = $this->request->getCurrentRequest()->query->get('mid');
      $this->paragraph = $this->entityTypeManager()->getStorage('paragraph')->load($mid);
    }
    return $this->paragraph;
  }

}
