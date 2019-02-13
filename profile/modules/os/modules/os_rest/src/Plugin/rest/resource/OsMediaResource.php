<?php

namespace Drupal\os_rest\Plugin\rest\resource;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\rest\ResourceResponse;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;
use Symfony\Component\HttpFoundation\Request;

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
    /** @var VsiteContextManagerInterface $vsiteContextManager */
    $vsiteContextManager = \Drupal::service('vsite.context_manager');
    $directory = 'public://global/';
    if ($purl = $vsiteContextManager->getActivePurl()) {
      $directory = 'public://'.$purl.'/files/';
    }

    $new_filename = strtolower($filename);
    $new_filename = preg_replace('|[^a-z0-9\-_\.]|', '_', $new_filename);
    $new_filename = preg_replace(':__:', '_', $new_filename);
    $new_filename = preg_replace('|_\.|', '.', $new_filename);
    $invalidChars = false;
    if ($filename != $new_filename) {
      $invalidChars = true;
    }

    $fullname = $directory . $new_filename;
    $counter = 0;
    $collision = false;
    while (file_exists($fullname)) {
      $collision = true;
      $pos = strrpos($new_filename, '.');
      if ($pos !== FALSE) {
        $name = substr($new_filename, 0, $pos);
        $ext = substr($new_filename, $pos);
      } else {
        $name = basename($fullname);
        $ext = '';
      }

      $fullname = sprintf("%s%s_%02d%s", $directory, $name, ++$counter, $ext);
    }
    $resource = new ResourceResponse([
      'expectedFileName' => basename($fullname),
      'collision' => $collision,
      'invalidChars' => $invalidChars
    ]);
    $resource->addCacheableDependency($filename);
    return $resource;
  }

  /**
   * {@inheritdoc}
   */
  public function routes() {
    $routeCollection = parent::routes();

    $path = '/api/media/filename/{filename}';
    $route = $this->getBaseRoute($path, 'get');
    $params['filename'] = 'filename';

    $route_name = strtr($this->pluginId, ':', '.');
    $routeCollection->add("$route_name.get.filename", $route);

    return $routeCollection;
  }

}
