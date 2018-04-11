<?php
/**
 * SAML 1.1 remote IdP metadata for simpleSAMLphp.
 *
 * Remember to remove the IdPs you don't use from this file.
 *
 * See: https://rnd.feide.no/content/idp-remote-metadata-reference
 */

$metadata['https://fed.huit.harvard.edu/idp/shibboleth'] = array (
  'entityid' => 'https://fed.huit.harvard.edu/idp/shibboleth',
  'name' => 
  array (
    'en' => 'Harvard University',
  ),
  'description' => 
  array (
    'en' => 'Harvard College',
  ),
  'OrganizationName' => 
  array (
    'en' => 'Harvard College',
  ),
  'OrganizationDisplayName' => 
  array (
    'en' => 'Harvard University',
  ),
  'url' => 
  array (
    'en' => 'http://www.harvard.edu/',
  ),
  'OrganizationURL' => 
  array (
    'en' => 'http://www.harvard.edu/',
  ),
  'contacts' => 
  array (
    0 => 
    array (
      'contactType' => 'support',
      'givenName' => 'IdP Support Team',
      'emailAddress' => 
      array (
        0 => 'idp_support@harvard.edu',
      ),
    ),
    1 => 
    array (
      'contactType' => 'technical',
      'givenName' => 'IdP Support Team',
      'emailAddress' => 
      array (
        0 => 'idp_support@harvard.edu',
      ),
    ),
    2 => 
    array (
      'contactType' => 'administrative',
      'givenName' => 'IdP Support Team',
      'emailAddress' => 
      array (
        0 => 'idp_support@harvard.edu',
      ),
    ),
  ),
  'metadata-set' => 'saml20-idp-remote',
  'SingleSignOnService' => 
  array (
    0 => 
    array (
      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
      'Location' => 'https://key-idp.iam.harvard.edu/idp/profile/SAML2/POST/SSO',
    ),
    1 => 
    array (
      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST-SimpleSign',
      'Location' => 'https://key-idp.iam.harvard.edu/idp/profile/SAML2/POST-SimpleSign/SSO',
    ),
    2 => 
    array (
      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
      'Location' => 'https://key-idp.iam.harvard.edu/idp/profile/SAML2/Redirect/SSO',
    ),
  ),
  'SingleLogoutService' => 
  array (
  ),
  'ArtifactResolutionService' => 
  array (
  ),
  'keys' => 
  array (
    0 => 
    array (
      'encryption' => false,
      'signing' => true,
      'type' => 'X509Certificate',
      'X509Certificate' => '
                        MIIDRDCCAiygAwIBAgIUdXfRRGHcHe0kqyWjt9pzdElroS8wDQYJKoZIhvcNAQEL
                        BQAwIjEgMB4GA1UEAwwXa2V5LWlkcC5pYW0uaGFydmFyZC5lZHUwHhcNMTYwNjA5
                        MjAyNDEyWhcNMzYwNjA5MjAyNDEyWjAiMSAwHgYDVQQDDBdrZXktaWRwLmlhbS5o
                        YXJ2YXJkLmVkdTCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBAIr/Hd3R
                        cDBNh5C2hi9GicY0LCOJDW34ndazmFZy5djYajqxoy7+RPDZwOlJdjIq7hpzxKvD
                        K59dSLha60XfSSzqKpnQ8S/jcvpKnpW9UStMR7lGaIUTLSAEqHvguzR7iQt3wuKD
                        FxGPxvQeO/z32F0wvmbmemI1XhLSIo1aJAOujsAFPex1K3QYTkBQDOiDqd9gatr9
                        W163rx+Nd7BHpXaUWGQcLpkM7iMH9lgmWg4F4yvJLOV72ygOwb/YP7bnVog2B+VM
                        AuX//TVhMHc3d4QOMS7zDDKexbG1kdlBuFrawV5betGnIywEFE9Du3RCH61Zhppd
                        rhtfP+ie2tbqFlUCAwEAAaNyMHAwHQYDVR0OBBYEFHKP/f1hfPpCGc1DKMtXlWom
                        aAV7ME8GA1UdEQRIMEaCF2tleS1pZHAuaWFtLmhhcnZhcmQuZWR1hitodHRwczov
                        L2ZlZC5odWl0LmhhcnZhcmQuZWR1L2lkcC9zaGliYm9sZXRoMA0GCSqGSIb3DQEB
                        CwUAA4IBAQAFLHg4EBEDDeUhQi+QRVgbgmkiKkPSiZLeeDbmaWyELEr5kGye7Q6Z
                        wcXDK3qHOQc6GRhBw13A7YqCuuhjgxD51hzlPvOy6HAmPkaqWuNfXl2QMxb1LNkY
                        0WJiEHLOZvnpItV5mTgszzlTfg/rj1l8IfsBSYfSZjePIk7IIW4y0PsQG+mOCz4D
                        jrZDSJtefq5iaDcZKHGmAOex9osIjM2JJ7hUV52YV/ct+Ha6q+oBnzUo62lVGOsx
                        zyNYEoUX1Q25f0lm72MYS7M4LifZ4sW3fF9OZClDelj2VcqAWHeMQhjkbtMyrTc5
                        59SJSzhAtL9UdzpgB0Poym6nF34EgDtl
                    ',
    ),
  ),
  'scope' => 
  array (
    0 => 'harvard.edu',
  ),
);