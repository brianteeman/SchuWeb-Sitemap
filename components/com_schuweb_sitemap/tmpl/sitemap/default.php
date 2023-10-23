<?php
/**
 * @version         sw.build.version
 * @copyright   Copyright (C) 2019 - 2022 Sven Schultschik. All rights reserved
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 * @author          Sven Schultschik (extensions@schultschik.de)
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Create shortcut to parameters.
$params = $this->params;

?>
<div id="SchuWeb_Sitemap">
<?php if ($params->get('show_page_heading', 1) && $params->get('page_heading') != '') : ?>
    <h1>
        <?php echo $this->escape($params->get('page_heading')); ?>
    </h1>
<?php endif; ?>

<?php if ($params->get('showintro', 1) )  : ?>
    <?php echo $this->item->introtext; ?>
<?php endif; ?>

    <?php echo $this->loadTemplate('items'); ?>

<?php if ($params->get('include_link', 1) )  : ?>
    <div class="muted" style="font-size:10px;width:100%;clear:both;text-align:center;">Powered by <a target="_blank" href="https://extensions.schultschik.com/">SchuWeb Sitemap</a></div>
<?php endif; ?>

    <span class="article_separator">&nbsp;</span>
</div>