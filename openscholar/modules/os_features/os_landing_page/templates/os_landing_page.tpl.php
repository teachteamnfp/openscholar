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
  <div class="logo-menu-bar-wrapper row">
    <div class="logo">
      <a href="<?php echo $variables['schoolDetails']['path_to_school_main_website']; ?>">
      <img src="<?php echo $variables['schoolDetails']['path_to_logo']; ?>"></a>
    </div>
    <div class="menu">
      <ul>
        <li><a href="link">Contact Support</a></li>
        <li><a href="https://help.theopenscholar.com/" target="_blank">Documentation</a></li>
        <li><a href="https://help.theopenscholar.com/video-tutorials" target="_blank">Video Tutorials</a></li>
        <li><a href="/user" class="log-in">Log In</a></li>
      </ul>
    </div>
  </div>
  <!-- region content top -->
  <div class="hero row" style="background-color:<?php echo $variables['schoolDetails']['school_primary_color']; ?>;">
    <div class="heading">
      <h1>Create and Manage Your Own Beautiful Website</h1>
      <h2>For labs, research centers, departments, schools, faculty and students</h2>
      <button>Create a Wesbite</button>
    </div>
  </div>
  <div class="visual-image"><img src="/profiles/openscholar/modules/os_features/os_landing_page/includes/images/devices.png"></div>
  <!-- region content first -->
  <div class="panel-first row">
    <div class="grid-wrapper">
    	<h2>Feature Highlights</h2>
      <div class="block-1"><h3 class="widgets">
	Widgets
</h3>
<span>Display snippets of larger pieces of content as:</span>

<ul><li>
		Lists
	</li>
	<li>
		Content sliders
	</li>
	<li>
		Other media such as slideshows or videos
	</li>
</ul></div>
      <div class="block-2"><h3 class="choose-theme">
	CHOOSE FROM MANY THEME DESIGNS
</h3>

<ul><li>
		Select from a wide array of professional templates or create your own unique theme
	</li>
	<li>
		Templates to make construction simple while maintaining your individual style
	</li>
</ul></div>
      <div class="block-3"><h3 class="drag-drop">
	DRAG AND DROP<br>INTERFACE
</h3>
<span>The "drag-and-drop" tool allows you to:</span>

<ul><li>
		Easily arrange the content presentation on any site
	</li>
	<li>
		Design site-wide default layouts
	</li>
	<li>
		Create unique layouts for different sections of your site
	</li>
</ul></div>
      <div class="block-4"><h3 class="share-content">
	SHARE CONTENT TO SOCIAL MEDIA
</h3>

<ul><li>
		Share&nbsp;your content on social networks such as Facebook and Twitter
	</li>
	<li>
		Extend the reach of your research, publications, speaking engagements, or departmental events
	</li>
</ul></div>
    </div>
  </div>
  <!-- region content second -->
  <div class="panel-second row">
    <div class="grid-wrapper">
      <h2>Feaures</h2>
      <div class="block-1">TBD</div>
      <div class="block-2"><iframe width="560" height="315" src="https://www.youtube.com/embed/tG6lcd73kN0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe></div>
     
    </div>
  </div>
  <!-- region footer -->
  <div class="panel-footer row">
    <div class="grid-wrapper">
      <h2>Feaures</h2>
      <div class="block-1">TBD</div>
      <div class="block-2">TBD</div>
      <div class="block-3">TBD</div>
      <div class="block-4">TBD</div>
    </div>
  </div>
  <!-- region branding footer -->
 
    <div class="branding_footer row">
      <div class="branding-container">
        <div class="copyright">
          <span class="copyright">Copyright &copy; <?php echo date('Y'); ?> <?php echo $variables['schoolDetails']['name_of_school']; ?></span>  | 
          <a href="<?php echo $variables['schoolDetails']['path_to_schools_accessibility_policy']; ?>">Accessibility</a> | 
          <a href="<?php echo $variables['schoolDetails']['path_to_schools_reporting_copyright_infringements']; ?>">Report Copyright Infringement</a></div>
      </div>
    </div>

</div>