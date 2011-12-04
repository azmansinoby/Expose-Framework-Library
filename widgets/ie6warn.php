<?php

/**
 * @package     Expose
 * @version     2.0
 * @author      ThemeXpert http://www.themexpert.com
 * @copyright   Copyright (C) 2010 - 2011 ThemeXpert
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3
 **/

//prevent direct access
defined ('EXPOSE_VERSION') or die ('resticted aceess');

//import parent gist class
expose_import('core.widget');

class ExposeWidgetIe6Warn extends ExposeWidget{

    public $name = 'ie6warn';

    public function isInMobile()
    {
        return FALSE;
    }

    public function init()
    {
        if($this->browser->getBrowser() == ExposeBrowser::BROWSER_IE AND $this->browser->getVersion() == 6)
        {
            //add ie6warn js
            $this->document->addScript($this->exposeUrl . '/interface/js/ie6warn.js');
            //add js to onload method
            $this->document->addScriptDeclaration('window.onload=sevenUp.plugin.black.test( options, callback );');
        }
    }
}