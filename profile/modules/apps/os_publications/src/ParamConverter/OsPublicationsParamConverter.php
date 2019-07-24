<?php

namespace Drupal\os_publications\ParamConverter;

use Drupal\bibcite\Plugin\BibciteFormatManagerInterface;
use Drupal\Core\ParamConverter\ParamConverterInterface;
use Symfony\Component\Routing\Route;

/**
 * Class OsPublicationsParamConverter.
 *
 * @package Drupal\os_publications\ParamConverter
 */
class OsPublicationsParamConverter implements ParamConverterInterface {

  /**
   * OsPublicationsParamConverter constructor.
   *
   * @param \Drupal\bibcite\Plugin\BibciteFormatManagerInterface $format_manager
   *   Format manager instance.
   */
  public function __construct(BibciteFormatManagerInterface $format_manager) {
    $this->formatManager = $format_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function convert($value, $definition, $name, array $defaults) {
    return $this->formatManager->createInstance($value);
  }

  /**
   * {@inheritdoc}
   */
  public function applies($definition, $name, Route $route) {
    return (!empty($definition['type']) && $definition['type'] == 'bibcite_format');
  }

}
