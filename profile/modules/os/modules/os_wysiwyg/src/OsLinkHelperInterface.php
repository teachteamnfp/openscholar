<?php

namespace Drupal\os_wysiwyg;

use Drupal\media\MediaInterface;

/**
 * OsLinkHelperInterface class.
 */
interface OsLinkHelperInterface {

  /**
   * Get file url from media id.
   *
   * @param int $mid
   *   Media ID.
   *
   * @return string
   *   File url.
   */
  public function getFileUrlFromMedia(int $mid);

  /**
   * Get file from media.
   *
   * @param \Drupal\media\MediaInterface $media
   *   Media entity.
   *
   * @return \Drupal\file\Entity\File|null
   *   File entity or null.
   */
  public function getFileFromMedia(MediaInterface $media);

}
