<?php

class OsbulkOperationEnitity extends OsRestfulEntityCacheableBase {

  public static function controllersInfo() {
    return array(
      'bulk/terms/apply' => array(
        \RestfulInterface::PATCH => 'updateTerms'
      ),
      'bulk/terms/remove' => array(
        \RestfulInterface::PATCH => 'removeTerms'
      ),
      'bulk' => array(
        \RestfulInterface::PATCH => 'updateEntities'
      ),
    ) + parent::controllersInfo();
  }

  protected function getLastModified($id) {
    // Vocabularies cannot really be editted. When they were first created isn't stored either.
    // This function is only concerned with modifications, so as long as we assume it's really old, we're fine for now
    return strotime('-31 days', REQUEST_TIME);
  }

  /**
   * Bulk: Add terms to entity.
   */
  protected function updateTerms() {
    if (!empty($this->request['entity_id']) && !empty($this->request['tids'])) {
      $entity_type = $this->entityType;
      $new_terms = $this->request['tids'];
      $entity_id = $this->request['entity_id'];
      $current_terms = array();
      $entities = entity_load($entity_type, $entity_id);
      foreach ($entities as $key => $entity) {
        $entity_wrapper = entity_metadata_wrapper($entity_type, $entity);
        foreach ($entity_wrapper->og_vocabulary->value() as $delta => $term_wrapper) {
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
   * Bulk: Remove terms from entity.
   */
  protected function removeTerms() {
    if (!empty($this->request['entity_id']) && !empty($this->request['tids'])) {
      $entity_type = $this->entityType;
      $new_terms = $this->request['tids'];
      $entity_id = $this->request['entity_id'];
      $current_terms = array();
      $entities = entity_load($entity_type, $entity_id);
      foreach ($entities as $key => $entity) {
        $entity_wrapper = entity_metadata_wrapper($entity_type, $entity);
        foreach ($entity_wrapper->og_vocabulary->value() as $delta => $term_wrapper) {
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
   * Bulk: Update entity status.
   */
  protected function updateEntities() {
    if (!empty($this->request['entity_id']) && !empty($this->request['operation'])) {
      $entity_type = $this->entityType;
      $entity_id = $this->request['entity_id'];
      $op = $this->request['operation'];
      $entities = entity_load($entity_type, $entity_id);
      $status = ($op == 'published') ? 1 : 0;
      foreach ($entities as $key => $entity) {
        $entity_wrapper = entity_metadata_wrapper($entity_type, $entity);
        $entity_wrapper->status->set($status);
        $entity_wrapper->save();
      }
      return array('saved' => true);
    }
    else {
      return array('saved' => false);
    }
  }

  /**
   * Bulk: Delete entities.
   * Override as base method doesn't allow multiple deletes.
   */
  public function deleteEntity($entity_ids) {
    $entity_ids = explode(',', $entity_ids);
    if (is_array($entity_ids) && !empty($entity_ids)) {
      $entities = entity_load($this->entityType, $entity_ids);
      $results = array();
      $count = 0;
      foreach ($entities as $key => $entity) {
        if ($this->checkEntityAccess('delete', $this->entityType, $entity)) {
          entity_delete($this->entityType, $entity_ids);
          $count++;
        }
        else {
          $results[] = t('Skipped Delete item on Node ' . $entity->title . ' due to insufficient permissions.');
        }
      }
      if (count($entity_ids) > 0) {
        array_push($results, t('Performed Delete item on ' . $count. ' item'));
        return $results;
      }
      else {
        return array('deleted' => true);
      }
    }
    else {
      return array('deleted' => false);
    }
  }

}
