<?php

namespace Drupal\vsite\Plugin\Purl\Method;

use Drupal\purl\Plugin\Purl\Method\MethodAbstract;
use Drupal\purl\Plugin\Purl\Method\MethodInterface;
use Drupal\purl\Plugin\Purl\Method\PreGenerateHookInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Site\Settings;

/**
 * Method for handling domain based vsites.
 *
 * @PurlMethod(
 *     id="vsite_domain",
 *     name="Vsite Domain",
 *     stages={
 *        Drupal\purl\Plugin\Purl\Method\MethodInterface::STAGE_PROCESS_OUTBOUND,
 *        Drupal\purl\Plugin\Purl\Method\MethodInterface::STAGE_PRE_GENERATE
 *     }
 * )
 */
class VsiteDomainMethod extends MethodAbstract implements MethodInterface, ContainerAwareInterface, PreGenerateHookInterface {
  use ContainerAwareTrait;

  /**
   * Return true if the modifier matches the current domain.
   */
  public function contains(Request $request, $modifier) {
    $baseHost = $this->getBaseHost();

    if (!$baseHost) {
      return FALSE;
    }

    $host = $request->getHost();

    if ($host === $this->getBaseHost()) {
      return FALSE;
    }

    return $modifier == $request->getHost();
  }

  /**
   * Return the base host for the install.
   */
  private function getBaseHost() {
    // Retrieve this from request context.
    return Settings::get('purl_base_domain');
  }

  /**
   * Return the request context.
   *
   * @return \Drupal\Core\Routing\RequestContext
   *   The Current Request Context
   */
  private function getRequestContext() {
    return $this->container->get('router.request_context');
  }

  /**
   * Enter the purl context for this url.
   */
  public function enterContext($modifier, $path, array &$options) {
    $baseHost = $this->getBaseHost();

    // Can't do anything if this is not set.
    if (!$baseHost) {
      return NULL;
    }

    $currentHost = isset($options['host']) ? $options['host'] : $this->getRequestContext()->getHost();
    if (empty($currentHost)) {
      return NULL;
    }

    $domain = FALSE;
    if ($modifier == $currentHost) {
      $domain = $modifier;
    }

    $options['absolute'] = TRUE;

    if ($domain) {
      $options['host'] = $domain;
    }
    else {
      $options['host'] = $baseHost;
    }

    return $path;
  }

  /**
   * Exit the purl context for this url.
   */
  public function exitContext($modifier, $path, array &$options) {

    $baseHost = $this->getBaseHost();

    // Can't do anything if this is not set.
    if (!$baseHost) {
      return NULL;
    }

    $currentHost = isset($options['host']) ? $options['host'] : $this->getRequestContext()->getHost();
    if (empty($currentHost)) {
      return NULL;
    }

    $domain = FALSE;
    if ($modifier == $currentHost) {
      $domain = $modifier;
    }

    if ($domain) {
      $options['absolute'] = TRUE;
      $options['host'] = $baseHost;
    }

    return $path;
  }

  /**
   * PreGenerate the enter context.
   */
  public function preGenerateEnter($modifier, $name, &$parameters, &$options, $collect_bubblable_metadata = FALSE) {
    $this->enterContext($modifier, '', $options);
  }

  /**
   * PreGenerate the exit context.
   */
  public function preGenerateExit($modifier, $name, &$parameters, &$options, $collect_bubblable_metadata = FALSE) {
    $this->exitContext($modifier, '', $options);
  }

}
