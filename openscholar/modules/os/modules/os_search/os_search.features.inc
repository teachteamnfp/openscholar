<?php
/**
 * @file
 * so_search.features.inc
 */

/**
 * Implements hook_default_search_api_server().
 
function so_search_default_search_api_server() {
  $items = array();

  $items['solr_server'] = entity_import('search_api_server', '{
    "name" : "Solr Server",
    "machine_name" : "solr_server",
    "description" : "",
    "class" : "search_api_solr_service",
    "options" : {
      "http_user" : "",
      "http_pass" : "",
      "excerpt" : 0,
      "retrieve_data" : 0,
      "highlight_data" : 0,
      "name" : "Solr Server",
      "description" : "",
      "host" : "localhost",
      "port" : "8983",
      "path" : "solr"
    },
    "enabled" : "1",
    "rdf_mapping" : []
  }');

  return $items;
}
*/