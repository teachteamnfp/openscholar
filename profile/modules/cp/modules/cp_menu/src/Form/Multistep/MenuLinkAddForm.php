<?php

namespace Drupal\cp_menu\Form\Multistep;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use Drupal\cp_menu\Form\Multistep\Manager\StepManager;
use Drupal\cp_menu\Form\Multistep\Step\StepOne;
use Drupal\cp_menu\Form\Multistep\Step\StepTwo;
use Drupal\cp_menu\MenuHelperInterface;
use Drupal\cp_menu\Services\MenuHelper;
use Drupal\vsite\Plugin\VsiteContextManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MenuLinkAddForm.
 */
class MenuLinkAddForm extends FormBase {

  /**
   * Step Id.
   *
   * @var \Drupal\cp_menu\Form\Multistep\Step\StepOne
   */
  protected $stepId;

  /**
   * Multi steps of the form.
   *
   * @var \Drupal\cp_menu\Form\Multistep\Step\StepInterface
   */
  protected $step;

  /**
   * Step manager instance.
   *
   * @var \Drupal\cp_menu\Form\Multistep\Manager\StepManager
   */
  protected $stepManager;

  /**
   * Private temp store.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStore
   */
  protected $store;

  /**
   * Private temp store service.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $privateTempStore;

  /**
   * Entity Type Manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Path Validator Service.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

  /**
   * Menu helper service.
   *
   * @var \Drupal\cp_menu\MenuHelperInterface
   */
  protected $menuHelper;

  /**
   * Vsite Manager service.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManager
   */
  protected $vsiteManager;

  /**
   * Constructs a \Drupal\cp_menu\Form\Multistep\MenuWizardBase instance.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $private_temp_store
   *   Private temp store factory instance.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager instance.
   * @param \Drupal\Core\Path\PathValidatorInterface $path_validator
   *   Path validator instance.
   * @param \Drupal\cp_menu\MenuHelperInterface $menu_helper
   *   Menu helper instance.
   * @param \Drupal\vsite\Plugin\VsiteContextManager $vsite_manager
   *   Vsite manager instance.
   */
  public function __construct(PrivateTempStoreFactory $private_temp_store, EntityTypeManagerInterface $entity_type_manager, PathValidatorInterface $path_validator, MenuHelperInterface $menu_helper, VsiteContextManager $vsite_manager) {
    $this->stepId = StepOne::STEP_ONE;
    $this->privateTempStore = $private_temp_store;
    $this->store = $this->privateTempStore->get('link_data');
    $this->stepManager = new StepManager($this->privateTempStore);
    $this->entityTypeManager = $entity_type_manager;
    $this->pathValidator = $path_validator;
    $this->menuHelper = $menu_helper;
    $this->vsiteManager = $vsite_manager;
  }

