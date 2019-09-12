<?php

namespace Drupal\os_rest\Plugin\rest\resource;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class OsMediaResource.
 *
 * @package Drupal\os_rest\Plugin\rest\resource
 */
class OsMediaResource extends OsEntityResource {

  const FILE_FIELDS = [
    'filename',
  ];

  /**
   * Switch between paths based on argument type.
   *
   * Every GET call to this resource goes through this method,
   * and PHP doesn't support method overloading, so this kind of thing is
   * necessary.
   *
   * @param \Drupal\Core\Entity\EntityInterface|string $arg1
   *   The argument from the path.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response to the client.
   */
  public function get($arg1) {
    if ($arg1 instanceof EntityInterface) {
      return parent::get($arg1);
    }
    elseif (is_string($arg1)) {
      return $this->checkFilename($arg1);
    }
  }

  /**
   * Check the filename for collisions.
   *
   * @param string $filename
   *   The filename to check for collisions.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response to the client.
   */
  protected function checkFilename($filename) {
    /** @var \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsiteContextManager */
    $vsiteContextManager = \Drupal::service('vsite.context_manager');
    $directory = 'public://global/';
    if ($purl = $vsiteContextManager->getActivePurl()) {
      $directory = 'public://' . $purl . '/files/';
    }

    $new_filename = strtolower($filename);
    $new_filename = preg_replace('|[^a-z0-9\-_\.]|', '_', $new_filename);
    $new_filename = preg_replace(':__:', '_', $new_filename);
    $new_filename = preg_replace('|_\.|', '.', $new_filename);
    $invalidChars = FALSE;
    if ($filename != $new_filename) {
      $invalidChars = TRUE;
    }

    $fullname = $directory . $new_filename;
    $counter = 0;
    $collision = FALSE;
    while (file_exists($fullname)) {
      $collision = TRUE;
      $pos = strrpos($new_filename, '.');
      if ($pos !== FALSE) {
        $name = substr($new_filename, 0, $pos);
        $ext = substr($new_filename, $pos);
      }
      else {
        $name = basename($fullname);
        $ext = '';
      }

      $fullname = sprintf("%s%s_%02d%s", $directory, $name, ++$counter, $ext);
    }
    $resource = new ResourceResponse([
      'expectedFileName' => basename($fullname),
      'collision' => $collision,
      'invalidChars' => $invalidChars,
    ]);
    $resource->addCacheableDependency($filename);
    return $resource;
  }

  /**
   * Responds to entity PATCH requests.
   *
   * @param \Drupal\Core\Entity\EntityInterface $original_entity
   *   The original entity object.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function patch(EntityInterface $original_entity, EntityInterface $entity = NULL) {
    $data = json_decode(\Drupal::request()->getContent(), TRUE);
    if ($entity == NULL) {
      throw new BadRequestHttpException('No entity content received.');
    }
    $definition = $this->getPluginDefinition();
    if ($entity->getEntityTypeId() != $definition['entity_type']) {
      throw new BadRequestHttpException('Invalid entity type');
    }

    $changed_fields = [];
    $fileEntityOrig = [];
    foreach ($entity->_restSubmittedFields as $key => $field_name) {
      // Check for.
      if (in_array($field_name, self::FILE_FIELDS)) {
        if ($entity->bundle() == 'image') {
          $field = 'field_media_image';
        }
        elseif ($entity->bundle() == 'document') {
          $field = 'field_media_file';
        }
        elseif ($entity->bundle() == 'video') {
          $field = 'field_media_video_file';
        }
        $fileId = $original_entity->get($field)->get(0)->get('target_id')->getValue();
        $fileEntityOrig = \Drupal::entityTypeManager()->getStorage('file')->load($fileId);
        $changed_fields[] = $field_name;
        $fileEntityOrig->set($field_name, $data[$field_name]);
        unset($entity->_restSubmittedFields[$key]);
      }
    }

    if ($fileEntityOrig) {
      // Validate the received data before saving.
      $this->validate($fileEntityOrig, $changed_fields);
      try {
        $fileEntityOrig->save();
        $this->logger->notice('Updated entity %type with ID %id.', [
          '%type' => $fileEntityOrig->getEntityTypeId(),
          '%id' => $fileEntityOrig->id(),
        ]);
        // Call the parent method to update remaining fields if any.
        return parent::patch($original_entity, $entity);
      }
      catch (EntityStorageException $e) {
        throw new HttpException(500, 'Internal Server Error', $e);
      }
    }
    return parent::patch($original_entity, $entity);
  }

  /**
   * {@inheritdoc}
   */
  public function routes() {
    $routeCollection = parent::routes();

    $path = '/api/media/filename/{filename}';
    $route = $this->getBaseRoute($path, 'get');

    $route_name = strtr($this->pluginId, ':', '.');
    $routeCollection->add("$route_name.get.filename", $route);

    return $routeCollection;
  }

}
