<?php

namespace Drupal\os_mailchimp\Controller;

/**
 * @file
 * OsMailChimpModalController class.
 */

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilder;
use Drupal\os_mailchimp\Form\OsMailChimpSignupForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class OsMailChimpModalController.
 */
class OsMailChimpModalController extends ControllerBase {

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilder
   */
  protected $formBuilder;

  /**
   * The ModalFormExampleController constructor.
   *
   * @param \Drupal\Core\Form\FormBuilder $formBuilder
   *   The form builder.
   */
  public function __construct(FormBuilder $formBuilder) {
    $this->formBuilder = $formBuilder;
  }

  /**
   * {@inheritdoc}
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The Drupal service container.
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder')
    );
  }

  /**
   * Modal response.
   *
   * @param string $list_id
   *   Selected MailChimp list id.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   AjaxResponse.
   */
  public function modal($list_id) {
    $content = $this->getModalContent($list_id);

    $options = [
      'dialogClass' => 'popup-dialog-class',
      'width' => 300,
    ];
    $response = new AjaxResponse();
    $response->addCommand(new OpenModalDialogCommand(t('Mailchimp subscribe'), $content, $options));

    return $response;
  }

  /**
   * Get Modal content.
   *
   * @param string $list_id
   *   Selected Mailchimp list id.
   *
   * @return array|\Drupal\Core\StringTranslation\TranslatableMarkup
   *   Rendered form or service error.
   */
  private function getModalContent($list_id) {
    $list = mailchimp_get_list($list_id);

    if (empty($list)) {
      return $this->t('The subscription service is currently unavailable. Please check again later.');
    }

    return $this->formBuilder->getForm('\Drupal\os_mailchimp\Form\OsMailChimpSignupForm', $list);
  }

}
