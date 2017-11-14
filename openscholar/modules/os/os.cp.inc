<?php

/**
 * Implements hook_cp_settings().
 *
 * Add the option to choose between Infinite scrolling or Pagination display for long list of content.
 */
function os_cp_settings() {
  $setting = array();
  $setting['long_list_content_pagination'] = array(
    'group' => array(
      '#title' => t('Long List Display'),
      '#id' => 'long-list-content-pagination',
      '#weight' => 40,
      '#help_link' => l(t('Learn more about Long list display'), variable_get('help_long_list_url'), array('attributes' => array('target' => '_blank')))
    ),
    'form' => array(
      '#type' => 'radios',
      '#prefix' => '<label class="margin-bottom settings-form-heading">Choose how long lists of content will display</label>',
      '#default_value' => variable_get('long_list_content_pagination', 'infinite_scroll'),
      '#options' => array('infinite_scroll' => 'Infinite scrolling', 'pager' => 'Pagination'),
      '#description' => t('Pagination applies only to Blog, Links, News, FAQs, Publications and Profiles.')
    ),
  );

  return $setting;
}