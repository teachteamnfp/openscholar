<?php

namespace Drupal\os_twitter_pull;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class TwitterPullConfig.
 *
 * @package Drupal\os_twitter_pull
 */
class TwitterPullConfig {

  private $settings = [];
  private $cacheLength = 20 * 60;

  /**
   * TwitterPullConfig constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   Drupal config.
   */
  public function __construct(ConfigFactoryInterface $config) {
    $os_twitter_pull_settings = $config->get('os_twitter_pull.settings');
    $this->settings = $os_twitter_pull_settings->get();
  }

  /**
   * Set settings array.
   */
  public function setSettings(array $settings): void {
    $this->settings = $settings;
  }

  /**
   * Get settings array.
   */
  public function getSettings(): array {
    return $this->settings;
  }

  /**
   * Get cache length in seconds.
   */
  public function getCacheLength() {
    return $this->cacheLength;
  }

}
