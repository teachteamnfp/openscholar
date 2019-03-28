<?php

namespace Drupal\os_theme_preview;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Contains helpers for theme preview.
 *
 * Most probably this would be renamed to be something better, once the scope
 * becomes more clear.
 */
final class Helper implements HelperInterface {

  use StringTranslationTrait;

  /**
   * Current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Helper constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack.
   */
  public function __construct(RequestStack $request_stack) {
    $this->request = $request_stack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public function startPreviewMode($theme) {
    /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface|null $session */
    $session = $this->request->getSession();

    if (!$session) {
      throw new ThemePreviewException($this->t('Preview could not be started.'));
    }

    $session->set('os_theme_preview', $theme);
  }

}
