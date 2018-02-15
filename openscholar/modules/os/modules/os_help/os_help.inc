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
        'cp/appearance' => array('url' => 'http://docs.openscholar.harvard.edu/managing-look-your-site', 'title' => 'Manage Appearance'),
        'cp/build' => array('url' => 'http://docs.openscholar.harvard.edu/content-apps-and-widgets', 'title' => 'Apps'),
        'cp/build/apps' => array('url' => 'http://docs.openscholar.harvard.edu/content-apps-and-widgets', 'title' => 'Apps'),
        'cp/build/layout' => array('url' => 'http://docs.openscholar.harvard.edu/customizing-your-site-layout', 'title' => 'Manage Layout'),
        'cp/build/menu' => array('url' => 'http://docs.openscholar.harvard.edu/menus', 'title' => 'Manage Menus'),
        'cp/build/taxonomy' => array('url' => 'http://docs.openscholar.harvard.edu/categorizing-your-content-taxonomy', 'title' => 'Manage Taxonomy'),
        'cp/content' => array('url' => '', 'title' => ''),
        'cp/content/content' => array('url' => '', 'title' => ''),
        'cp/content/files' => array('url' => 'http://docs.openscholar.harvard.edu/file-management', 'title' => 'Manage Files'),
        'cp/settings' => array('url' => 'http://docs.openscholar.harvard.edu/site-settings', 'title' => 'Manage Site Settings'),
        'cp/support' => array('url' => 'http://hwp.harvard.edu/pages/support', 'title' => 'Get Support'),
        'cp/users' => array('url' => 'http://docs.openscholar.harvard.edu/managing-users', 'title' => 'Manage Site Members')
      );
      break;
    case 'app':
      $links = array(
        'harvard_courses' => array('url' => '', 'title' => ''),
        'os_news' => array('url' => 'http://docs.openscholar.harvard.edu/import-news-rss', 'title' => 'News App'),
        'os_biocv' => array('url' => 'http://docs.openscholar.harvard.edu/biocv', 'title' => 'Bio/CV app'),
        'os_blog' => array('url' => 'http://docs.openscholar.harvard.edu/blog', 'title' => 'Blog App'),
        'os_booklets' => array('url' => 'http://docs.openscholar.harvard.edu/booklet', 'title' => 'Booklets App'),
        'os_classes' => array('url' => 'http://docs.openscholar.harvard.edu/classes', 'title' => 'Classes App'),
        'os_events' => array('url' => 'http://docs.openscholar.harvard.edu/events-app', 'title' => 'Events App'),
        'os_faq' => array('url' => 'http://docs.openscholar.harvard.edu/faq-app', 'title' => 'FAQ App'),
        'os_files_private' => array('url' => 'http://docs.openscholar.harvard.edu/private-files', 'title' => 'Private Files App'),
        'os_pages' => array('url' => 'http://docs.openscholar.harvard.edu/pages', 'title' => 'Basic Pages App'),
        'os_image_gallery' => array('url' => 'http://docs.openscholar.harvard.edu/image-management', 'title' => 'Media Gallery App'),
        'os_links' => array('url' => 'http://docs.openscholar.harvard.edu/links', 'title' => 'Links App'),
        'os_profiles' => array('url' => 'http://docs.openscholar.harvard.edu/people-profiles', 'title' => 'Profiles App'),
        'os_publications' => array('url' => 'http://docs.openscholar.harvard.edu/publications', 'title' => 'Publications App'),
        'os_presentations' => array('url' => 'http://docs.openscholar.harvard.edu/presentations', 'title' => 'Presentations App'),
        'os_reader' => array('url' => 'http://docs.openscholar.harvard.edu/rss-feed', 'title' => 'Reader App'),
        'os_software' => array('url' => 'http://docs.openscholar.harvard.edu/software', 'title' => 'Software App'),
      );
      break;
    case 'widget':
      $links = array(
         'os_boxes_html' => array('url' => 'http://docs.openscholar.harvard.edu/custom-texthtml-widget', 'title' => 'Custom text widget'),
         'os_taxonomy_fbt' => array('url' => 'http://docs.openscholar.harvard.edu/taxonomy-widget', 'title' => 'Taxonomy Widget'),
         'os_boxes_manual_list' => array('url' => 'http://docs.openscholar.harvard.edu/featured-posts-widget', 'title' => 'Featured Posts Widget'),
         'os_search_solr_more_like_this' => array('url' => 'http://docs.openscholar.harvard.edu/more', 'title' => 'More like this Widget'),
         'os_sv_list_box' => array('url' => 'http://docs.openscholar.harvard.edu/list-of-posts-widget', 'title' => 'List of Posts Widget'),
         'os_sv_list_file' => array('url' => 'http://docs.openscholar.harvard.edu/list-files', 'title' => 'List of Files Widget'),
         'os_boxes_feedreader' => array('url' => 'http://docs.openscholar.harvard.edu/feed-reader-widget', 'title' => 'Feed Reader Widget'),
         'os_boxes_pub_year' => array('url' => 'http://docs.openscholar.harvard.edu/publications-year-widget', 'title' => 'Publications by Year Widget'),
         'os_boxes_booktoc' => array('url' => 'http://docs.openscholar.harvard.edu/booklet-table-contents-widget', 'title' => 'Booklet Table of Content Widget'),
         'os_boxes_pub_ca' => array('url' => 'http://docs.openscholar.harvard.edu/publications-authors-widget', 'title' => 'Publications by Authors Widget'),
         'os_boxes_pub_type' => array('url' => 'http://docs.openscholar.harvard.edu/publications-types-widget', 'title' => 'Publications: Types Widget'),
         'os_slideshow_box' => array('url' => 'http://docs.openscholar.harvard.edu/photo-slideshow-widget', 'title' => 'Slideshow Widget'),
         'os_boxes_media' => array('url' => 'http://docs.openscholar.harvard.edu/embed-media-widget', 'title' => 'Embed Media Widget'),
         'os_boxes_rss' => array('url' => 'http://docs.openscholar.harvard.edu/rss-feed-widget', 'title' => 'Rss Feed Widget'),
         'os_boxes_mailchimp' => array('url' => 'http://docs.openscholar.harvard.edu/embedding-mailchimp-newsletter-sign', 'title' => 'MailChimp Widget'),
         'os_boxes_follow' => array('url' => 'http://docs.openscholar.harvard.edu/follow-me-widget', 'title' => 'Follow Me Widget'),
         'os_boxes_twitterfeed' => array('url' => 'http://docs.openscholar.harvard.edu/twitter-widget', 'title' => 'Twitter Widget'),
         'os_boxes_bio' => array('url' => 'http://docs.openscholar.harvard.edu/bio-widget', 'title' => 'Bio Widget'),
         'os_boxes_addthis' => array('url' => 'http://docs.openscholar.harvard.edu/addthis-widget', 'title' => 'Add This Widget'),
         'os_boxes_accordion' => array('url' => 'http://docs.openscholar.harvard.edu/accordion-widget', 'title' => 'Accordian Widget'),
         'os_boxes_tabs' => array('url' => 'http://docs.openscholar.harvard.edu/tabs-widget', 'title' => 'Tabs Widget'),
         'os_boxes_rand_widget' => array('url' => 'http://docs.openscholar.harvard.edu/random-widget', 'title' => 'Random Widget'),
         'os_boxes_widget_slider' => array('url' => 'http://docs.openscholar.harvard.edu/widget-slider', 'title' => 'Widget Slider Widget'),
         'os_boxes_columns' => array('url' => 'http://docs.openscholar.harvard.edu/columns-widget', 'title' => 'Columns Widget'),
         'os_boxes_facetapi_vocabulary' => array('url' => 'http://docs.openscholar.harvard.edu/filtering-content-faceted-taxonomy-widget', 'title' => 'Faceted Taxonomy Widget'),
         'os_boxes_dataverse_dataset' => array('url' => 'http://docs.openscholar.harvard.edu/dataverse-dataset-widget', 'title' => 'Dataverse Dataset'),
         'os_boxes_dataverse_dataset_citation' => array('url' => 'http://docs.openscholar.harvard.edu/dataverse-dataset-citation', 'title' => 'Dataverse Dataset Citation'),
         'os_boxes_dataverse_list' => array('url' => 'http://docs.openscholar.harvard.edu/dataverse-list-widget', 'title' => 'Dataverse List'),
         'os_boxes_dataverse_search' => array('url' => 'http://docs.openscholar.harvard.edu/dataverse-search-box', 'title' => 'Dataverse Search'),
         'os_search_solr_search_box' => array('url' => 'http://docs.openscholar.harvard.edu/filtered-search-widget', 'title' => 'Filtered Search Widget'),
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
