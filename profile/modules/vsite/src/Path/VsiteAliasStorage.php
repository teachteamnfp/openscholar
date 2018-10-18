<?php
/**
 * Created by PhpStorm.
 * User: New User
 * Date: 10/12/2018
 * Time: 4:49 PM
 */

namespace Drupal\vsite\Path;

// this is a decorator. still needs to be added to services.yml
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Path\AliasStorageInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\purl\Plugin\ModifierIndex;
use Drupal\purl\Entity\Provider;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;

class VsiteAliasStorage implements AliasStorageInterface {

  /** @var AliasStorageInterface */
  protected $storage;

  /** @var ModifierIndex */
  protected $modifierIndex;

  /** @var EntityTypeManagerInterface */
  protected $entityTypeManager;

  /** @var VsiteContextManagerInterface */
  protected $vsiteContextManager;

  function __construct(AliasStorageInterface $storage, ModifierIndex $modifierIndex, EntityTypeManagerInterface $entityTypeManager, VsiteContextManagerInterface $vsiteContextManager) {
    $this->storage = $storage;
    $this->modifierIndex = $modifierIndex;
    $this->entityTypeManager = $entityTypeManager;
    $this->vsiteContextManager = $vsiteContextManager;
  }

  /**
   * @return \Drupal\purl\Modifier[]
   */
  protected function getModifiers() {
    /** @var Provider $provider */
    $provider = $this->entityTypeManager->getStorage ('purl_provider')->load('group_purl_provider');
    return $this->modifierIndex->getProviderModifiers ($provider);
  }

  /**
   * takes the original path and translates it to a token
   * i.e. site01/about becomes [vsite:1]/about
   *
   * @param string $path
   * @return string
   */
  protected function pathToToken(string $path) {
    if (strpos($path, 'group/') !== FALSE) {
      return $path;
    }
    $modifiers = $this->getModifiers ();

    list($site,) = explode('/', trim($path, '/'));
    foreach ($modifiers as $m) {
      if ($m->getModifierKey () == $site) {
        return str_replace($site, '[vsite:'.$m->getValue().']', $path);
      }
    }

    return $path;
  }

  /**
   * Converts a vsite token into the site url
   *
   * @param string $path
   * @return string
   */
  protected function tokenToPath(string $path) {
    $modifiers = $this->getModifiers ();

    $matches = [];
    if (preg_match('|\[vsite:([\d]+)\]|', $path, $matches)) {
      $id = $matches[1];

      foreach ($modifiers as $m) {
        if ($m->getValue () == $id) {
          return str_replace($matches[0], $m->getModifierKey (), $path);
        }
      }
    }

    return $path;
  }

  /**
   * @inheritDoc
   */
  public function save ($source, $alias, $langcode = LanguageInterface::LANGCODE_NOT_SPECIFIED, $pid = NULL) {
    $alias = $this->pathToToken($alias);
    $fields = $this->storage->save($source, $alias, $langcode, $pid);
    if (!empty($fields['alias'])) {
      $fields['alias'] = $this->tokenToPath ($fields['alias']);
    }
    return $fields;
  }

  /**
   * @inheritDoc
   */
  public function load ($conditions) {
    if (!empty($conditions['alias'])) {
      $conditions['alias'] = $this->pathToToken($conditions['alias']);
    }
    $loaded = $this->storage->load($conditions);
    if ($loaded) {
      $loaded['alias'] = $this->tokenToPath ($loaded['alias']);
    }
    return $loaded;
  }

  /**
   * @inheritDoc
   */
  public function delete ($conditions) {
    if (!empty($conditions['alias'])) {
      $conditions['alias'] = $this->pathToToken($conditions['alias']);
    }
    return $this->storage->delete($conditions);
  }

  /**
   * @inheritDoc
   */
  public function preloadPathAlias ($preloaded, $langcode) {
    $output = $this->storage->preloadPathAlias ($preloaded, $langcode);

    foreach ($output as &$o) {
      $o['alias'] = $this->tokenToPath ($o['alias']);
    }

    return $output;
  }

  /**
   * @inheritDoc
   */
  public function lookupPathAlias ($path, $langcode) {
    if (strpos($path, '/group/') === FALSE) {
      $path = $this->tokenToPath ($path);
    }
    return $this->storage->lookupPathAlias ($path, $langcode);
  }

  /**
   * @inheritDoc
   *
   * This is the entry point for requests to determine what the real route is going to be
   *
   * PURL strips the modifier from the request and starts a new request with the stripped-down path
   * By the time processing gets here, there's no modifiers at all on the path at all.
   * We have to add it back on in order to detect the right entity properly
   */
  public function lookupPathSource ($path, $langcode) {
    /** @var GroupInterface $group */
    if ($group = $this->vsiteContextManager->getActiveVsite ()) {
      $path = '/[vsite:'.$group->id().']'.$path;
    }
    return $this->storage->lookupPathSource ($path, $langcode);
  }

  /**
   * @inheritDoc
   */
  public function aliasExists ($alias, $langcode, $source = NULL) {
    $alias = $this->pathToToken ($alias);
    return $this->storage->aliasExists ($alias, $langcode, $source);
  }

  /**
   * @inheritDoc
   */
  public function languageAliasExists () {
    return $this->storage->languageAliasExists ();
  }

  /**
   * @inheritDoc
   */
  public function getAliasesForAdminListing ($header, $keys = NULL) {
    $output = $this->storage->getAliasesForAdminListing ($header, $keys);
    foreach ($output as &$o) {
      $o->alias = $this->tokenToPath ($o->alias);
    }
    return $output;
  }

  /**
   * @inheritDoc
   */
  public function pathHasMatchingAlias ($initial_substring) {
    return $this->storage->pathHasMatchingAlias ($initial_substring);
  }
}