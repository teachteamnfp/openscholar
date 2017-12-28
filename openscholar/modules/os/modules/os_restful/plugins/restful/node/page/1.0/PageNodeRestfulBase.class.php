<?php

class PageNodeRestfulBase extends OsNodeRestfulBase {

  public function publicFieldsInfo() {
    $public_fields = parent::publicFieldsInfo();

    $public_fields['path'] = array(
      'property' => 'path',
    );

    return $public_fields;
  }

  public function getNodeForm() {
    $form = parent::getNodeForm();
    // hook_form_BASE_FORM_ID_alter won't work because we need to pass vsite
    // explicitly here. So we need to Re-apply the logic here.
    // @See os_pages_form_page_node_form_alter(&$form, &$form_state).
    if ($this->getBundle() == 'page') {
      // Adds a custom fieldset for the right-hand column to hold meta
      // description.
      if (isset($form['os_seo']) && $this->request['vsite'] > 0) {
        $form['os_seo'] = array(
          '#type' => 'fieldset',
          '#title' => t('Search Engine Optimization (SEO)'),
          '#collapsible' => TRUE,
          '#collapsed' => TRUE,
          '#weight' => 20,
          '#group' => 'additional_settings',
          'field_meta_description' => $form['field_meta_description'],
          '#access' => TRUE,
        );
        unset($form['field_meta_description']);
      }
      if (isset($form['os_css_class_fieldset']) && $this->request['vsite'] > 0) {
        $form['os_css_class_fieldset']['#weight'] = 21;
        $form['os_css_class_fieldset']['#group'] = 'additional_settings';
        unset($form['os_css_class_fieldset']['#attributes']);
      }
    }

    return $form;
  }

}
