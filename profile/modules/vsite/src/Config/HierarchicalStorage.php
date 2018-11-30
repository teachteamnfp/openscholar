<?php

namespace Drupal\vsite\Config;

use Drupal\Core\Config\StorageInterface;

/**
 * Allows multiple StorageInterfaces to be stacked.
 */
class HierarchicalStorage implements HierarchicalStorageInterface {

  const GLOBAL_STORAGE = INF;

  /**
   * @var array
   * Each value is in the format [StorageInterface storage, int weight]
   */
  protected $storages = [];

  /**
   * Constructor.
   */
  public function __construct(StorageInterface $storage) {
    $this->storages[] = [
      'storage' => $storage,
      'weight' => self::GLOBAL_STORAGE,
    ];
  }

  /**
   * @inheritdoc
   */
  public function addStorage(StorageInterface $s, $weight) {
    $this->storages[] = [
      'storage' => $s,
      'weight' => $weight,
    ];

    usort($this->storages, function ($a, $b) {
      if ($a['weight'] == $b['weight']) {
        return 0;
      }
      return ($a['weight'] < $b['weight']) ? -1 : 1;
    });
  }

  /**
   * Iterate over every storage and call the function given.
   */
  protected function iterate(callable $func) {
    foreach ($this->storages as $s) {
      /** @var \Drupal\Core\Config\StorageInterface $store */
      $store = $s['storage'];
      $output = $func($store);
      if (!is_null($output)) {
        return $output;
      }
    }
    return FALSE;
  }

  /**
   * @inheritdoc
   */
  public function exists($name) {
    return $this->iterate(function (StorageInterface $store) use ($name) {
      return $store->exists($name);
    });
  }

  /**
   * @inheritdoc
   */
  public function read($name) {
    $output = $this->iterate(function (StorageInterface $store) use ($name) {
      $output = $store->read($name);
      if (!is_null($output)) {
        return $output;
      }
    });
    return $output;
  }

  /**
   * @inheritdoc
   */
  public function readMultiple(array $names) {
    $output = [];
    foreach ($this->storages as $s) {
      /** @var \Drupal\Core\Config\StorageInterface $store */
      $store = $s['storage'];
      $output += $store->readMultiple($names);
    }

    return $output;
  }

  /**
   * @inheritdoc
   *
   * We always write to the bottom-most storage
   */
  public function write($name, array $data) {
    /** @var \Drupal\Core\Config\StorageInterface $store */
    $store = $this->storages[0]['storage'];
    $store->write($name, $data);
  }

  /**
   * @inheritdoc
   */
  public function delete($name) {
    /** @var \Drupal\Core\Config\StorageInterface $store */
    $store = $this->storages[0]['storage'];
    $store->delete($name);
  }

  /**
   * @inheritdoc
   */
  public function rename($name, $new_name) {
    /** @var \Drupal\Core\Config\StorageInterface $store */
    $store = $this->storages[0]['storage'];
    $store->rename($name, $new_name);
  }

  /**
   * @inheritdoc
   */
  public function encode($data) {
    /** @var \Drupal\Core\Config\StorageInterface $store */
    $store = end($this->storages)['storage'];
    return $store->encode($data);
  }

  /**
   * @inheritdoc
   */
  public function decode($raw) {
    /** @var \Drupal\Core\Config\StorageInterface $store */
    $store = end($this->storages)['storage'];
    return $store->decode($raw);
  }

  /**
   * @inheritdoc
   */
  public function listAll($prefix = '') {
    $output = [];
    foreach ($this->storages as $s) {
      /** @var \Drupal\Core\Config\StorageInterface $store */
      $store = $s['storage'];
      $output += $store->listAll($prefix);
    }

    return $output;
  }

  /**
   * @inheritdoc
   */
  public function deleteAll($prefix = '') {
    /** @var \Drupal\Core\Config\StorageInterface $store */
    $store = $this->storages[0]['storage'];
    $store->deleteAll($prefix);
  }

  /**
   * @inheritdoc
   */
  public function createCollection($collection) {
    /** @var \Drupal\Core\Config\StorageInterface $store */
    $store = end($this->storages)['storage'];
    return $store->createCollection($collection);
  }

  /**
   * @inheritdoc
   */
  public function getAllCollectionNames() {
    /** @var \Drupal\Core\Config\StorageInterface $store */
    $store = end($this->storages)['storage'];
    return $store->getAllCollectionNames();
  }

  /**
   * @inheritdoc
   */
  public function getCollectionName() {
    /** @var \Drupal\Core\Config\StorageInterface $store */
    $store = $this->storages[0]['storage'];
    return $store->getCollectionName();
  }

}
