<?php

namespace Drupal\os_theme_preview;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
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
   * Session key.
   */
  public const SESSION_KEY = 'os_theme_preview';

  /**
   * Default preview duration - in minutes.
   */
  public const PREVIEW_DURATION = 5;

  /**
   * Current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Date time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $dateTime;

  /**
   * Helper constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack.
   * @param \Drupal\Component\Datetime\TimeInterface $date_time
   *   Date time service.
   */
  public function __construct(RequestStack $request_stack, TimeInterface $date_time) {
    $this->request = $request_stack->getCurrentRequest();
    $this->dateTime = $date_time;
  }

  /**
   * {@inheritdoc}
   */
  public function startPreviewMode($theme): void {
    \setrawcookie(self::SESSION_KEY, \rawurlencode($theme), $this->dateTime->getRequestTime() + self::PREVIEW_DURATION * 60, '/site01');
  }

  /**
   * {@inheritdoc}
   */
  public function getPreviewedTheme(): ?string {
    return $this->request->cookies->get(self::SESSION_KEY);
  }

  /**
   * {@inheritdoc}
   */
  public function stopPreviewMode(): void {
    $this->request->cookies->remove(self::SESSION_KEY);
  }

}
