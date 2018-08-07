<?php
/**
 * Created by PhpStorm.
 * User: New User
 * Date: 8/7/2018
 * Time: 3:27 PM
 */

namespace Drupal\vsite\Path;

use \Drupal\core\Path\AliasStorage;
use Drupal\Core\Database\Connection;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageInterface;


class VsiteAliasStorage extends AliasStorage {

  /** @var \Drupal\core\Path\AliasStorage */
  protected $alias_storage;

  public function __construct(AliasStorage $aliasStorage, Connection $connection, ModuleHandlerInterface $module_handler) {
    $this->alias_storage = $aliasStorage;
    parent::__construct ($connection, $module_handler);
  }

  protected function parseAlias($alias) {
    return $alias;
  }

  public function save($source, $alias, $langcode = LanguageInterface::LANGCODE_NOT_SPECIFIED, $pid = NULL) {
    // mangle the alias however we please
    $alias = $this->parseAlias($alias);

    return parent::save($source, $alias, $langcode, $pid);
  }

  public function aliasExists($alias, $langcode, $source = NULL) {
    $alias = $this->parseAlias ($alias);

    return parent::aliasExists ($alias, $langcode, $source);
  }

  public function lookupPathAlias($path, $langcode) {
    $alias = parent::lookupPathAlias ($path, $langcode);

    return $this->parseAlias ($alias);
  }

  public function lookupPathSource($path, $langcode) {
    $alias = $this->parseAlias ($path);

    return parent::lookupPathSource ($alias, $langcode);
  }

}