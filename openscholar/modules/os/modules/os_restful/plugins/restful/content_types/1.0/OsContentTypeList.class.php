<?php

/**
 * @file
 * Contains \RestfulDataProviderDbQuery
 */

class OsContentTypeList extends \RestfulBase implements \RestfulDataProviderInterface {

  /**
   * Overrides \RestfulBase::controllersInfo().
   */
  public static function controllersInfo() {
    return array(
      '^.*$' => array(
        \RestfulInterface::GET => 'getContentTypes',
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function publicFieldsInfo() {
    return array(
      'label' => array(
        'property' => 'label',
      ),
      'id' => array(
        'property' => 'id',
      ),
    );
  }

  /**
   * Get content type options.
   */
  public function getContentTypes () {
    $exempted_content_types = array(
      'blog_import' => 'Blog entry import',
      'department' => 'Department Site',
      'feed_importer' => 'Feed importer',
      'personal' => 'Personal Site',
      'project' => 'Project Site',
      'slideshow_slide' => 'Slideshow Image',
      );

    $node_types = array_diff(node_type_get_names(), $exempted_content_types);
    $output = array();
    foreach ($node_types as $key => $node_type) {
      $output[] = array('label' => $node_type, 'id' => $key);
    }

    return $output;
  }

}
