<?php

namespace Drupal\os_publications\ParamConverter;

use Drupal\Core\ParamConverter\ParamConverterInterface;
use Symfony\Component\Routing\Route;

/**
 * Class OsPublicationsParamConverter.
 *
 * @package Drupal\os_publications\ParamConverter
 */
class OsPublicationsParamConverter implements ParamConverterInterface {

  /**
   * {@inheritdoc}
   */
  public function convert($value, $definition, $name, array $defaults) {
    // If ($name == 'bibcite_format') {
    // $formatManager = \Drupal::service('plugin.manager.bibcite_format');
    // return $formatManager->createInstance($value);
    // }
    // elseif ($name == 'request') {
    // return \Drupal::request();
    // }
  }

  /**
   * {@inheritdoc}
   */
  public function applies($definition, $name, Route $route) {
    return (!empty($definition['type']) && ($definition['type'] == 'bibcite_format' || $definition['type'] == 'request'));
  }

}
