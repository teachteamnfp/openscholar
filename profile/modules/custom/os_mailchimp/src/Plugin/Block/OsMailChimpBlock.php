<?php

namespace Drupal\os_mailchimp\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;

/**
 * Provides a block with mailchimp subscribe.
 *
 * @Block(
 *   id = "os_mailchimp_subscribe",
 *   admin_label = @Translation("Mailchimp subscribe"),
 * )
 */
class OsMailChimpBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    if (empty($config['list_id'])) {
      // Debug info for html markup.
      return [
        '#type' => 'markup',
        '#markup' => 'List ID is not configured!',
      ];
    }

    $link_url = Url::fromRoute('os_mailchimp.modal.subscribe', ['list_id' => $config['list_id']]);
    $link_url->setOptions([
      'attributes' => [
        'class' => ['use-ajax', 'button', 'button--small'],
        'data-dialog-type' => 'modal',
      ],
    ]);

    return [
      '#type' => 'markup',
      '#markup' => Link::fromTextAndUrl(t('Subscribe to list!'), $link_url)->toString(),
      '#attached' => [
        'library' => [
          'core/drupal.dialog.ajax',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();
    $lists = mailchimp_get_lists();

    $form['list_id'] = [
      '#type' => 'select',
      '#title' => 'List to subscribe',
      '#options' => $this->mailChimpListsToOptions($lists),
      '#default_value' => $config['list_id'],
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['list_id'] = $form_state->getValue('list_id');
  }

  /**
   * Convert mailchimp lists to form options.
   *
   * @param array $lists
   *   Generated array by mailchimp_get_lists().
   *
   * @return array
   *   Converted form options.
   */
  protected function mailChimpListsToOptions(array $lists) : array {
    $options = [];
    foreach ($lists as $list_id => $list) {
      $options[$list_id] = $list->name;
    }
    return $options;
  }

}
