<?php

class bulkEnitityRestfulBase extends OsRestfulEntityCacheableBase {

  public static function controllersInfo() {
    return array(
      '' => array(
        \RestfulInterface::GET => 'getList',
        \RestfulInterface::HEAD => 'getList',
        \RestfulInterface::PATCH => 'updateEntities',
        \RestfulInterface::DELETE => 'deleteEntities',
      ),
      'term/apply' => array(
        RestfulInterface::PATCH => 'applyTerms'
      ),
      'term/remove' => array(
        RestfulInterface::PATCH => 'removeTerms'
      ),
    ) + parent::controllersInfo();
  }

  protected function getLastModified($id) {
    $q = db_select('node', 'n')
      ->fields('n', array('changed'))
      ->condition('nid', $id)
      ->execute();

    foreach ($q as $r) {
      return $r->changed;
    }

    return FALSE;
  }

  /**
   * Apply term tid's to selected entities.
   */
  protected function applyTerms() {
    if (!empty($this->request['tid']) && !empty($this->request['entity_id']) && !empty($this->request['entity_type'])) {
      $entity_type = $this->request['entity_type'];
      $entity_id = $this->request['entity_id'];
      $new_terms = $this->request['tid'];
      $entities = entity_load($entity_type, $entity_id);
      $current_terms = array();
      foreach ($entities as $key => $entity) {
        $entity_wrapper = entity_metadata_wrapper($entity_type, $entity);
        foreach ($entity_wrapper->og_vocabulary->value() as $delta => $term_wrapper) {
          // $term_wrapper may now be accessed as a taxonomy term wrapper.
          $current_terms[] = $term_wrapper->tid;
        }
        $result = array_unique(array_merge($current_terms, $new_terms));
        if (!empty($result)) {
          $entity_wrapper->og_vocabulary->set($result);
          $entity_wrapper->save();
        }
      }
      return array('saved' => true);
    }
    else {
      return array('saved' => false);
    }
  }

  /**
   * Remove term tid's from selected entities.
   */
  protected function removeTerms() {
    if (!empty($this->request['tid']) && !empty($this->request['entity_id']) && !empty($this->request['entity_type'])) {
      $entity_type = $this->request['entity_type'];
      $entity_id = $this->request['entity_id'];
      $new_terms = $this->request['tid'];
      $current_terms = array();
      $entities = entity_load($entity_type, $entity_id);
      foreach ($entities as $key => $entity) {
        $entity_wrapper = entity_metadata_wrapper($entity_type, $entity);
        foreach ($entity_wrapper->og_vocabulary->value() as $delta => $term_wrapper) {
          // $term_wrapper may now be accessed as a taxonomy term wrapper.
          $current_terms[] = $term_wrapper->tid;
        }
        $result = array_diff($current_terms, $new_terms);
        $entity_wrapper->og_vocabulary->set($result);
        $entity_wrapper->save();
      }
      return array('saved' => true);
    }
    else {
      return array('saved' => false);
    }
  }

  /**
   * Bulk update entities.
   */
  protected function UpdateEntities() {
    if (!empty($this->request['entity_id']) && !empty($this->request['operation']) && !empty($this->request['entity_type'])) {
      $entity_type = $this->request['entity_type'];
      $entity_id = $this->request['entity_id'];
      $op = $this->request['operation'];
      $entities = entity_load($entity_type, $entity_id);
      if ($op == 'published' || $op == 'unpublished') {
        $status = ($op == 'published') ? 1 : 0;
        foreach ($entities as $key => $entity) {
          $entity_wrapper = entity_metadata_wrapper($entity_type, $entity);
          $entity_wrapper->status->set($status);
          $entity_wrapper->save();
        }
        return array('saved' => true);
      }
    }
    else {
      return array('saved' => false);
    }
  }

  /**
   * Bulk delete entities.
   */
  protected function deleteEntities() {
    if (!empty($this->request['entity_id']) && !empty($this->request['operation']) && !empty($this->request['entity_type'])) {
      $entity_type = $this->request['entity_type'];
      $entity_id = $this->request['entity_id'];
      $op = $this->request['operation'];
      if ($op == 'deleted') {
        entity_delete_multiple($entity_type, $entity_id);
        return array('saved' => true);
      }
    }
    else {
      return array('saved' => false);
    }
  }

}
