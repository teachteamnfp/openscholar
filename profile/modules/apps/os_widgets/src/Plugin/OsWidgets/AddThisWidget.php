<?php

namespace Drupal\os_widgets\Plugin\OsWidgets;

use Drupal\os_widgets\OsWidgetsBase;
use Drupal\os_widgets\OsWidgetsInterface;

/**
 * Class AddThisWidget.
 *
 * @OsWidget(
 *   id = "addthis_widget",
 *   title = @Translation("AddThis")
 * )
 */
class AddThisWidget extends OsWidgetsBase implements OsWidgetsInterface {

  /**
   * {@inheritdoc}
   */
  public function buildBlock(&$build, $block_content) {
    if (empty($block_content)) {
      return;
    }
    $field_addthis_display_style_values = $block_content->get('field_addthis_display_style')->getValue();
    $display_style = $field_addthis_display_style_values[0]['value'];
    $html = '';
    switch ($display_style) {
      case 'buttons':
        $image_path = '/' . $this->getModulePath() . '/images/addthis/addthis_smallbar.png';
        $html = '<a class="addthis_button" href=" http://www.addthis.com/bookmark.php?v=250&amp;pubid=xa-4e9eeefa6983da55 "><img src="' . $image_path . '" width="125" height="16" alt="Bookmark and Share" style="border:0"/></a>';
        break;

      case 'toolbox_small':
        $html = '<div class="addthis_toolbox addthis_default_style">';
        $html .= '<a class="addthis_button_facebook"></a>';
        $html .= '<a class="addthis_button_twitter"></a>';
        $html .= '<a class="addthis_button_email"></a>';
        $html .= '<a class="addthis_button_linkedin"></a>';
        $html .= '<a class="addthis_button_google_plusone"></a>';
        $html .= '</div>';
        break;

      case 'toolbox_large':
        $html = '<div class="addthis_toolbox addthis_default_style addthis_32x32_style">';
        $html .= '<a class="addthis_button_facebook"></a>';
        $html .= '<a class="addthis_button_twitter"></a>';
        $html .= '<a class="addthis_button_email"></a>';
        $html .= '<a class="addthis_button_linkedin"></a>';
        $html .= '<a class="addthis_button_google_plusone"></a>';
        $html .= '</div>';
        break;

      case 'numeric':
        $html = '<div class="addthis_toolbox addthis_default_style">';
        $html .= '<a class="addthis_button_facebook_like" fb:like:layout="button_count"></a>';
        $html .= '<a class="addthis_button_tweet"></a>';
        $html .= '<a class="addthis_button_google_plusone" g:plusone:size="medium"></a>';
        $html .= '<a class="addthis_counter addthis_pill_style"></a></div>';
        break;

      case 'counter':
        $html = '<div class="addthis_toolbox addthis_default_style "><a class="addthis_counter"></a></div>';
        break;
    }
    $build['addthis']['#attached']['library'][] = 'os_widgets/addthis';
    $build['addthis']['#markup'] = $html;
  }

  /**
   * Get module path, able to create Mock.
   */
  public function getModulePath() {
    return drupal_get_path('module', 'os_widgets');
  }

}
