<?php

namespace Drupal\cp_users\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\PrependCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Ajax\RestripeCommand;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\user\UserInterface;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Handles the form to confirm to remove a user from a site.
 */
class CpUsersRemoveForm extends ConfirmFormBase {

  /**
   * Vsite Context Manager.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * The user being removed from the site.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * Messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('vsite.context_manager'),
      $container->get('messenger'),
      $container->get('renderer')
    );
  }

  /**
   * Constructor.
   *
   * @param \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsiteContextManager
   *   Vsite Context Manager Interface.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messenger Interface.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   Renderer Interface.
   */
  public function __construct(VsiteContextManagerInterface $vsiteContextManager, MessengerInterface $messenger, RendererInterface $renderer) {
    $this->vsiteContextManager = $vsiteContextManager;
    $this->messenger = $messenger;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t("Are you sure you want to remove this user from your site? This will not delete the user's account.");
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return Url::fromRoute('cp.users')->toString();
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cp-users-remove-member';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, UserInterface $user = NULL) {
    $this->user = $user;

    $form = parent::buildForm($form, $form_state);

    $form['actions']['submit']['#attributes']['class'][] = 'use-ajax';
    unset($form['actions']['submit']['#submit']);
    $form['actions']['submit']['#ajax'] = [
      'callback' => [$this, 'submitForm'],
      'event' => 'click',
    ];
    $form['actions']['cancel']['#attributes']['class'][] = 'use-ajax';
    unset($form['actions']['cancel']['#submit']);
    $form['actions']['cancel']['#ajax'] = [
      'callback' => [$this, 'closeModal'],
      'event' => 'click',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $group = $this->vsiteContextManager->getActiveVsite();
    if (!$group) {
      $response->setStatusCode(403, 'Forbidden');
    }
    else {
      $response->addCommand(new CloseModalDialogCommand());
      $response->addCommand(new RemoveCommand('[data-user-id="' . $this->user->id() . '"]'));
      $response->addCommand(new RestripeCommand('.cp-manager-user-content'));

      if ($group = $this->vsiteContextManager->getActiveVsite()) {
        $group->removeMember($this->user);
        $this->messenger->addMessage($this->t('Member <em>@user</em> has been removed from <em>@site</em>', ['@user' => $this->user->getAccountName(), '@site' => $group->label()]));
        $status_messages = [
          '#type' => 'status_messages',
        ];
        $messages = $this->renderer->renderRoot($status_messages);
        $response->addCommand(new PrependCommand('.region-content', $messages));
      }
    }
    return $response;
  }

  /**
   * Closes the modal.
   */
  public function closeModal() {
    $response = new AjaxResponse();
    $response->addCommand(new CloseModalDialogCommand());
    return $response;
  }

}
