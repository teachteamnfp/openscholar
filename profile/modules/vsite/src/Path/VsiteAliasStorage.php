<?php

namespace Drupal\vsite\Path;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Path\AliasStorageInterface;
use Drupal\purl\Plugin\ModifierIndex;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;

/**
 * Wraps AliasStorage so we can replace the vsite path with an integer id.
 */
class VsiteAliasStorage implements AliasStorageInterface {

  /**
   * The AliasStorage object we're decorating.
   *
   * @var \Drupal\Core\Path\AliasStorageInterface
   */
  protected $storage;

  /**
   * PURL's list of all modifiers.
   *
   * @var \Drupal\purl\Plugin\ModifierIndex
   */
  protected $modifierIndex;

  /**
   * Manager for entities.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Manager for vsites.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * Constructor.
   */
  public function __construct(AliasStorageInterface $storage, ModifierIndex $modifierIndex, EntityTypeManagerInterface $entityTypeManager, VsiteContextManagerInterface $vsiteContextManager) {
    $this->storage = $storage;
    $this->modifierIndex = $modifierIndex;
    $this->entityTypeManager = $entityTypeManager;
    $this->vsiteContextManager = $vsiteContextManager;
  }

  /**
   * Returns an array of all purl modifiers.
   *
   * @return \Drupal\purl\Modifier[]
   *   An array of purl modifiers
   */
  protected function getModifiers() {
    /** @var \Drupal\purl\Entity\Provider $provider */
    $provider = $this->entityTypeManager->getStorage('purl_provider')->load('group_purl_provider');
    return $this->modifierIndex->getProviderModifiers($provider);
  }

  /**
   * Takes the original path and translates it to a token.
   *
   * I.e. site01/about becomes [vsite:1]/about.
   *
   * @param string $path
   *   The path with the vsite's purl.
   *
   * @return string
   *   The path with the purl replaced by a token.
   */
  protected function pathToToken(string $path) {
    if (strpos($path, 'group/') !== FALSE) {
      return $path;
    }
    $modifiers = $this->getModifiers();

    list($site,) = explode('/', trim($path, '/'));
    foreach ($modifiers as $m) {
      if ($m->getModifierKey() == $site) {
        return str_replace($site, '[vsite:' . $m->getValue() . ']', $path);
      }
    }

    return $path;
  }

  /**
   * Converts a vsite token into the site url.
   *
   * @param string $path
   *   The tokenied path.
   *
   * @return string
   *   The path with the token replaced with the vsite's purl.
   */
  protected function tokenToPath(string $path) {
    $modifiers = $this->getModifiers();

    $matches = [];
    if (preg_match('|\[vsite:([\d]+)\]|', $path, $matches)) {
      $id = $matches[1];

      foreach ($modifiers as $m) {
        if ($m->getValue() == $id) {
          return str_replace($matches[0], $m->getModifierKey(), $path);
        }
      }
    }

    return $path;
  }

  /**
   * {@inheritdoc}
   */
  public function save($source, $alias, $langcode = LanguageInterface::LANGCODE_NOT_SPECIFIED, $pid = NULL) {
    $alias = $this->pathToToken($alias);
    $fields = $this->storage->save($source, $alias, $langcode, $pid);
    if (!empty($fields['alias'])) {
      $fields['alias'] = $this->tokenToPath($fields['alias']);
    }
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function load($conditions) {
    if (!empty($conditions['alias'])) {
      $conditions['alias'] = $this->pathToToken($conditions['alias']);
    }
    $loaded = $this->storage->load($conditions);
    if ($loaded) {
      $loaded['alias'] = $this->tokenToPath($loaded['alias']);
    }
    return $loaded;
  }

  /**
   * {@inheritdoc}
   */
  public function delete($conditions) {
    if (!empty($conditions['alias'])) {
      $conditions['alias'] = $this->pathToToken($conditions['alias']);
    }
    return $this->storage->delete($conditions);
  }

  /**
   * {@inheritdoc}
   */
  public function preloadPathAlias($preloaded, $langcode) {
    $output = $this->storage->preloadPathAlias($preloaded, $langcode);

    foreach ($output as $source => &$alias) {
      $alias = $this->tokenToPath($alias);
    }

    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function lookupPathAlias($path, $langcode) {
    $output = $this->storage->lookupPathAlias($path, $langcode);
    if (strpos($path, '/group/') === FALSE) {
      $output = $this->tokenToPath($output);
    }
    return $output;
  }

  /**
   * {@inheritdoc}
   *
   * This is the entry point for requests to determine the real route.
   *
   * PURL strips the modifier from the request and starts a new request
   *   with the stripped-down path. By the time processing gets here, there's
   *   no modifiers at all on the path at all. We have to add it back on in
   *   order to detect the right entity properly.
   */
  public function lookupPathSource($path, $langcode) {
    /** @var \Drupal\group\Entity\GroupInterface $group */
    if ($group = $this->vsiteContextManager->getActiveVsite()) {
      $path = '/[vsite:' . $group->id() . ']' . $path;
    }
    return $this->storage->lookupPathSource($path, $langcode);
  }

  /**
   * {@inheritdoc}
   */
  public function aliasExists($alias, $langcode, $source = NULL) {
    $alias = $this->pathToToken($alias);
    return $this->storage->aliasExists($alias, $langcode, $source);
  }

  /**
   * {@inheritdoc}
   */
  public function languageAliasExists() {
    return $this->storage->languageAliasExists();
  }

  /**
   * {@inheritdoc}
   */
  public function getAliasesForAdminListing($header, $keys = NULL) {
    $output = $this->storage->getAliasesForAdminListing($header, $keys);
    foreach ($output as &$o) {
      $o->alias = $this->tokenToPath($o->alias);
    }
    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function pathHasMatchingAlias($initial_substring) {
    return $this->storage->pathHasMatchingAlias($initial_substring);
  }

}
