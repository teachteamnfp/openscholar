<?php

namespace Drupal\os_rest\Plugin\rest\resource;
use Drupal\Core\Entity\EntityInterface;
use Drupal\rest\ResourceResponse;

/**
 * Class OsMediaResource
 * @package Drupal\os_rest\Plugin\rest\resource
 */
class OsMediaResource extends OsEntityResource {

  public function get($arg1) {
    if ($arg1 instanceof EntityInterface) {
      return parent::get($arg1);
    }
    else if (is_string($arg1)) {
      return $this->checkFilename($arg1);
    }
  }

  protected function checkFilename($filename) {
    $resource = new ResourceResponse([
      'filename' => $filename
    ]);
    $resource->addCacheableDependency($filename);
    return $resource;
  }

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