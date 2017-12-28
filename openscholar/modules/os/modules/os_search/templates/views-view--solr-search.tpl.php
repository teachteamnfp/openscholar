<div class="<?php print $classes; ?>">
    <?php print render($title_prefix); ?>
    <?php if ($title): ?>
        <?php print $title; ?>
    <?php endif; ?>
    <?php print render($title_suffix); ?>

    <?php if ($header): ?>
        <div class="view-header">
            <?php print $header; ?>
        </div>
    <?php endif; ?>

    <?php if ($exposed): ?>
        <div class="container-inline form-wrapper">
            <?php print $exposed; ?>
        </div>
    <?php endif; ?>

    <?php if ($rows): ?>
        <h2>Search results</h2>
        <div class="search-results">
            <?php print $rows; ?>
        </div>
    <?php else: ?>
        <h2><?php print t('Your search yielded no results');?></h2>
        <?php print search_help('search#noresults', drupal_help_arg()); ?>
    <?php endif; ?>

    <?php if ($pager): ?>
        <?php print $pager; ?>
    <?php endif; ?>

    <?php if ($footer): ?>
        <div class="view-footer">
        <?php print $footer; ?>
        </div>
    <?php endif; ?>
</div>
