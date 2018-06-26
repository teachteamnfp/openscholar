<?php

/**
 * Extracting the files from the zip to a folder, parsing information from the
 * info file and more.
 *
 * How to use:
 *  $fid = 1;
 *  $file = new SubTheme($fid);
 *  $file->extract(); // This isn't necessary anymore.
 *  $info = $file->parseInfo();
 *
 * or:
 *
 *  $file = new SubTheme();
 *  $file->path = GIT_CLONED_PATH;
 *  $info = $file->parseInfo();
 */

class SubTheme {

  /**
   * The file ID.
   * @var string|null
   */
  public $fid;

  /**
   * The path of the ZIP file.
   * @var string|null
   */
  public $filePath;

  /**
   * The path which the ZIP file is extracted to.
   * @var string|null
   */
  public $extractPath;

  /**
   * The file object.
   * @var object|null
   */
  public $file;

  /**
   * The path of the folder.
   * @var string|null
   */
  public $path;

  /**
   * Path of the flavor file in the subthemes' folder (and it's ready to use).
   *
   * @var string|null
   */
  public $flavorPath;

  /**
   * Constructing the class.
   *
   * @param $fid
   *  A file id.
   */
  public function __construct($fid = NULL) {
    $this->fid = $fid;
    $this->flavorPath = NULL;
    $this->fileInfo();

    if (
      !empty(glob($this->extractPath . DIRECTORY_SEPARATOR . '*.flav')) ||
      !empty(glob($this->path . DIRECTORY_SEPARATOR . '*.flav'))
    ) {
      // Subtheme has a flavor file and
      // is extracted already
      // OR
      // it's a git project, not a ZIP archive.
      if (empty($this->path)) {
        $this->path = $this->extractPath;
      }
      $this->flavorPath = reset(glob($this->extractPath . DIRECTORY_SEPARATOR . '*.flav'));
    }
  }

  /**
   * Return info for the intended folder path for a zip file.
   */
  public function fileInfo() {
    if (
      !empty($this->fid) &&
      $this->file = file_load($this->fid)
    ) {
      // Creating an array of stream wrappers that will be removed.
      $streams = array();
      foreach (stream_get_wrappers() as $stream) {
        $streams[] = $stream . '://';
      }

      $files_folder = variable_get('file_public_path', conf_path() . DIRECTORY_SEPARATOR . 'files');

      // Generate the folder name by the unique URI of the file.
      $file_name = str_replace($streams, '', $this->file->uri);
      $folder_name = str_replace(array('.', '_'), '-', $file_name);

      $this->filePath = empty($this->filePath) ? $files_folder . DIRECTORY_SEPARATOR . $file_name : $this->filePath;
      $this->extractPath = empty($this->extractPath) ? $files_folder . DIRECTORY_SEPARATOR . $folder_name : $this->extractPath;
    }
  }

  /**
   * Extracting the zip file.
   */
  public function extract() {
    if (!empty($this->flavorPath)) {
      return;
    }

    $zip = new ZipArchive;

    if ($zip->open($this->filePath) === TRUE) {
      // Extract the file.
      $zip->extractTo($this->extractPath);
      $zip->close();

      $this->processExtractedSubtheme(TRUE);
    }
    else {
      // Couldn't extract the zip file, display an error message and watchdog.
      $params = array(
        '@file' => $this->filePath,
        '@path' => $this->extractPath,
      );

      $message = t('Unable to extract @file to @path', $params);
      watchdog('subtheme', $message);
      drupal_set_message($message, 'error');
    }
  }

  /**
   * Helper callback to process the Subtheme.
   *
   * @param bool $cleanup_needed
   *   Whether the subtheme folder has to be cleaned form unnecessary folders
   *   or not.
   */
  private function processExtractedSubtheme($cleanup_needed = FALSE) {
    $flav_root = $this->findFileByPattern($this->extractPath, '*.flav');
    // We found a flavour file and can set up path for this subtheme.
    $this->flavorPath = $flav_root;
    $this->path = $this->extractPath;

    if (
      !$flav_root ||
      $flav_root === $this->extractPath
    ) {
      return;
    }

    if ($cleanup_needed) {
      //
      // Remove unnecessary subfolders.
      //
      $dirs_in_path_root = glob($this->path . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);
      foreach ($dirs_in_path_root as $index => $dir_in_path_root) {
        if (strpos($flav_root, $dir_in_path_root) !== 0) {
          $this->deleteTree($dir_in_path_root);
          unset($dirs_in_path_root[$index]);
        }
      }
    }

    //
    // Move theme files and folders into the root path.
    //
    $flav_root_content = array_filter(scandir($flav_root), function($value) {
      return !in_array($value, array('.', '..'));
    });

    foreach ($flav_root_content as $resource) {
      rename(
        $flav_root . DIRECTORY_SEPARATOR . $resource,
        $this->path .  DIRECTORY_SEPARATOR . $resource
      );
    }

    if ($cleanup_needed) {
      // Remove the first subpath in the unzipped folder where the flavour files
      // were extracted originally if it won't cause component loss.
      // Exception example:
      // Flavour file extracted to:
      // @code
      // public://
      // |-- my-flavour-zip
      //   |-- css
      //     |-- my-flavout.flav
      //     |-- screenshot.png
      //     |-- css
      //       |-- my-flavout.css
      // @endcode
      // In this case, we wont remove the 'public://my-flavour-zip/css' dir
      // since it would lead to loose 'my-flavout.css' as well.
      foreach ($dirs_in_path_root as $dir_in_path_root) {
        if (!in_array(basename($dir_in_path_root), $flav_root_content)) {
          $this->deleteTree($dir_in_path_root);
        }
      }
    }
  }

