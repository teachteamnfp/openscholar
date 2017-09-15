<?php
$git_path = libraries_get_path('git');
require_once(DRUPAL_ROOT . '/' . $git_path . '/autoload.php');
use GitWrapper\GitWrapper;
use GitWrapper\GitException;

/**
 * @file
 * Contains \RestfulDataProviderDbQuery
 */
class OsRestfulThemes extends \RestfulBase implements \RestfulDataProviderInterface {
  /**
   * Overrides \RestfulBase::controllersInfo().
   */
  public static function controllersInfo() {
    return array(
      '' => array(
        // If they don't pass a menu-id then display nothing.
        \RestfulInterface::POST => 'fetchBranches',
        \RestfulInterface::PUT => 'createFromGitBranches',
      ),
      // We don't know what the ID looks like, assume that everything is the ID.
      '^.*$' => array(
        \RestfulInterface::POST => 'uploadZipTheme',
        \RestfulInterface::PUT => 'editTheme',
        \RestfulInterface::GET => 'getFlavorName',
        \RestfulInterface::DELETE => 'deleteSubTheme',
      ),
    );
  }


  /**
   * {@inheritdoc}
  */
  public function publicFieldsInfo() {}

  public function uploadZipTheme() {}

  public function fetchBranches() {

    $branches = array();
    $selected_branch = '';
    // Initiate the return message
    $subtheme->msg = array();
    $repo_address = '';
    $current_branch = '';
    $flavor_name = '';

    $branch_name = isset($this->request['git']) ? urldecode($this->request['git']) : '';
    $flavor = isset($this->request['flavor']) ? urldecode($this->request['flavor']) : '';

    if ($repository_address = !empty($branch_name) ? trim($branch_name) : FALSE) {
      $wrapper = new GitWrapper();
      $wrapper->setPrivateKey('.');

      $path = variable_get('file_public_path', conf_path() . '/files') . '/subtheme/' . $repository_address;

      // @todo: Remove the github hardcoding.
      $path = str_replace(array('http://', 'https://', '.git', 'git@github.com:'), '', $path);

      if (!file_exists($path)) {
        drupal_mkdir($path, NULL, TRUE);
      }

      $git = $wrapper->workingCopy($path);

      if (!$git->isCloned()) {
        try {
          $git->clone($repository_address);
          $git->setCloned(TRUE);
        }
        catch (GitException $e) {
          // Can't clone the repo.
          $subtheme->msg[] = t('Could not clone @repository, error @error', array('@repository' => $repository_address, '@error' => $e->getMessage(), 'warning'));
        }
      }

      if ($git->isCloned()) {
        try {
          foreach ($git->getBranches()->remote() as $branch) {
            if (strpos($branch, ' -> ') !== FALSE) {
              // A branch named "origin/HEAD  -> xyz" is provided by the class, we
              // don't need it.
              continue;
            }
            $branches[str_replace(' ', '_', $branch)] = $branch;
          }
        }
        catch (GitException $e) {
        }
      }

      $sub_theme = new SubTheme();
      $sub_theme->path = $path;

      $valid_repo = FALSE;
      if ($branches) {
        $valid_repo = TRUE;
      }
      elseif (!$branches && $repository_address) {
        $subtheme->msg[] = t('Git repository is wrong.');
      }
      if ($valid_repo) {
        // return msg with $branches;
        $subtheme->branches = $branches;
      }

      // For return purpose only
      $repo_address = $repository_address;

    } else {

      // In case of editing populate the repository and branches information
      if (!empty($_GET['vsite'])) {
        $vsite = vsite_get_vsite($_GET['vsite']);
        $flavors = $vsite->controllers->variable->get('flavors');
        $info = $flavors[$flavor];
        $path = $info['path'];
        $flavor_name = $info['name'];
        $sub_theme->path = $path;

        $wrapper = new GitWrapper();
        $wrapper->setPrivateKey('.');
        $git = $wrapper->workingCopy($path);

        // Get the current branch.
        $current_branches = explode("\n", $git->branch()->getOutput());
        foreach ($current_branches as $branch) {
          if ($branch && strpos($branch, '*') === 0) {
            $selected_branch = trim(str_replace("*", '', $branch));
          }
        }

        $repo_address = $git->remote()->config('remote.origin.url')->getOutput();

        // Get the available branches.
        foreach ($git->getBranches() as $branch) {
          $branches[$branch] = $branch;
        }
        // return msg with $branches;
        $subtheme->branches = $branches;
      }
    }

    return array(
      'branches' => $subtheme->branches,
      'msg' => $subtheme->msg,
      'path' => $sub_theme->path,
      'repo' => $repo_address,
      'current_branch' => $selected_branch,
      'flavor_name' => $flavor_name,
    );
  }

