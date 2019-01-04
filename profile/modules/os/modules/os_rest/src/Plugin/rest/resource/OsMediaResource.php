<?php

namespace Drupal\os_rest\Plugin\rest\resource;

use Drupal\Core\Entity\EntityInterface;
use Drupal\rest\ResourceResponse;

/**
 * Class OsMediaResource.
 *
 * @package Drupal\os_rest\Plugin\rest\resource
 */
class OsMediaResource extends OsEntityResource {

  /**
   * Switch between paths based on argument type.
   *
   * Every GET call to this resource goes through this method,
   * and PHP doesn't support method overloading, so this kind of thing is necessary.
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
    $resource = new ResourceResponse([
      'filename' => $filename,
    ]);
    $resource->addCacheableDependency($filename);
    return $resource;
  }

  /**
   * {@inheritdoc}
   */
  public function routes() {
    $routeCollection = parent::routes();

    $path = '/media/filename/{filename}';
    $route = $this->getBaseRoute($path, 'get');
    $params['filename'] = 'filename';

    $route_name = strtr($this->pluginId, ':', '.');
    $routeCollection->add("$route_name.get.filename", $route);

    return $routeCollection;
  }

}
