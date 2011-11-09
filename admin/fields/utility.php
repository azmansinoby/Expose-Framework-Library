<?php
/**
 * @package     Expose
 * @version     2.0    Mar 15, 2011
 * @author      ThemeXpert http://www.themexpert.com
 * @copyright   Copyright (C) 2010 - 2011 ThemeXpert
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3
 * @filesource
 * @file        utility.php
 **/

// Ensure this file is being included by a parent file
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldUtility extends JFormField{

    protected  $type = 'Utility';

    protected function getInput(){

        //make expose object global
        global $expose;
            
        // Initialize some field attributes.
        $action     = $this->element['action'];

        if($action == 'boot'){
            //load expose bootstrap
            jimport('expose.bootstrap');
            $expose->addScript($expose->exposeUrl.'admin/widgets/jquery.blockUI.js');
            $expose->addScript($expose->exposeUrl.'admin/widgets/expose.js');
            //load expose.css file
            $expose->addStyle($expose->exposeUrl.'admin/widgets/expose.css');
        }
        else if($action == 'finalize'){
            //load main expose js file
            $expose->addScript($expose->exposeUrl.'admin/widgets/jquery.tools.min.js');
            
            //finalize addmin
            $expose->finalizedAdmin();
        }
    }
}

