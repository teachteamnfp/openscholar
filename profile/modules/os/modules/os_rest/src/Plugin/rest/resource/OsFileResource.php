<?php

namespace Drupal\os_rest\Plugin\rest\resource;

use Drupal\Component\Utility\Bytes;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Image\ImageFactory;
use Drupal\file\Entity\File;
use Drupal\file\FileInterface;
use Drupal\file\Plugin\rest\resource\FileUploadResource;
use Drupal\image\Entity\ImageStyle;
use Drupal\media\Entity\Media;
use Drupal\media\MediaInterface;
use Drupal\media\MediaSourceInterface;
use Drupal\media\MediaSourceManager;
use Drupal\media\MediaTypeInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\RequestHandler;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Route;

/**
 * File upload resource.
 *
 * This is implemented as a field-level resource for the following reasons:
 *   - Validation for uploaded files is tied to fields (allowed extensions, max
 *     size, etc..).
 *   - The actual files do not need to be stored in another temporary location,
 *     to be later moved when they are referenced from a file field.
 *   - Permission to upload a file can be determined by a users field level
 *     create access to the file field.
 *
 * @RestResource(
 *   id = "file:os:upload",
 *   label = @Translation("OpenScholar File Upload"),
 *   serialization_class = "Drupal\file\Entity\File",
 *   uri_paths = {
 *     "canonical" = "/api/file-upload/{entity}",
 *     "create" = "/api/file-upload"
 *   }
 * )
 */
class OsFileResource extends FileUploadResource {

  /**
   * {@inheritdoc}
   */
  public function post(Request $request, $entity_type_id = '', $bundle = '', $field_name = '') {
    $destination = $this->getUploadLocation();

    // Check the destination file path is writable.
    if (!file_prepare_directory($destination, FILE_CREATE_DIRECTORY)) {
      throw new HttpException(500, 'Destination file path is not writable');
    }

    $validators = $this->getUploadValidators();

    // Save the uploaded file.
    /** @var UploadedFile $file_raw */
    $file_raw = $request->files->get('file');

    if ($newName = $request->request->get('newName')) {
      // Make a new file that's the right name.
      $file_raw = new UploadedFile($file_raw->getPathname(), $newName, $file_raw->getMimeType(), $file_raw->getSize(), $file_raw->getError(), true);
    }

    // Can't use file_save_upload() because it expects all files to be in the files array in the files parameter of the request
    // $request->files->get('files'), which is weird and going to be empty when coming from js
    $file = _file_save_upload_single($file_raw, 'upload', $validators, $destination, FILE_EXISTS_REPLACE);

    if (!$file) {
      throw new HttpException(500, 'File could not be saved.');
    }

    $extension = pathinfo($file->getFileUri(), PATHINFO_EXTENSION);

    /** @var MediaTypeInterface[] $mediaTypes */
    $mediaTypes = \Drupal::entityTypeManager()->getStorage('media_type')->loadMultiple();
    foreach ($mediaTypes as $mediaType) {
      $fieldDefinition = $mediaType->getSource()->getSourceFieldDefinition($mediaType);
      if (is_null($fieldDefinition)) continue;
      $exts = explode(' ', $fieldDefinition->getSetting('file_extensions'));
      if (in_array($extension, $exts)) {
        $media = Media::create([
          'bundle' => $mediaType->id(),
          'uid' => \Drupal::currentUser()->id(),
          'langcode' => \Drupal::languageManager()->getDefaultLanguage()->getId(),
          $fieldDefinition->getName() => [
            'target_id' => $file->id()
          ]
        ]);
      }
    }
    if (!$media) {
      $file->delete();
      throw new HttpException(500, 'No Media Type accepts this kind of file.');
    }
    $media->save();

    // 201 Created responses return the newly created entity in the response
    // body. These responses are not cacheable, so we add no cacheability
    // metadata here.
    return new ModifiedResourceResponse($media, 201);
  }

  /**
   * Replace an existing file on disk with the freshly uploaded file.
   *
   * @param EntityInterface $entity
   *   The file whose contents are being replaced
   *
   * @return ModifiedResourceResponse
   *   The response.
   */
  public function put(EntityInterface $entity) {
    $temp_file_path = $this->streamUploadData();
    /** @var FileInterface $target */
    $target = $entity;

    if (file_unmanaged_copy($temp_file_path, $target->getFileUri(), FILE_EXISTS_REPLACE) === FALSE) {
      throw new HttpException(500, 'The file could not be replaced.');
    }

    $target->save();
    if (!file_validate_is_image($target)) {
      /** @var ImageStyle[] $imageStyles */
      $imageStyles = \Drupal::entityTypeManager()->getStorage('image_style')->loadMultiple();

      foreach ($imageStyles as $style) {
        $style->flush($target->getFileUri());
      }
    }

    file_unmanaged_delete($temp_file_path);

    return new ModifiedResourceResponse($target, 200);
  }

  protected function getUploadLocation(array $settings = []) {
    /** @var VsiteContextManagerInterface $vsiteContextManager */
    $vsiteContextManager = \Drupal::service('vsite.context_manager');
    if ($purl = $vsiteContextManager->getActivePurl()) {
      return 'public://'.$purl.'/files';
    }
    return 'public://global';
  }

  /**
   * Returns validators applicable for every field
   *
   * @param FieldDefinitionInterface|null $field_definition
   *   Not used. Only here for compatibility.
   * @return array
   *   The validators
   */
  protected function getUploadValidators(FieldDefinitionInterface $field_definition = null) {
    $validators = [
      // Add in our check of the file name length.
      'file_validate_name_length' => [],
    ];

    // Cap the upload size according to the PHP limit.
    $max_filesize = Bytes::toInt(file_upload_max_size());

    // There is always a file size limit due to the PHP server limit.
    $validators['file_validate_size'] = [$max_filesize];

    // Add the extension check if necessary.
    // $validators['file_validate_extensions'] = [];

    return $validators;
  }

  /**
   * Return validators applicable for replacing a single file.
   *
   * @param FileInterface $target
   * @return array
   */
  protected function getReplacementValidators(FileInterface $target) {
    $validators = [];

    // Cap the upload size according to the PHP limit.
    $max_filesize = Bytes::toInt(file_upload_max_size());

    // There is always a file size limit due to the PHP server limit.
    $validators['file_validate_size'] = [$max_filesize];

    // Add the extension check if necessary.
    $uri = $target->getFileUri();
    $extension = pathinfo($uri, PATHINFO_EXTENSION);
    $validators['file_validate_extensions'] = [$extension];

    return $validators;
  }

  /**
   * {@inheritdoc}
   */
  protected function getBaseRoute($canonical_path, $method) {
    $route = parent::getBaseRoute($canonical_path, $method);
    $route->setOption('parameters', ['entity' => ['type' => 'entity:file']]);
    return $route;
  }

  /**
   * {@inheritdoc}
   */
  protected function getBaseRouteRequirements($method) {
    $reqs = parent::getBaseRouteRequirements($method);

    $reqs['_content_type_format'] = '*';

    return $reqs;
  }
}