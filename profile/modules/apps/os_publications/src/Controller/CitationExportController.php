<?php

namespace Drupal\os_publications\Controller;

use Drupal\bibcite\Plugin\BibciteFormatInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\vsite\Plugin\VsiteContextManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class CitationExportController.
 *
 * @package Drupal\os_publications\Controller
 */
class CitationExportController extends ControllerBase {

  /**
   * Plugin id to load entities.
   */
  const PLUGIN_ID = 'group_entity:bibcite_reference';

  /**
   * The serializer service.
   *
   * @var \Symfony\Component\Serializer\Serializer
   */
  protected $serializer;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Vsite Manager service.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManager
   */
  protected $vsiteManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(SerializerInterface $serializer, EntityTypeManagerInterface $entity_type_manager, VsiteContextManager $vsite_manager) {
    $this->serializer = $serializer;
    $this->entityTypeManager = $entity_type_manager;
    $this->vsiteManager = $vsite_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('serializer'),
      $container->get('entity_type.manager'),
      $container->get('vsite.context_manager')
    );
  }

  /**
   * Process citation export.
   *
   * @param array $entities
   *   Entities to be exported.
   * @param \Drupal\bibcite\Plugin\BibciteFormatInterface $bibcite_format
   *   Format to be exported in.
   * @param string $filename
   *   The file name to generate.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   Response object.
   */
  protected function processExport(array $entities, BibciteFormatInterface $bibcite_format, string $filename = NULL): Response {
    if (!$filename) {
      $filename = $bibcite_format->getLabel();
    }

    $response = new Response();

    if ($result = $this->serializer->serialize($entities, $bibcite_format->getPluginId())) {
      $response->headers->set('Cache-Control', 'no-cache');
      $response->headers->set('Content-type', 'text/plain');
      $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '.' . $bibcite_format->getExtension() . '";');

      $response->sendHeaders();

      $result = is_array($result) ? implode("\n", $result) : $result;
      $response->setContent($result);
    }

    return $response;
  }

  /**
   * Export multiple entities to available export formats.
   *
   * @param \Drupal\bibcite\Plugin\BibciteFormatInterface $bibcite_format
   *   Instance of format plugin.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   Response object contains serialized reference data.
   */
  public function exportMultiple(BibciteFormatInterface $bibcite_format): Response {
    if (!$bibcite_format->isExportFormat()) {
      throw new NotFoundHttpException();
    }

    $vsite = $this->vsiteManager->getActiveVsite();
    $publications = $vsite->getContent(self::PLUGIN_ID);
    foreach ($publications as $publication) {
      $entities[$publication->getEntity()->id()] = $publication->getEntity();
    }

    if (!$entities) {
      throw new NotFoundHttpException();
    }

    $filename = vsprintf('%s-%s-%s', [
      $vsite->label(), 'publications', $bibcite_format->getPluginId(),
    ]);
    return $this->processExport($entities, $bibcite_format, $filename);
  }

}
