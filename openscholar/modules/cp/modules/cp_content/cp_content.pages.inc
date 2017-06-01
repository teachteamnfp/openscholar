<?php
function cp_content_add_page() {
  $item = menu_get_item('node/add');
  $content = system_admin_menu_block($item);
  // Bypass the node/add listing if only one content type is available.

  if (count($content) == 1) {
    $item = array_shift($content);
    drupal_goto($item['href']);
  }

  $bundles = os_get_bundles();

  $links = array();
  foreach ($bundles as $type => $name) {
    $url_str = str_replace('_', '-', $type);
    foreach ($content as $link) {
      if ($link['link_path'] == 'node/add/'.$url_str) {
        $links[] = $link;
      }
    }
  }

  return theme('node_add_list', array('content' => $links));
}

/**
 * Page callback; For list all content types.
 *
 * @return json
 */
function cp_content_type_rest() {
  $exempted_content_types = array(
    'blog_import' => 'Blog entry import',
    'department' => 'Department Site',
    'feed_importer' => 'Feed importer',
    'personal' => 'Personal Site',
    'project' => 'Project Site',
    'slideshow_slide' => 'Slideshow Image',
    );

  $node_types = array_diff(node_type_get_names(), $exempted_content_types);
  $data = array();
  foreach ($node_types as $key => $node_type) {
    $data[] = array('label' => $node_type, 'id' => $key);
  }

  return array('data' => $data);
}
