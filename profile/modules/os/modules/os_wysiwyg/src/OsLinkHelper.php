<?php

namespace Drupal\os_wysiwyg;

use Drupal\Core\StreamWrapper\StreamWrapperManagerInterface;
use Drupal\media\Entity\Media;
use Drupal\media\MediaInterface;

/**
 * OsLinkHelper class.
 */
class OsLinkHelper implements OsLinkHelperInterface {

  /**
   * Stream Wrapper Manager.
   *
   * @var \Drupal\Core\StreamWrapper\StreamWrapperManagerInterface
   */
  protected $streamWrapperManager;

  /**
   * OsLinkHelper constructor.
   *
   * @param \Drupal\Core\StreamWrapper\StreamWrapperManagerInterface $stream_wrapper_manager
   *   Stream Wrapper Manager.
   */
  public function __construct(StreamWrapperManagerInterface $stream_wrapper_manager) {
    $this->streamWrapperManager = $stream_wrapper_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getFileUrlFromMedia(int $mid) {
    $url = '';
    $media = Media::load($mid);
    if (empty($media)) {
      return $url;
    }
    $file = $this->getFileFromMedia($media);
    if (empty($file)) {
      return $url;
    }
    $uri = $file->getFileUri();
    $url = $this->streamWrapperManager->getViaUri($uri)->getExternalUrl();
    return $url;
  }

  /**
   * {@inheritdoc}
   */
  public function getFileFromMedia(MediaInterface $media) {
    $file_field_name = '';
    if ($media->hasField('field_media_file')) {
      $file_field_name = 'field_media_file';
    }
    if ($media->hasField('field_media_image')) {
      $file_field_name = 'field_media_image';
    }
    if (empty($file_field_name)) {
      return NULL;
    }
    $referencedEntities = $media->get($file_field_name)->referencedEntities();
    return array_shift($referencedEntities);
  }

}
