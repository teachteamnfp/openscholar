<?php

namespace Drupal\vsite_module_test\Controller;


use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\PathProcessor\PathProcessorManager;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Url;
use Drupal\purl\Context;
use Drupal\purl\ContextHelper;
use Drupal\purl\MatchedModifiers;
use Drupal\purl\PathProcessor\PurlContextOutboundPathProcessor;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;

class LinkTestController extends ControllerBase {

  public function linkTest() {

    /** @var VsiteContextManagerInterface $vsiteContextManager */
    $vsiteContextManager = \Drupal::service('vsite.context_manager');

    $metadata = new BubbleableMetadata();
    /** @var PathProcessorManager $pathProcessor */
    $pathProcessor = \Drupal::service('path_processor_manager');
    $options = [];
    $testStr = $pathProcessor->processOutbound('/link-test', $options, null, $metadata);

    /** @var MatchedModifiers $matchedModifiers */
    $matchedModifiers = \Drupal::service('purl.matched_modifiers');

    /** @var ContextHelper $contextHelper */
    $contextHelper = \Drupal::service('purl.context_helper');
    $contexts = $matchedModifiers->createContexts();
    /** @var Context $context */
    $context = reset($contexts);
    $details = [
      'method_class' => get_class($context->getMethod()),
      'stages' => $context->getMethod()->getStages(),
      'action' => $context->getAction(),
      'modifier' => $context->getModifier()
    ];
    $opt2 = [];
    $testStr2 = $contextHelper->processOutbound($contexts, '/link-test', $opt2);
    return [
      'vsite_active' => [
        '#type' => 'markup',
        '#markup' => $vsiteContextManager->getActiveVsite() ? 'vsite active': 'vsite not active',
      ],
      'url' => [
        '#type' => 'markup',
        '#markup' => $testStr
      ],
      'options' => [
        '#type' => 'markup',
        '#markup' => 'Options: '.(isset($options['purl_context']) ? 'purl_context is set' : 'purl_context is not set').' '.gettype($options['purl_context'])
      ],
      'contexts' => [
        '#type' => 'markup',
        '#markup' => print_r($details, 1)
      ],
      'helper' => [
        '#type' => 'markup',
        '#markup' => 'Just the context helper: '.$testStr2
      ]
    ];
  }

}
