<?php

class AmazonElasticsearchService extends DrupalApacheSolrService {

  const UPDATE_SERVLET = 'doc';
  const SEARCH_SERVLET = 'search';

  protected function _constructUrl($servlet, $params = array(), $added_query_string = NULL) {
    // PHP's built in http_build_query() doesn't give us the format Solr wants.
    $query_string = $this->httpBuildQuery($params);

    if ($query_string) {
      $query_string = '?' . $query_string;
      if ($added_query_string) {
        $query_string = $query_string . '&' . $added_query_string;
      }
    }
    elseif ($added_query_string) {
      $query_string = '?' . $added_query_string;
    }

    $url = $this->parsed_url;
    return $url['scheme'] . $servlet . '-' . $url['host'] . $url['port'] . $url['path'] . $query_string;
  }
}