  // Save
  public function createFromGitBranches() {
    error_reporting(E_ALL);
    // Initiate the return message
    $subtheme->msg = array();
    $valid = TRUE;
    watchdog('cp_rest', print_r($this->request, true));
    if (!empty($this->request['branch'])) {
      $branch = $this->request['branch'];
      $path = $this->request['path'];

      $wrapper = new GitWrapper();
      $wrapper->setPrivateKey('.');
      $git = $wrapper->workingCopy($path);

      // We pull, in case the user wants to reload a subtheme.
      try {
        if (strpos($branch, 'remotes') === 0) {
          $git->checkout($branch, array('t' => TRUE));
        }
        else {
          $git->checkout($branch);
        }
      }
      catch (GitException $e) {
      }

      try {
        $git->pull();
      }
      catch (GitException $e) {
      }

      $sub_theme = new SubTheme();
      $sub_theme->path = $path;

      if (empty($sub_theme)) {
        $valid = FALSE;
      }

      $info = $sub_theme->parseInfo();

      $themes = list_themes();

      // Validating
      if (empty($info['module'])) {
        $subtheme->msg[] = t('The theme you uploaded is not valid.  `module` directive missing.');
        //$valid = FALSE;
      }
      else if (!in_array($info['module'], array_keys($themes))) {
        $subtheme->msg[] = t('The theme you uploaded is not valid.  `module` refers to a theme that does not exist.');
        //$valid = FALSE;
      }


      // Submitting
      $file = $sub_theme;

      if (!empty($_GET['vsite']) && $valid) {

        $vsite = vsite_get_vsite($_GET['vsite']);
        $flavors = $vsite->controllers->variable->get('flavors');

        // Parse the info.
        $info = $file->parseInfo();

        // Save the path of the extracted theme for later.
        $flavors[$info['theme name']] = array(
          'path' => $file->path,
          'name' => $info['name'],
        );

        $vsite->controllers->variable->set('flavors', $flavors);
        $subtheme->msg[] = t('Success');
      } else {
        $subtheme->msg[] = t('No Vsite');
      }
    } else {
      $subtheme->msg[] = t('No branch was selected');
    }

    return array(
     'msg' => $subtheme->msg,
     'sub_theme' => $sub_theme,
    );
  }

  // Edit theme
  public function editTheme() {
    $subtheme->msg = array();
    if (!empty($this->request['branch'])) {
      $branch = $this->request['branch'];
      watchdog('cp_theme', print_r($this->request, true));
      $wrapper = new GitWrapper();
      $wrapper->setPrivateKey('.');
      $git = $wrapper->workingCopy($branch);

      $success = TRUE;
      // We didn't just updated - we change the branch. Checking out to that branch.
      try {
        if (strpos($branch, 'remotes') === 0) {
          $git->checkout($branch, array('t' => TRUE));
        }
        else {
          $git->checkout($branch);
        }
      }
      catch (GitException $e) {
        $subtheme->msg[] = $e->getMessage();
        $success = FALSE;
      }

      // Pulling hte data from the git repository.
      try {
        $git->pull();
      }
      catch (GitException $e) {
        $subtheme->msg[] = $e->getMessage();
        $success = FALSE;
      }

      if ($success) {
        $subtheme->msg[] = t('The subtheme updated succesfully.');
      }
    }
    return array(
     'msg' => $subtheme->msg,
     'sub_theme' => $sub_theme,
    );
  }

  public function getFlavorName($flavor) {
    $flavor_name = '';
    if (!empty($_GET['vsite']) && !empty($flavor)) {
      watchdog('cp_theme', print_r($flavor, true));
      $vsite = vsite_get_vsite($_GET['vsite']);
      $flavors = $vsite->controllers->variable->get('flavors');
      $info = $flavors[$flavor];
      $flavor_name = $info['name'];
    }
    return array(
       'flavor_name' => $flavor_name,
    );
  }

  public function deleteSubTheme($flavor) {
    $subtheme->msg = array();
    if (!empty($_GET['vsite']) && !empty($flavor)) {
      watchdog('cp_theme', print_r($flavor, true));
      $vsite = vsite_get_vsite($_GET['vsite']);
      $flavors = $vsite->controllers->variable->get('flavors');
      watchdog('cp_theme', print_r($flavors, true));
      $info = $flavors[$flavor];
      $dir = $info['path'];
      watchdog('cp_theme', print_r($info, true));
      $params = array('!title' => $info['name']);
      // Remove the folder and set the redirect.
      try {
        $it = new RecursiveDirectoryIterator($dir);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
        foreach($files as $file) {
          if ($file->getFilename() === '.' || $file->getFilename() === '..') {
            continue;
          }

          if ($file->isDir()) {
            rmdir($file->getRealPath());
          }
          else {
            unlink($file->getRealPath());
          }
        }
        rmdir($dir);
      } catch (Exception $e) {
        $params = array('!error' => $e->getMessage());
        $subtheme->msg[] = t('An error occurred: !error', $params);
      }

      $subtheme->msg[] = t('The theme !title has been removed.', $params);
      unset($flavors[$flavor]);
      $vsite->controllers->variable->set('flavors', $flavors);
    }

    return array(
     'msg' => $subtheme->msg,
     'sub_theme' => $sub_theme,
    );
  }
}