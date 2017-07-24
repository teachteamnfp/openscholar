<?php

/**
 * @file
 * Contains RestfulEntityUser__1_0.
 */

class OsRestfulUser extends \RestfulEntityBaseUser {

  public function publicFieldsInfo() {
    $public_fields = parent::publicFieldsInfo();

    $public_fields['name'] = array(
      'property' => 'name',
    );

    $public_fields['password'] = array(
      'property' => 'pass',
      'callback' => array($this, 'hideField')
    );

    $public_fields['status'] = array(
      'property' => 'status',
    );

    $public_fields['role'] = array(
      'property' => 'roles',
      'process_callbacks' => array(
        array($this, 'getRoles'),
      ),
    );

    $public_fields['create_access'] = array(
      'callback' => array($this, 'getCreateAccess')
    );

    $ga_field = og_get_group_audience_fields('user','user','node');
    unset($ga_field['vsite_support_expire']);

    if(count($ga_field)) {
      $public_fields['og_user_node'] = array(
        'property' => key($ga_field),
        'process_callbacks' => array(
          array($this, 'vsiteFieldDisplay'),
        ),
      );
    }

    return $public_fields;
  }

  /**
   * Hide the field value.
   *
   * @return null
   */
  protected function hideField() {
    return NULL;
  }

  /**
   * Overriding the create entity method in order to load the password.inc file.
   */
  public function createEntity() {
    require_once DRUPAL_ROOT . '/' . variable_get('password_inc', 'includes/password.inc');
    return parent::createEntity();
  }

  /**
   * Refactor the roles property with rid-name format.
   */
  public function getRoles($roles) {
    $return = array();
    foreach ($roles as $role) {
      $info = user_role_load($role);
      $return[$info->rid] = $info->name;
    }
    return $return;
  }

  /**
   * Returns whether a user can create new sites or not
   */
  public function getCreateAccess() {
    if (module_exists('vsite')) {
      return _vsite_user_access_create_vsite();
    }
  }

  /**
   * Display the id and the title of the group.
   */
  public function vsiteFieldDisplay($values) {
    $account = $this->getAccount();
    ctools_include('subsite', 'vsite');

    $groups = array();
    // Obtaining associative array of custom domains, keyed by space id
    $custom_domains = $this->getCustomDomains($values);
    $purl_base_domain = variable_get('purl_base_domain');
    foreach ($values as $value) {
      $groups[] = array(
        'title' => $value->title,
        'id' => $value->nid,
        'purl' => $value->purl,
        'delete_base_url' => isset($custom_domains[$value->nid]) ? 'http://' . $custom_domains[$value->nid] . '/user#overlay=' : $purl_base_domain . '/' . $value->purl . '/#overlay=' . $value->purl . '/',
        'owner' => ($value->uid == $account->uid),
        'subsite_access' => vsite_subsite_access('create', $value),
        'delete_access' => node_access('delete', $value),
      );
    }
    return $groups;
  }

  /**
   * Returns associative array of custom domains, keyed by space id
   */
  protected function getCustomDomains($vsites) {
    $space_ids = array();
    foreach ($vsites as $vsite) {
      $space_ids[] = $vsite->nid;
    }
    $result = db_select('purl', 'p')
      ->fields('p', array('id', 'value'))
      ->condition('provider', 'vsite_domain', '=')
      ->condition('id', $space_ids, 'IN')
      ->execute()
      ->fetchAllKeyed(0, 1);
    return $result;
  }
}
