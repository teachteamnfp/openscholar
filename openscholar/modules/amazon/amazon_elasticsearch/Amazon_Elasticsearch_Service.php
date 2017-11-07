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
      'DomainNames.member.1' => variable_get('amazon_cloudsearch_domain', 'openscholar-test'),
      'Version' => '2013-01-01',
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
      $data = $response->data;
      $matches = array();
      if (preg_match('|<Message>([^<]*)</Message>|', $data, $matches)) {
        drupal_set_message("<pre>".($matches[0])."</pre>", 'error');
      }
      return FALSE;
    }
  }

  /**
   * @param $verb         - HTTP Verb (GET, POST, etc)
   * @param $uri          - If you don't know, then its "/"
   * @param $query_string - A normal http query string, arguments in alphabetical order
   * @param string $body  - The body of the http request you're sending. Not needed for GET requests
   * @return mixed        - The http headers to be added to the request
   *
   * @see http://docs.aws.amazon.com/general/latest/gr/sigv4-signed-request-examples.html
   */
  protected function sign($verb, $uri, $query_string, $body = "") {
    $host = 'cloudsearch.us-east-1.amazonaws.com';
    $datetime = gmdate("Ymd\THis\Z", REQUEST_TIME);
    $date = gmdate("Ymd", REQUEST_TIME);
    $headers = "host:$host\nx-amz-date:$datetime\n";

    $canonical_request = sprintf("%s\n%s\n%s\n%s\n%s\n%s",
      $verb,
      $uri ? $uri : '/',
      $query_string,
      $headers,
      "host;x-amz-date",
      hash("sha256", $body));
    drupal_set_message("<pre>Canonical string: \n$canonical_request</pre>");

    $credential_scope = "$date/us-east-1/cloudsearch/aws4_request";
    $string_to_sign = "AWS4-HMAC-SHA256\n$datetime\n$credential_scope\n".hash("sha256", $canonical_request);
    drupal_set_message(nl2br("String to sign\n$string_to_sign"));

    $secret_key = variable_get('amazon_secret_key');

    // signing key
    $keyDate = hash_hmac("SHA256", $date, "AWS4$secret_key")."\n";
    $keyRegion = hash_hmac("SHA256", 'us-east-1', $keyDate)."\n";
    $keyService = hash_hmac("SHA256", 'cloudsearch', $keyRegion)."\n";
    $keySigning = hash_hmac("SHA256", 'aws4_request', $keyService)."\n";

    // signature
    $signature = hash_hmac("SHA256", $string_to_sign, $keySigning)."\n";

    // authorization header
    $access_key = variable_get('amazon_access_key');
    $authorization_header = "AWS4-HMAC-SHA256 Credential=$access_key/$credential_scope, SignedHeaders=host;x-amz-date, Signature=$signature";

    $headers = array(
      'x-amz-date' => $datetime,
      'Authorization' => $authorization_header
    );

    return $headers;
  }

  /**
   * Amazon demands that the query arguments be in alphabetical order.
   * It does not mention this anywhere, you just have to infer it from error messages
   *
   * {@inheritDoc}
   */
  protected function httpBuildQuery($query) {
    ksort($query);
    return parent::httpBuildQuery($query);
  }
}