  /**
   * Inject all services we need.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Service container.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private'),
      $container->get('entity_type.manager'),
      $container->get('path.validator'),
      $container->get('cp_menu.menu_helper'),
      $container->get('vsite.context_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cp_menu_link_add_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $menu = NULL) : array {

    $form['wrapper-messages'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'messages-wrapper',
      ],
    ];

    $form['wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'form-wrapper',
      ],
    ];

    // Get step from step manager.
    $this->step = $this->stepManager->getStep($this->stepId);

    // Store the menu name in the store.
    $this->store->set('menu_name', $menu);

    // Attach step form elements.
    $form['wrapper'] += $this->step->buildStepFormElements();

    // Attach buttons.
    $form['wrapper']['actions']['#type'] = 'actions';

    // Buttons for Step one.
    if ($this->stepId === 1) {
      $form['wrapper']['actions']['continue'] = [
        '#type' => 'submit',
        '#value' => $this->t('Continue'),
        '#goto_step' => StepTwo::STEP_TWO,
        '#ajax' => [
          'callback' => [$this, 'loadStep'],
          'event' => 'click',
          'wrapper' => 'form-wrapper',
        ],
      ];

      $form['wrapper']['actions']['cancel'] = [
        '#type' => 'submit',
        '#value' => $this->t('Cancel'),
        '#limit_validation_errors' => [],
        '#submit' => [],
        '#cancel' => TRUE,
      ];
    }

    // Buttons for Step Two.
    elseif ($this->stepId === 2) {

      $form['wrapper']['actions']['previous'] = [
        '#type' => 'submit',
        '#value' => $this->t('Back'),
        '#goto_step' => StepOne::STEP_ONE,
        '#limit_validation_errors' => [],
        '#submit' => [],
        '#ajax' => [
          'callback' => [$this, 'loadStep'],
          'wrapper' => 'form-wrapper',
          'event' => 'click',
        ],
      ];

      $form['wrapper']['actions']['finish'] = [
        '#type' => 'submit',
        '#value' => $this->t('Finish'),
        '#ajax' => [
          'callback' => [$this, 'submitValues'],
          'event' => 'click',
          'wrapper' => 'form-wrapper',
        ],
      ];

      $form['wrapper']['actions']['cancel'] = [
        '#type' => 'submit',
        '#value' => $this->t('Cancel'),
        '#limit_validation_errors' => [],
        '#submit' => [],
        '#cancel' => TRUE,
      ];
    }
    return $form;
  }

  /**
   * Ajax callback to load new step.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state interface.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Ajax response.
   */
  public static function loadStep(array &$form, FormStateInterface $form_state) : AjaxResponse {
    $response = new AjaxResponse();

    // Remove messages.
    $response->addCommand(new HtmlCommand('#messages-wrapper', ''));

    // Update Form.
    $response->addCommand(new HtmlCommand('#form-wrapper',
      $form['wrapper']));

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) : void {
    $triggering_element = $form_state->getTriggeringElement();
    // Only validate if validation doesn't have to be skipped.
    // For example on "previous" button.
    if (!isset($triggering_element['#cancel'])) {
      // Validate fields.
      // Validate all validators for field.
      $values = $form_state->getValues();
      if (isset($values['url']) && $values['url']) {
        $url = $this->stripPurl($values['url']);
        $url = $this->pathValidator->getUrlIfValid($url);
        if ($url === FALSE) {
          $form_state->setErrorByName('url', 'Url is invalid.');
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) : void {

    $triggering_element = $form_state->getTriggeringElement();
    // Save filled values to step. So we can use them as default_value later on.
    if (isset($triggering_element['#goto_step'])) {
      if ($this->stepId === 1) {
        $type = $form_state->getValue('link_type');
        $this->store->set('link_type', $type);
      }
      // Add step to manager.
      $this->stepManager->addStep($this->step);
      // Set step to navigate to.
      $this->stepId = $triggering_element['#goto_step'];
      $form_state->setRebuild(TRUE);
    }

    if (isset($triggering_element['#cancel'])) {
      $keys = ['link_type', 'menu_name'];
      foreach ($keys as $key) {
        $this->store->delete($key);
      }
      $form_state->setRedirect('cp.build.menu');
    }
  }

  /**
   * Submit handler for last step of form.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state interface.
   */
  public function submitValues(array &$form, FormStateInterface $form_state) : AjaxResponse {
    $response = new AjaxResponse();

    if ($form_state->getErrors()) {
      $response->addCommand(new ReplaceCommand('#form-wrapper', $form));
      $this->messenger()->deleteAll();
      return $response;
    }

    $type = $this->store->get('link_type');
    $values = $form_state->getValues();

    $vsite = $this->vsiteManager->getActiveVsite();
    $menus = $vsite->getContent('group_menu:menu');
    $menu_id = $this->store->get('menu_name');
    $vsite_menu_id = $menus ? $menu_id : (MenuHelper::DEFAULT_VSITE_MENU_MAPPING[$menu_id] . $vsite->id());
    // If first time then create a new menu by replicating shared menus.
    if (!$menus) {
      // Create new menus.
      $this->menuHelper->createVsiteMenus($vsite);
    }

    // Decide the data to be saved in the link based on link types.
    switch ($type) {
      case 'home':
        $url = 'internal:/';
        break;

      case 'menu_heading':
        $url = 'internal:#';
        break;

      case 'url':
        $url = $this->stripPurl($values['url']);
        $url = $this->pathValidator->getUrlIfValid($url)->toString();
        if (UrlHelper::isExternal($url)) {
          $parts = UrlHelper::parse($url);
          $url = $parts['path'];
          $options['query'] = $parts['query'];
          $options['fragment'] = $parts['fragment'];
        }
        else {
          $url = 'internal:' . $url;
        }
        break;
    }

    // Save the link to mapped menu.
    $this->entityTypeManager->getStorage('menu_link_content')->create([
      'title' => $this->t('@title', ['@title' => $values['title']]),
      'link' => ['uri' => $url, 'options' => $options ?? []],
      'menu_name' => $vsite_menu_id,
      'description' => $values['tooltip'] ?? '',
      'expanded' => TRUE,
    ])->save();

    $response->addCommand(new CloseModalDialogCommand());
    $currentURL = Url::fromRoute('cp.build.menu');
    $response->addCommand(new RedirectCommand($currentURL->toString()));

    // Call the block cache clear method as changes are made.
    $this->menuHelper->invalidateBlockCache($vsite, $vsite_menu_id);

    return $response;
  }

  /**
   * Strips the purl as it is automatically attached while saving.
   *
   * @param string $value
   *   The entered url.
   *
   * @return string
   *   The stripped url.
   */
  protected function stripPurl(string $value) {
    $purl = $this->vsiteManager->getActivePurl();
    // If vsite prefix is entered, strip it.
    $parts = explode('/', $value);
    if ($parts && in_array($purl, $parts)) {
      return str_replace($purl, '', $value);
    }
    return $value;
  }

}
