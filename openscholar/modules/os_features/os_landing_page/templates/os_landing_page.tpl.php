<?php
/**
* @Name: os_landing_page.tpl.php
* @created: 21/05/2018
* @Author: 
* @Version: 1.0
**/
?>
<div id="landing-page-content-wrapper">
  <!-- menu bar -->
  <div id="logo-menu-bar-wrapper">
    <div class="logo">
      <a href="<?php echo $variables['schoolDetails']['path_to_school_main_website']; ?>">
      <img src="<?php echo $variables['schoolDetails']['path_to_logo']; ?>"></a>
    </div>
    <div class="menu">
      <ul>
        <li><a href="link">Link 1</a></li>
        <li><a href="link">Link 2</a></li>
        <li><a href="link">Link 3</a></li>
        <li><a href="link">Link 4</a></li>
        <li><a href="link">Link 5</a></li>
      </ul>
    </div>
  </div>
  <!-- region content top -->
  <div id="hero">
    <div class="heading">
      <h1>Header 1</h1>
      <h2>Header 2</h2>
      <button>Create a Wesbite</button>
    </div>
    <div class="visual-image">TBD</div>
  </div>
  <!-- region content first -->
  <div id="panel-first">
    <div class="grid-wrapper">
      <div class="block-1">TBD</div>
      <div class="block-2">TBD</div>
      <div class="block-3">TBD</div>
      <div class="block-4">TBD</div>
    </div>
  </div>
  <!-- region content second -->
  <div id="panel-second">
    <div class="grid-wrapper">
      <h2>Feaures</h2>
      <div class="block-1">TBD</div>
      <div class="block-2">TBD</div>
      <div class="block-3">TBD</div>
      <div class="block-4">TBD</div>
    </div>
  </div>
  <!-- region footer -->
  <div id="panel-footer">
    <div class="grid-wrapper">
      <h2>Feaures</h2>
      <div class="block-1">TBD</div>
      <div class="block-2">TBD</div>
      <div class="block-3">TBD</div>
      <div class="block-4">TBD</div>
    </div>
  </div>
  <!-- region branding footer -->
 
    <div id="branding_footer">
      <div class="branding-container">
        <div class="copyright">
          <span class="copyright">Copyright &copy; <?php echo date('Y'); ?> <?php echo $variables['schoolDetails']['name_of_school']; ?></span>  | 
          <a href="<?php echo $variables['schoolDetails']['path_to_schools_accessibility_policy']; ?>">Accessibility</a> | 
          <a href="<?php echo $variables['schoolDetails']['path_to_schools_reporting_copyright_infringements']; ?>">Report Copyright Infringement</a></div>
      </div>
    </div>

</div>