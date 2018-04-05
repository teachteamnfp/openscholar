<?php
// $Id$

/**
 * Create the processor that will override the purl_domain
 */
if (!class_exists('purl_vsite_domain')) {
  /**
   *  Full domain handling for scholar, will also remove purl prefix's from alias's
   */
  class purl_vsite_domain extends purl_domain implements purl_processor {
    use VsitePathHandling;

    public $modifier_regex = '!^[a-z0-9_\.-]+$!';

    /**
     * Simply matches our 'q' (aka domain) against an allowed value.
     *
     * If none is found and $q starts with www. then try without
     */
    public function parse($valid_values, $q) {
      $path_elements = parent::parse($valid_values, $q);

      if (!count($path_elements) && strpos($q, 'www.') === 0) {
        // Removes "www"
        $q = substr($q, 4);
        $path_elements = parent::parse($valid_values, $q);
      }

      return $path_elements;
    }

    function detect($q) {
      $plugin = ctools_get_plugins('spaces', 'plugins', 'vsite');
      if ($plugin) {
        $class = ctools_plugin_get_class($plugin, 'handler');
      }

      if (isset($class) && call_user_func(array($class, 'is_excluded_path'), $q)) {
        global $base_url;
        // Matches based on global base URL
        return $base_url;
      }
      else {
        $domain = parent::detect($q);

        // Removes the port if it is 80 (some browsers don't do this by default)
        if (substr($domain, -3) == ":80") {
          $domain = substr($domain, 0, (strlen($domain) - 3));
        }

        return $domain;
      }
    }

    public function method() {
      return PURL_VSITE_DOMAIN;
    }

    public function description() {
      return t('Enter a domain registered for this site, such as "www.example.edu".');
    }

    protected function rewriteBaseUrl($url) {
      return "http://$url";
    }
  }
}

