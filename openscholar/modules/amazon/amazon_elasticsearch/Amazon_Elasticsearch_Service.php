<?php

class AmazonElasticsearchService extends DrupalApacheSolrService {

  const UPDATE_SERVLET = 'doc';
  const SEARCH_SERVLET = 'search';

  protected function _constructUrl($servlet, $params = array(), $added_query_string = NULL) {
    // PHP's built in http_build_query() doesn't give us the format Solr wants.
    $params += array(

    );
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

    $path = '';
    switch ($servlet) {
      case self::UPDATE_SERVLET:
        $path = '/documents/batch';
        break;
      case self::SEARCH_SERVLET:
        $path = '/search';
        break;
    }

    $url = $this->parsed_url;
    return $url['scheme'] . $servlet . '-' . $url['host'] . $url['port'] . $url['path'] . $path . $query_string;
  }

  /**
   * {@inheritDoc}
   */
  public function ping($timeout = 2) {
    $start = microtime(TRUE);

    if ($timeout <= 0.0) {
      $timeout = -1;
    }
    $pingUrl = 'https://cloudsearch.us-east-1.amazonaws.com';
    $content = array(
      'Action' => 'DescribeDomains',
      'Version' => '2013-01-01',
      'DomainNames.member.1' => variable_get('amazon_cloudsearch_domain'),
    );
    $query_string = $this->httpBuildQuery($content);
    $headers = $this->sign("GET", "/", $query_string);
    // Attempt a HEAD request to the solr ping url.
    $options = array(
      'method' => 'GET',
      'timeout' => $timeout,
      'headers' => $headers
    );
    $pingUrl .= '?' . $query_string;
    $response = $this->_makeHttpRequest($pingUrl, $options);

    if ($response->code == 200) {
      // Add 0.1 ms to the ping time so we never return 0.0.
      error_log(print_r($response->data, 1));
      return microtime(TRUE) - $start + 0.0001;
    }
    else {
      error_log(print_r($response, 1));
      return FALSE;
    }
  }

  /**
   * @param $verb
   * @param $uri
   * @param $query_string
   * @param string $body
   * @return mixed
   *
   * @see http://docs.aws.amazon.com/general/latest/gr/sigv4-signed-request-examples.html
   */
  protected function sign($verb, $uri, $query_string, $body = "") {
    $host = 'cloudsearch';
    $datetime = date("c", REQUEST_TIME);
    $date = date("Y-m-d", REQUEST_TIME);
    $headers = "host:$host\nx-amz-date:$datetime\n";

    $canonical_request = sprintf("%s\n%s\n%s\n%s\n%s\n%s",
      $verb,
      $uri,
      $query_string,
      $headers,
      "host,x-amz-date",
      hash("sha256", $body));

    $credential_scope = "$date/us-east1/cloudsearch/aws4_request";
    $string_to_sign = "AWS4-HMAC-SHA256\n$datetime\n$credential_scope\n"+hash("sha256", $canonical_request);

    $secret_key = variable_get('amazon_secret_key');

    // signing key
    $keyDate = hash_hmac("SHA256", $date, "AWS4-$secret_key");
    $keyRegion = hash_hmac("SHA256", 'us-east-1', $keyDate);
    $keyService = hash_hmac("SHA256", 'cloudsearch', $keyRegion);
    $keySigning = hash_hmac("SHA256", 'aws4_request', $keyService);

    // signature
    $signature = hash_hmac("SHA256", $string_to_sign, $keySigning);

    // authorization header
    $access_key = variable_get('amazon_access_key');
    $authorization_header = "AWS4-HMAC-SHA256 Credential=$access_key/$credential_scope, SignedHeaders=host;x-amz-date, Signature=$signature";

    $headers = array(
      'x-amz-date' => $datetime,
      'Authorization' => $authorization_header
    );

    return $headers;
  }
}