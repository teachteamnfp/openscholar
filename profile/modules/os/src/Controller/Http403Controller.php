<?php

namespace Drupal\os\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

/**
 * Alters access denied page content.
 */
class Http403Controller extends ControllerBase {

  /**
   * Renders access denied page content.
   *
   * @return array
   *   Renderable Drupal structure.
   */
  public function render(): array {
    $message = $this->t('Sorry, you are not authorized to access this page.<br />Please contact the site owner to gain access.');

    if ($this->currentUser()->isAnonymous()) {
      $message = $this->t('This website or page content is accessible to authorized users. For access, please <a href="@url">log in here.</a>', [
        '@url' => Url::fromRoute('user.login', [], [
          'query' => $this->getRedirectDestination()->getAsArray(),
        ])->toString(),
      ]);
    }

    return [
      '#markup' => $message,
    ];
  }

}
