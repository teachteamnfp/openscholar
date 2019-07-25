<?php

namespace Drupal\os_media\Plugin\Filter;

use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * Class OsMediaFileTransformationFilter.
 *
 * @Filter(
 *   id = "media_transform",
 *   title = @Translation("Media Transformer"),
 *   description = @Translation("Transforms media thumbnails into full rich-text entities"),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_IRREVERSIBLE,
 *   settings = {},
 *   weight = 100
 * )
 */
class OsMediaFileTransformationFilter extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $matches = [];
    preg_match_all('|<img[^>]+data-mid="([\d]+)"[^>]*>|', $text, $matches, PREG_SET_ORDER);
    $result = new FilterProcessResult();
    foreach ($matches as $m) {
      $imgTag = $m[0];
      $mid = $m[1];
      $args = [
        $mid,
      ];
      $width = [];
      preg_match_all('|width="([\d]+)"|', $imgTag, $width, PREG_SET_ORDER);
      $width = $width[0];
      if (empty($width)) {
        $args[] = 'default';
      }
      else {
        $args[] = $width[1];
      }
      $placeholder = $result->createPlaceholder('os_media.lazy_builders:renderMedia', $args);
      $text = str_replace($imgTag, $placeholder, $text);
    }
    $result->setProcessedText($text);
    return $result;
  }

}
