<?php

/**
 * @file
 * Provides mapping for contextual ids to documentation URLs.
 */

/**
 * The mode for popup icon links to behave when clicked.
 *
 * @todo Expose setting for sitewide admins?
 */
define('OS_HELP_ICON_LINK_MODE', 'new_tab');

/**
 * Returns a docs URL for the given path or form element key.
 *
 * @param bool $is_path
 *   TRUE if the $id param being passed is a page path, FALSE otherwise.
 * @param string id
 *   The current path or form element.
 */
function os_help_get_variables_from_id($type, $id) {
  $variables = FALSE;

  switch ($type) {
    case 'path':
      $links = array(
        'cp/appearance' => array('url' => variable_get('help_cp_appearance_url'), 'title' => 'Manage Appearance'),
        'cp/build' => array('url' => variable_get('help_cp_build_url'), 'title' => 'Apps'),
        'cp/build/apps' => array('url' => variable_get('help_cp_build_url'), 'title' => 'Apps'),
        'cp/build/layout' => array('url' => variable_get('help_cp_build_layout_url'), 'title' => 'Manage Layout'),
        'cp/build/menu' => array('url' => variable_get('help_cp_build_menu_url'), 'title' => 'Manage Menus'),
        'cp/build/taxonomy' => array('url' => variable_get('help_cp_build_taxonomy_url'), 'title' => 'Manage Taxonomy'),
        'cp/content' => array('url' => '', 'title' => ''),
        'cp/content/content' => array('url' => '', 'title' => ''),
        'cp/content/files' => array('url' => variable_get('help_cp_content_files_url'), 'title' => 'Manage Files'),
        'cp/settings' => array('url' => variable_get('help_cp_settings_url'),, 'title' => 'Manage Site Settings'),
        'cp/support' => array('url' => variable_get('help_cp_support_url'), 'title' => 'Get Support'),
        'cp/users' => array('url' => variable_get('help_cp_users_url'), 'title' => 'Manage Site Members'),
      );
      break;
    case 'app':
      $links = array(
        'harvard_courses' => array('url' => '', 'title' => ''),
        'os_news' => array('url' => variable_get('help_os_news_url'), 'title' => 'News App'),
        'os_biocv' => array('url' => variable_get('help_os_biocv_url'), 'title' => 'Bio/CV app'),
        'os_blog' => array('url' => variable_get('help_os_blog_url'), 'title' => 'Blog App'),
        'os_booklets' => array('url' => variable_get('help_os_booklets_url'), 'title' => 'Booklets App'),
        'os_classes' => array('url' => variable_get('help_os_classes_url'), 'title' => 'Classes App'),
        'os_events' => array('url' => variable_get('help_os_events_url'), 'title' => 'Events App'),
        'os_faq' => array('url' => variable_get('help_os_faq_url'), 'title' => 'FAQ App'),
        'os_files_private' => array('url' => variable_get('help_os_files_private_url'), 'title' => 'Private Files App'),
        'os_pages' => array('url' => variable_get('help_os_pages_url'), 'title' => 'Basic Pages App'),
        'os_image_gallery' => array('url' => variable_get('help_os_image_gallery_url'), 'title' => 'Media Gallery App'),
        'os_links' => array('url' => variable_get('help_os_links_url'), 'title' => 'Links App'),
        'os_profiles' => array('url' => variable_get('help_os_profiles_url'), 'title' => 'Profiles App'),
        'os_publications' => array('url' => variable_get('help_os_publications_url'), 'title' => 'Publications App'),
        'os_presentations' => array('url' => variable_get('help_os_presentations_url'), 'title' => 'Presentations App'),
        'os_reader' => array('url' => variable_get('help_os_reader_url'), 'title' => 'Reader App'),
        'os_software' => array('url' => variable_get('help_os_software_url'), 'title' => 'Software App'),
      );
      break;
    case 'widget':
      $links = array(
        'os_boxes_html' => array('url' => variable_get('help_os_custom_texthtml_url'), 'title' => 'Custom text widget'),
        'os_taxonomy_fbt' => array('url' => variable_get('help_os_taxonomy_fbt_url'), 'title' => 'Taxonomy Widget'),
        'os_boxes_manual_list' => array('url' => variable_get('help_os_featured_posts_url'), 'title' => 'Featured Posts Widget'),
        'os_search_solr_more_like_this' => array('url' => variable_get('help_os_search_solr_more_url'), 'title' => 'More like this Widget'),
        'os_sv_list_box' => array('url' => variable_get('help_os_sv_list_box_url'), 'title' => 'List of Posts Widget'),
        'os_sv_list_file' => array('url' => variable_get('help_os_sv_list_file_url'), 'title' => 'List of Files Widget'),
        'os_boxes_feedreader' => array('url' => variable_get('help_os_feedreader_url'), 'title' => 'Feed Reader Widget'),
        'os_boxes_pub_year' => array('url' => variable_get('help_os_pub_year_url'), 'title' => 'Publications by Year Widget'),
        'os_boxes_booktoc' => array('url' => variable_get('help_os_booktoc_url'), 'title' => 'Booklet Table of Content Widget'),
        'os_boxes_pub_ca' => array('url' => variable_get('help_os_pub_author_url'), 'title' => 'Publications by Authors Widget'),
        'os_boxes_pub_type' => array('url' => variable_get('help_os_pub_types_url'), 'title' => 'Publications: Types Widget'),
        'os_slideshow_box' => array('url' => variable_get('help_os_slideshow_url'), 'title' => 'Slideshow Widget'),
        'os_boxes_media' => array('url' => variable_get('help_os_embed_media_url'), 'title' => 'Embed Media Widget'),
        'os_boxes_rss' => array('url' => variable_get('help_os_rss_feed_url'), 'title' => 'Rss Feed Widget'),
        'os_boxes_mailchimp' => array('url' => variable_get('help_os_mailchimp_url'), 'title' => 'MailChimp Widget'),
        'os_boxes_follow' => array('url' => variable_get('help_os_follow_url'), 'title' => 'Follow Me Widget'),
        'os_boxes_twitterfeed' => array('url' => variable_get('help_os_twitter_url'), 'title' => 'Twitter Widget'),
        'os_boxes_bio' => array('url' => variable_get('help_os_bio_url'), 'title' => 'Bio Widget'),
        'os_boxes_addthis' => array('url' => variable_get('help_os_addthis_url'), 'title' => 'Add This Widget'),
        'os_boxes_accordion' => array('url' => variable_get('help_os_accordion_url'), 'title' => 'Accordian Widget'),
        'os_boxes_tabs' => array('url' => variable_get('help_os_tabs_url'), 'title' => 'Tabs Widget'),
        'os_boxes_rand_widget' => array('url' => variable_get('help_os_random_url'), 'title' => 'Random Widget'),
        'os_boxes_widget_slider' => array('url' => variable_get('help_os_slider_url'), 'title' => 'Widget Slider Widget'),
        'os_boxes_columns' => array('url' => variable_get('help_os_columns_url'), 'title' => 'Columns Widget'),
        'os_boxes_facetapi_vocabulary' => array('url' => variable_get('help_os_facetapi_url'), 'title' => 'Faceted Taxonomy Widget'),
        'os_boxes_dataverse_dataset' => array('url' => variable_get('help_os_dataverse_dataset_url'), 'title' => 'Dataverse Dataset'),
        'os_boxes_dataverse_dataset_citation' => array('url' => variable_get('help_os_dataverse_citation_url'), 'title' => 'Dataverse Dataset Citation'),
        'os_boxes_dataverse_list' => array('url' => variable_get('help_os_dataverse_list_url'), 'title' => 'Dataverse List'),
        'os_boxes_dataverse_search' => array('url' => variable_get('help_os_dataverse_search_url'), 'title' => 'Dataverse Search'),
        'os_search_solr_search_box' => array('url' => variable_get('help_os_filtered_search_url'), 'title' => 'Filtered Search Widget'),
      );
      break;
  }

  if (isset($links[$id]) && !empty($links[$id]['url']) && !empty($links[$id]['title'])) {
    $variables = array(
      'path' => $links[$id]['url'],
      'text' => '<span>' . t('Help') . '</span>',
      'options' => array(
        'fragment' => 'main-content',
        'attributes' => array(
          'class' => array(
            'os-help-link',
          ),
          'title' => $links[$id]['title'],
        ),
        'html' => TRUE,
      ),
    );
    $mode = OS_HELP_ICON_LINK_MODE;
    switch ($mode) {
      case 'popup':
        $variables['options']['attributes']['onclick'] = 'var w=window.open(this.href, \'os_help_link\', \'width=480,height=600,scrollbars,resizable\'); w.focus(); return false;';
        break;
      case 'new_tab':
        $variables['options']['attributes']['target'] = '_blank';
        break;
    }
  }

  return $variables;
}
