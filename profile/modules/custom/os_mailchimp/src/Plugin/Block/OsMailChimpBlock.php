<?php

namespace Drupal\os_mailchimp\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\os_mailchimp\Form\OsMailChimpSignupForm;

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
      return '';
    }
    $list = mailchimp_get_list($config['list_id']);

    if (empty($list)) {
      return [
        '#markup' => $this->t('The subscription service is currently unavailable. Please check again later.'),
      ];
    }
    $form = new OsMailChimpSignupForm();
    $form->setList($list);

    $content = \Drupal::formBuilder()->getForm($form);

    return $content;
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
