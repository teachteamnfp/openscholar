<?php

/**
 * Implements hook_drush_command().
 */
function os_search_drush_command() {
    if (function_exists('search_api_solr_site_hash')) {
        $items['os-search-search-api-solr-hash-reset'] = array(
        'description' => t('Reset the Search API\'s Apache Solr hash.')
        );
    }
    return $items;
}

/**
 * Callback for 'os-search-search-api-solr-hash-reset' command.
 */
function drush_os_search_search_api_solr_hash_reset() {
    drupal_set_message(t('Resetting Search API\'s Apache Solr hash'));
    drupal_set_message(t('Old indexed items will be lost and all items for `!site` will have to be reindexed.',
        array('!site' => conf_path()))
    );
    // Clear the old hash.
    variable_set('search_api_solr_site_hash', FALSE);
    // Set the new hash.
    $hash = search_api_solr_site_hash();
    drupal_set_message(t('Hash set to \'!hash\'', array('!hash' => $hash)));
}