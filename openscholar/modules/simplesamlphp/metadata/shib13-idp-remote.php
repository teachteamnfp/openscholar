<?php
/**
 * SAML 1.1 remote IdP metadata for simpleSAMLphp.
 *
 * Remember to remove the IdPs you don't use from this file.
 *
 * See: https://rnd.feide.no/content/idp-remote-metadata-reference
 */

$metadata['https://stage.fed.huit.harvard.edu/idp/shibboleth'] = array (
  'entityid' => 'https://stage.fed.huit.harvard.edu/idp/shibboleth',
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
      'Location' => 'https://key-idp.stage.iam.harvard.edu/idp/profile/SAML2/POST/SSO',
    ),
    1 => 
    array (
      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST-SimpleSign',
      'Location' => 'https://key-idp.stage.iam.harvard.edu/idp/profile/SAML2/POST-SimpleSign/SSO',
    ),
    2 => 
    array (
      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
      'Location' => 'https://key-idp.stage.iam.harvard.edu/idp/profile/SAML2/Redirect/SSO',
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
      'X509Certificate' => 'MIIDXDCCAkSgAwIBAgIUX70XzpDd3czVVVgMHAMjrVWZe50wDQYJKoZIhvcNAQELBQAwKDEmMCQG A1UEAwwda2V5LWlkcC5zdGFnZS5pYW0uaGFydmFyZC5lZHUwHhcNMTYwNjA5MTQwOTQyWhcNMzYw NjA5MTQwOTQyWjAoMSYwJAYDVQQDDB1rZXktaWRwLnN0YWdlLmlhbS5oYXJ2YXJkLmVkdTCCASIw DQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBAJtwvk/OboUMGy3ojXXB3MiLwkpzEyPvnHhNDhBd +aCJ8tIvxSgLe0r+jnjAKGoKjxJ/VqGBYgHNn/8oUNNOl9iPPOZSXUw9wsQqb2mWmPsVim/+9YVv +w+hn/P/dAk7NZU7bDMgn2kgWmm7osyzNYecJMyMLkZmwahA8/m8RRVSZBILMM8b/9cWOTN2vzFI kiOpQpjegZ6dw+gUUE/DzAVCDct1O03UmydHKRmT97xV6JpWfiHC1/RzFxgLcdRpuoBD2JqycA3Z 9EkVAV8SBzAbKJxABGxPSFyA6YUKyY9g/Cvd0KFLgLnDB8NFRU9WW1fWco10l1g1giTE6smYWakC AwEAAaN+MHwwHQYDVR0OBBYEFAmQGAp65E1Xy43rs5GIV9TF0l9gMFsGA1UdEQRUMFKCHWtleS1p ZHAuc3RhZ2UuaWFtLmhhcnZhcmQuZWR1hjFodHRwczovL3N0YWdlLmZlZC5odWl0LmhhcnZhcmQu ZWR1L2lkcC9zaGliYm9sZXRoMA0GCSqGSIb3DQEBCwUAA4IBAQAq4yThR//VPPmRySI6I0EvUBt4 Iv+K5HfkLn8LDRpMSa4hRXSm+ZLSULqxvqxony1gY7xviQdu9sVp2fnE3YjTRFvEzRDk/emsQOsC P+FQvUFJKmokKT3aVupuP8snmwOgEbmE0Q3upNhZCr3Ygs8ZDpCG6Ol99kFjedHATOYqSvpd+VOW HdeAKj8lyiy+Z0QQjpRNfZluSqUpfthjSeXJXFqq2ABnQZmdQNG+5wfLOcnRJu3UnBqk1J2/daqT g67kGKBA4IS+MwhyF1VSb3UxCCwQJk56rcFy8RoFh8KStsOwj5hY0gMCPSFWnfZ4KNc9nYGQV25a +SAZT1uCpunZ',
    ),
  ),
  'scope' => 
  array (
    0 => 'harvard.edu',
  ),
);
