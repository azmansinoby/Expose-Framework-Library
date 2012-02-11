<?php
/**
 * @package     Expose
 * @version     3.0.0
 * @author      ThemeXpert http://www.themexpert.com
 * @copyright   Copyright (C) 2010 - 2011 ThemeXpert
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3
 **/

//prevent direct access
defined ('EXPOSE_VERSION') or die ('resticted aceess');

global $expose;
$prefix = $expose->getPrefix();
?>

<div id="ex-main" class="<?php echo $prefix;?>row">

    <jdoc:include type="message" />

    <div id="ex-mainbody" class="ex-column">

        <?php /**Begin Content top**/ if($expose->countModules('contenttop')): ?>
        <!--Start Content Top Modules-->
        <div id="<?php echo $prefix;?>contenttop" class="clearfix">
            <?php $expose->renderModules('contenttop'); ?>
        </div>
        <!--End Content top Modules-->
        <?php /**End Content top **/ endif;?>

        <?php if($expose->displayComponent()): ?>
        <div id="ex-component" role="article" class="clearfix">
            <div class="ex-container">
                <div class="ex-block">
                    <jdoc:include type="component" />
                </div>
            </div>
        </div>
        <?php endif;?>

        <?php /**Begin Content bottom**/ if($expose->countModules('contentbottom')): ?>
        <!--Start Content Bottom Modules-->
        <div id="<?php echo $prefix;?>contentbottom" class="clearfix">
            <?php $expose->renderModules('contentbottom'); ?>
        </div>
        <!--End Content Bottom Modules-->
        <?php /**End Content bottom **/ endif;?>

    </div>
    


</div>