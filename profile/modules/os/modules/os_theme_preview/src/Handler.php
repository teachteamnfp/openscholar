<?php

namespace Drupal\os_theme_preview;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Handles theme preview operations.
 */
final class Handler implements HandlerInterface {

  use StringTranslationTrait;

  public const SESSION_KEY = 'os_theme_preview';

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
  public function startPreviewMode($theme, $vsite_id): void {
    /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface|null $session */
    $session = $this->request->getSession();

    if (!$session) {
      throw new ThemePreviewException($this->t('Preview could not be started.'));
    }

    $session->set(self::SESSION_KEY, new ThemePreview($theme, (int) $vsite_id));
  }

  /**
   * {@inheritdoc}
   */
  public function getPreviewedThemeData(): ?ThemePreview {
    /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface|null $session */
    $session = $this->request->getSession();

    if (!$session) {
      return NULL;
    }

    /** @var \Drupal\os_theme_preview\ThemePreview|null $current_preview_theme */
    $current_preview_theme = $session->get(self::SESSION_KEY);

    return $current_preview_theme;
  }

  /**
   * {@inheritdoc}
   */
  public function stopPreviewMode(): void {
    /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface|null $session */
    $session = $this->request->getSession();

    if (!$session) {
      throw new ThemePreviewException($this->t('Could not stop preview mode.'));
    }

    $session->remove(self::SESSION_KEY);
  }

}
