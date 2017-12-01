<?php
/**
 * @file
 * hwpi.strongarm.inc
 */

/**
 * Implements hook_strongarm().
 */
function hwpi_strongarm() {
  $export = array();

/**
 * List of Harvard documentation help link
 */

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_redirect_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/global-site-settings#url-redirects';
  $export['help_redirect_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'training_signup_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/one-page-themes';
  $export['training_signup_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_key_link';
  $strongarm->value = 'http://docs.openscholar.harvard.edulogging-your-site#linking-your-harvardkey-to-your-openscholar-account';
  $export['help_key_link'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_booklet_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/booklet#booklet-app-settings';
  $export['help_booklet_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'dataverse_default_url';
  $strongarm->value = 'https://dataverse.harvard.edu/';
  $export['dataverse_default_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_dataverse_widget_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/dataverse-dataset-widget';
  $export['help_dataverse_widget_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_dataverse_citation_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/dataverse-dataset-citation';
  $export['help_dataverse_citation_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_dataverse_list_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/dataverse-list-widget';
  $export['help_dataverse_list_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_dataverse_search_box_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/dataverse-search-box';
  $export['help_dataverse_search_box_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_faq_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/faq-app#faq-app-settings';
  $export['help_faq_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_events_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/events-app#events-app-settings';
  $export['help_events_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_private_files_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/private-files#setting-options';
  $export['help_private_files_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_links_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/links-app#link-app-settings';
  $export['help_links_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'doc_base_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu';
  $export['doc_base_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_site_visibility_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/private-files#setting-options';
  $export['help_site_visibility_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_custom_domain_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/apply-a-custom-domain';
  $export['help_custom_domain_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_subsite_theme_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/subsite-themes';
  $export['help_subsite_theme_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_search_engine_optimization_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/global-site-settings#search-engine-optimization';
  $export['help_search_engine_optimization_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_favicon_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/adding-site-favicon';
  $export['help_favicon_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_breadcrumbs_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/enable-breadcrumbs';
  $export['help_breadcrumbs_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_blog_comments_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/blogs#blog-settings';
  $export['help_blog_comments_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_analytics_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/global-site-settings#google-analytics';
  $export['help_analytics_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_long_list_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/global-site-settings#long-list-display';
  $export['help_long_list_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_publications_url';
  $strongarm->value = 'https://docs.openscholar.harvard.edu/publications-settings';
  $export['help_publications_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_taxonomy_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/categorizing-your-content-taxonomy';
  $export['help_taxonomy_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'privacy_policy_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/global-site-settings#privacy-policy';
  $export['privacy_policy_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_site_login_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/logging-your-site';
  $export['help_site_login_url'] = $strongarm;


/**
 * List of cp related documentation help link
 */

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_cp_appearance_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/managing-look-your-site';
  $export['help_cp_appearance_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_cp_build_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/content-apps-and-widgets';
  $export['help_cp_build_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_cp_build_layout_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/customizing-your-site-layout';
  $export['help_cp_build_layout_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_cp_build_menu_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/menus';
  $export['help_cp_build_menu_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_cp_build_taxonomy_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/categorizing-your-content-taxonomy';
  $export['help_cp_build_taxonomy_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_cp_content_files_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/file-management';
  $export['help_cp_content_files_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_cp_settings_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/site-settings';
  $export['help_cp_settings_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_cp_support_url';
  $strongarm->value = 'http://hwp.harvard.edu/pages/support';
  $export['help_cp_support_url'] = $strongarm;

/**
 * Harvard sitewide name,URL
 */

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'sitewide_name';
  $strongarm->value = 'Harvard Web Publishing';
  $export['sitewide_name'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'university_base_url';
  $strongarm->value = 'http://www.harvard.edu/';
  $export['university_base_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'university_url';
  $strongarm->value = 'http://hwp.harvard.edu/';
  $export['university_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'contact_url';
  $strongarm->value = 'http://hwp.harvard.edu/contact';
  $export['contact_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'site_url';
  $strongarm->value = 'http://openscholar.harvard.edu';
  $export['site_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'support_training_url';
  $strongarm->value = 'http://hwp.harvard.edu/pages/support#main-content';
  $export['support_training_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'site_access';
  $strongarm->value = 'http://accessibility.harvard.edu/';
  $export['site_access'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'copyright_infring';
  $strongarm->value = 'http://www.harvard.edu/reporting-copyright-infringements';
  $export['copyright_infring'] = $strongarm;

/**
 * List of Harvard app related documentation help link
 */

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_news_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/import-news-rss';
  $export['help_os_news_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_biocv_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/biocv';
  $export['help_os_biocv_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_blog_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/blog';
  $export['help_os_blog_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_booklets_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/booklet';
  $export['help_os_booklets_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_classes_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/classes';
  $export['help_os_classes_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_events_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/events-app';
  $export['help_os_events_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_faq_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/faq-app';
  $export['help_os_faq_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_files_private_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/private-files';
  $export['help_os_files_private_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_pages_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/pages';
  $export['help_os_pages_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_image_gallery';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/image-management';
  $export['help_os_image_gallery'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_links_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/links';
  $export['help_os_links_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_profiles_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/people-profiles';
  $export['help_os_profiles_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_publications_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/publications';
  $export['help_os_publications_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_presentations_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/presentations';
  $export['help_os_presentations_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_reader_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/rss-feed';
  $export['help_os_reader_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_software_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/software';
  $export['help_os_software_url'] = $strongarm;

/**
 * Harvard  upcoming events
 */
  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'upcoming_events';
  $strongarm->value = 'http://hwp.harvard.edu/calendar/upcoming';
  $export['upcoming_events'] = $strongarm;

/**
 * Dataverse URLs
 */
  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'old_dataverse_url';
  $strongarm->value = 'http://dvn.iq.harvard.edu/';
  $export['old_dataverse_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'modified_dataverse_url';
  $strongarm->value = 'http://thedata.harvard.edu/';
  $export['modified_dataverse_url'] = $strongarm;

/**
 * Hard-coded names.
 */
  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'highest_parent_item';
  $strongarm->value = 'HARVARD.EDU';
  $export['highest_parent_item'] = $strongarm;

/**
 * List of Harvard widget related documentation help link
 */

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_custom_texthtml_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/custom-texthtml-widget';
  $export['help_os_custom_texthtml_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_taxonomy_fbt_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/taxonomy-widget';
  $export['help_os_taxonomy_fbt_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_featured_posts_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/featured-posts-widget';
  $export['help_os_featured_posts_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_search_solr_more_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/more';
  $export['help_os_search_solr_more_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_sv_list_box_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/list-of-posts-widget';
  $export['help_os_sv_list_box_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_sv_list_file_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/custom-texthtml-widget';
  $export['help_os_sv_list_file_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_feedreader_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/taxonomy-widget';
  $export['help_os_feedreader_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_pub_year_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/featured-posts-widget';
  $export['help_os_pub_year_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_booktoc_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/more';
  $export['help_os_booktoc_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_pub_author_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/list-of-posts-widget';
  $export['help_os_pub_author_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_sv_list_file_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/list-files';
  $export['help_os_sv_list_file_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_feedreader_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/feed-reader-widget';
  $export['help_os_feedreader_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_pub_year_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/publications-year-widget';
  $export['help_os_pub_year_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_booktoc_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/booklet-table-contents-widget';
  $export['help_os_booktoc_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_pub_author_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/publications-authors-widget';
  $export['help_os_pub_author_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_pub_types_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/publications-types-widget';
  $export['help_os_pub_types_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_slideshow_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/photo-slideshow-widget';
  $export['help_os_slideshow_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_embed_media_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/embed-media-widget';
  $export['help_os_embed_media_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_rss_feed_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/rss-feed-widget';
  $export['help_os_rss_feed_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_mailchimp_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/embedding-mailchimp-newsletter-sign';
  $export['help_os_mailchimp_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_follow_me_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/follow-me-widget';
  $export['help_os_follow_me_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_twitter_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/twitter-widget';
  $export['help_os_twitter_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_bio_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/bio-widget';
  $export['help_os_bio_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_addthis_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/addthis-widget';
  $export['help_os_addthis_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_accordion_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/accordion-widget';
  $export['help_os_accordion_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_tabs_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/tabs-widget';
  $export['help_os_tabs_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_random_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/random-widget';
  $export['help_os_random_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_slider_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/widget-slider';
  $export['help_os_slider_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_columns_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/columns-widget';
  $export['help_os_columns_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_facetapi_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/filtering-content-faceted-taxonomy-widget';
  $export['help_os_facetapi_url'] = $strongarm;  

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_filtered_search_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/filtered-search-widget';
  $export['help_os_filtered_search_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_dataverse_dataset_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/dataverse-dataset-widget';
  $export['help_os_dataverse_dataset_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_dataverse_citation_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/dataverse-dataset-citation';
  $export['help_os_dataverse_citation_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_dataverse_list_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/dataverse-list-widget';
  $export['help_os_dataverse_list_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'help_os_dataverse_search_url';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/dataverse-search-box';
  $export['help_os_dataverse_search_url'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'university_name';
  $strongarm->value = 'Harvard';
  $export['university_name'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'pin_authenticate';
  $strongarm->value = 'https://www.pin1.harvard.edu/pin/authenticate';
  $export['pin_authenticate'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'getting_started_os';
  $strongarm->value = 'http://docs.openscholar.harvard.edu/getting-started-with-openscholar';
  $export['getting_started_os'] = $strongarm;

/**
 * DASH
 */
  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'dash_base_url';
  $strongarm->value = 'http://dash.harvard.edu';
  $export['dash_base_url'] = $strongarm;

  return $export;
}