  /**
   * Helper method to find file in a directory by a name pattern.
   *
   * @param string $path
   *   The path to search in recursively.
   * @param string $pattern
   *   File or directory name pattern to match.
   *
   * @return string|null
   *   Path to the matching resource found first or NULL if there are no matches
   *   at all.
   */
  private function findFileByPattern($path, $pattern) {
    $path_match = NULL;
    $files_in_path = glob($path . DIRECTORY_SEPARATOR . trim($pattern, " \t\n\r\0\x0B" . DIRECTORY_SEPARATOR));


    if (!empty($files_in_path)) {
      return $path;
    }

    $dirs_in_path = glob($path . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);

    if (empty($dirs_in_path)) {
      return NULL;
    }

    foreach ($dirs_in_path as $subdir) {
      $path_match = $this->findFileByPattern($subdir, $pattern);

      if ($path_match) {
        break;
      }
    }

    return $path_match;
  }

  /**
   * Helper method to delete non-empty directory.
   *
   * @param string $dir
   *   A relative or absolute dir to delete.
   *
   * @return bool
   *   Returns TRUE on success or FALSE on failure (e.g. lack of permissions).
   */
  private function deleteTree($dir) {
    $files = array_filter(scandir($dir), function($value) {
      return !in_array($value, array('.', '..'));
    });

    foreach ($files as $file) {
      $match = $dir . DIRECTORY_SEPARATOR . $file;
      (is_dir($match)) ? $this->deleteTree($match) : unlink($match);
    }

    return rmdir($dir);
  }

  /**
   * Parsing the info file of the sub theme.
   */
  public function parseInfo() {
    if (empty($this->path)) {
      $this->extract();
    }

    $info_files = glob($this->path  . DIRECTORY_SEPARATOR . '*.flav');
    $info = array();

    $info_file = reset($info_files);

    // Get the information from the info file.
    $file_content = drupal_parse_info_file($info_file);

    foreach ($file_content as $key => $value) {
      $info[$key] = $value;
    }

    $info['theme name'] = str_replace(array(dirname($info_file), '.flav', '/'), '', $info_file);

    return $info;
  }

  /**
   * Checks the uploaded file.
   *
   * @param string $fid
   *   The file id of the zip uploaded as an update.
   *
   * @return bool
   *   TRUE if the uploaded zip is contains a valid flavor, FALSE otherwise.
   */
  public function verifyZip($fid) {
    $this->fid = $fid;
    $this->fileInfo();
    $destination_temp = drupal_realpath('temporary://cp_appearance_theme_update_verify');
    file_prepare_directory($destination_temp, FILE_CREATE_DIRECTORY);
    $this->extractPath = $destination_temp;
    $this->extract();
    $this->deleteTree($destination_temp);

    return !empty($this->flavorPath);
  }

  /**
   * Update existing subtheme.
   *
   * @param string $new_fid
   *   The file id of the zip uploaded as an update.
   *
   * @param string $path_target
   *   The original path of the subtheme to update.
   *
   * @return array
   *   An array with previous and new display name of the flavor, or empty array
   *   if one of them is missing or the operation was unsuccessful.
   */
  public function updateFromZip($new_fid, $path_target) {
    $info_files_original = glob($path_target . DIRECTORY_SEPARATOR . '*.flav');
    $info_file_original = reset($info_files_original);
    $info_original = drupal_parse_info_file($info_file_original);
    $info_file_name_original = pathinfo($info_file_original, PATHINFO_FILENAME);
    $info = array();

    $this->fid = $new_fid;
    $this->fileInfo();
    $destination_temp = drupal_realpath('temporary://cp_appearance_theme_update');
    file_prepare_directory($destination_temp, FILE_CREATE_DIRECTORY);
    $this->extractPath = $destination_temp;
    $this->flavorPath = NULL;
    $this->extract();

    // Continue only if there is a flavor file in the extracted zip archive.
    if ($this->flavorPath) {
      $info_file_new = reset(glob($destination_temp . DIRECTORY_SEPARATOR . '*.flav'));
      $info_new = drupal_parse_info_file($info_file_new);
      // Keep the new theme info filename the same as it was previously.
      if (pathinfo($info_file_new, PATHINFO_FILENAME) !== $info_file_name_original) {
        rename(
          $info_file_new,
          $destination_temp . DIRECTORY_SEPARATOR . $info_file_name_original . '.flav'
        );
      }
      $this->recursiveMove($destination_temp, $path_target);
      $this->deleteTree($destination_temp);
      // Delete the uploaded zip.
      file_delete($this->file);
      if (
        !empty($info_original['name']) &&
        !empty($info_new['name']) &&
        $info_original['name'] !== $info_new['name']
      ) {
        $info['name_original'] = $info_original['name'];
        $info['name_new'] = $info_new['name'];
      }
    }

    return $info;
  }

  /**
   * Helper callback to move a subtheme.
   *
   * @param string $source_path
   *   Source path.
   * @param string $target_path
   *   Target path.
   */
  private function recursiveMove($source_path, $target_path) {
    //
    // Move files and folders found in source path to the target path.
    //
    $source_path_content = array_filter(scandir($source_path), function($value) {
      return !in_array($value, array('.', '..'));
    });

    foreach ($source_path_content as $resource) {
      if (
        is_dir($target_path .  DIRECTORY_SEPARATOR . $resource)
      ) {
        $this->recursiveMove($source_path . DIRECTORY_SEPARATOR . $resource, $target_path .  DIRECTORY_SEPARATOR . $resource);
      }
      else {
        rename(
          $source_path . DIRECTORY_SEPARATOR . $resource,
          $target_path .  DIRECTORY_SEPARATOR . $resource
        );
      }
    }
  }

}
