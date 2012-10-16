<?php
/**
 * Expose Main controller
 *
 * @package     Expose
 * @version     4.0
 * @author      ThemeXpert http://www.themexpert.com
 * @copyright   Copyright (C) 2010 - 2011 ThemeXpert
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3
 * @file        core.php
 **/

expose_import('core.layout');

class ExposeCore{

    //common var
    public  $baseUrl;
    public  $basePath;

    public  $templateUrl;
    public  $templatePath;

    public  $exposeUrl;
    public  $exposePath;

    public  $direction;
    public  $templateName;

    //Joomla Instance
    public $document;
    public $app;

    //style and scripts
    public  $styleSheets = array();
    //private  $styles = NULL;
    public  $scripts = array();
    private  $jqDom = NULL;
    private $prefix = '';

    //browser objects
    public $browser;
    public $platform;

    public function __construct(){
        //get the document object
        $this->document = JFactory::getDocument();

        //get the application object
        $this->app = JFactory::getApplication();

        //set the baseurl
        $this->baseUrl = JURI::root(true);

        //base path
        $this->basePath = JPATH_ROOT;

        $this->exposeUrl = $this->baseUrl . '/libraries/expose';
        $this->exposePath = $this->basePath . '/' . 'libraries' . '/' . 'expose';

        //get the current template name
        $this->templateName = $this->getActiveTemplate();
        
        //template url
        $this->templateUrl = $this->baseUrl . '/templates/'. $this->templateName;

        //template path
        $this->templatePath = $this->basePath . '/' . 'templates'. '/' . $this->templateName ;

        //set document direction
        $this->direction = $this->getDirection();

        //detect the platform first
        $this->detectPlatform();

    }


    public static function getInstance()
    {
        static $instance;

        if(!isset($instance))
        {
            $instance = New ExposeCore;
        }

        return $instance;
    }

    public function isAdmin(){
        return $this->app->isAdmin();
    }

    public function finalizedExpose(){

        expose_import('core.processor');

        ExposeProcessor::process('css');
        ExposeProcessor::process('js');

        if(isset ($this->jqDom) AND $this->jqDom != NULL){
            $this->_renderCombinedDom();
        }
        //load custom css from template settings
        $this->setCustomCss();

        //add custom js
        if($this->get('custom-js') != NULL)
        {
            $js = $this->get('custom-js');
            $this->document->addScriptDeclaration($js);
        }

        define('EXPOSE_FINAL', 1);

    }

    //finalized Admin
    public function finalizedAdmin(){
        if($this->isAdmin()){
            expose_import('core.processor');

            ExposeProcessor::process('css');
            ExposeProcessor::process('js');

            $this->_renderCombinedDom();
        }
    }

    //public function to get template params
    public function get($params,$default=NULL){
        if(!$this->isAdmin()){
            $value = ($this->document->params->get($params) != NULL) ? $this->document->params->get($params) : $default;
            return $value;
        }
    }
    
    public function getActiveTemplate(){
        $app = JFactory::getApplication('site');
        return $app->getTemplate();
    }

    public function loadCoreStyleSheets()
    {
        if($this->isAdmin()) return;

        $files = array('joomla.css');
        $this->addLink($files,'css',1);

        //load preset style
        $this->loadPresetStyle();
    }

    public function addLink($file, $type, $priority=10, $media='screen')
    {
        if(is_array($file)){
            foreach($file as $path){
                if($type == 'css')
                {
                    $this->addStyleSheet($path,$priority,$media);
                }else if($type == 'js'){
                    $this->addScript($path, $priority);
                }
            }
            return;
        }

        if($type == 'css')
        {
            $this->addStyleSheet($file,$priority,$media);
        }else if($type == 'js'){
            $this->addScript($file, $priority);
        }

        return;

    }

    private function addStyleSheet( $file, $priority, $media='screen' )
    {
        $obj = $this->styleSheets[$priority][] = new stdClass();

        if(preg_match('/\/\//', $file))
        {
            $obj->media = $media;
            $obj->url = $file;
            $obj->path = $file;
            $obj->source = 'url';
            return;
        }

        jimport('joomla.filesystem.file');

        $type = 'css';

        $burl = $this->exposeUrl . '/interface/' . $type . '/';
        $turl = $this->templateUrl  . '/' .$type . '/';

        if( dirname($file) != '.' AND dirname($file) != '..' )
        {
            //path is included so check its existence and add
            $path = $this->getFilePath($file);

            if(strpos($path, '?'))
            {
                $path = substr($path, 0, strpos($path, '?'));
            }

            if(JFile::exists($path)){
                $obj->path = $path;
                $obj->url = $file;
                $obj->media = $media;
                $obj->source = 'local';
                return;
            }

        }else{

            $tpath = $this->getFilePath($turl.$file);
            $bpath = $this->getFilePath($burl.$file);

            //cross check both base and template path for this file
            if(JFile::exists($tpath))
            {
                $obj->url = $turl.$file;
                $obj->path = $tpath;
                $obj->media = $media;
                $obj->source = 'local';
                return;

            }elseif(JFile::exists($bpath)){
                $obj->url = $burl.$file;
                $obj->path = $bpath;
                $obj->media = $media;
                $obj->source = 'local';
                return;
            }
        }
    }

    private function addScript( $file, $priority)
    {
        $obj = $this->scripts[$priority][] = new stdClass();

        if(preg_match('/\/\//', $file))
        {
            $obj->url = $file;
            $obj->path = $file;
            $obj->source = 'url';

            return;
        }

        jimport('joomla.filesystem.file');

        $type = 'js';

        $burl = $this->exposeUrl . '/interface/' . $type . '/';
        $turl = $this->templateUrl  . '/' .$type . '/';

        if( dirname($file) != '.' AND dirname($file) != '..' )
        {
            //path is included so check its existence and add
            $path = $this->getFilePath($file);

            if(strpos($path, '?'))
            {
                $path = substr($path, 0, strpos($path, '?'));
            }

            if(JFile::exists($path)){
                $obj->path = $path;
                $obj->url = $file;
                $obj->source = 'local';
                return;
            }

        }else{

            $tpath = $this->getFilePath($turl.$file);
            $bpath = $this->getFilePath($burl.$file);

            //cross check both base and template path for this file
            if(JFile::exists($tpath))
            {
                $obj->url = $turl.$file;
                $obj->path = $tpath;
                $obj->source = 'local';
                return;
            }elseif(JFile::exists($bpath)){
                $obj->url = $burl.$file;
                $obj->path = $bpath;
                $obj->source = 'local';
                return;
            }
        }
    }

    /*
     * Get File path from url.
     *
     * This function is borrowed from Gantry GPL theme framework http://www.gantry-framework.org
     * Author: Rockettheme
     *
     **/

    private function getFilePath($url)
    {
        $uri        = JURI::getInstance();
        $base       = $uri->toString( array('scheme', 'host', 'port'));
        $path       = JURI::Root(true);
        if ($url && $base && strpos($url,$base)!==false) $url = preg_replace('|^'.$base.'|',"",$url);
        if ($url && $path && strpos($url,$path)!==false) $url = preg_replace('|^'.$path.'|',"",$url);
        if (substr($url,0,1) != '/') $url = '/'.$url;
        $filepath = JPATH_SITE.$url;
        return $filepath;

    }

    public function loadPresetStyle()
    {

        if( $this->isAdmin() ) return;

        //if(defined('EXPOSE_FINAL')) return;
        $preset_file = (isset ($_COOKIE[$this->templateName.'_style'])) ? $_COOKIE[$this->templateName.'_style'] : $this->get('style');
        if(isset ($_REQUEST['style'])){
            setcookie($this->templateName.'_style',$_REQUEST['style'],time()+3600,'/');
            $preset_file = $_REQUEST['style'];
        }
        if($preset_file == '-1' OR $preset_file == 'none') return;

        $path = $this->templateUrl . '/css/styles/';
        $file = $path . $preset_file.'.css';
        $this->addLink($file, 'css');
    }


    public function addjQDom($js=NULL)
    {
        if($js != NULL){
            $this->jqDom .= "\t\t\t" . $js ."\n";
        }
    }

    private function _renderCombinedDom()
    {
        $jqNoConflict = "\n\t\t".'jQuery.noConflict();'."\n";
        $dom = '';
        //add noConflict
        $dom .= $jqNoConflict;
        $dom .= "\n\t\t" . 'jQuery(document).ready(function($){'."\n".$this->jqDom."\n\t\t});";

        $this->document->addScriptDeclaration($dom);
    }

    public function addjQuery()
    {
        //come form admin? just add jquery without asking any question because jquery is heart of
        //expose admin
        if($this->isAdmin()){

            $file = 'jquery-1.7.2.min.js';
            $this->addLink($file,'js',1);

            return;
        }

        //we will not load jquery on mobile device
        //if($this->platform == 'mobile') return;
        $version = $this->get('jquery-version');
        $cdn = $this->get('jquery-source');
        $file = 'https://ajax.googleapis.com/ajax/libs/jquery/'.$version.'/jquery.min.js';

        if( $this->get('jquery-enabled') ){

            if( EXPOSE_JVERSION == '25')
            {
                if( !$this->app->get('jQuery') )
                {
                    if( $cdn == 'local')
                    {
                        $file = 'jquery-'.$version.'.min.js';
                    }

                    $this->app->set('jQuery',$version);

                }
                $this->addLink($file,'js',1);
            }else{
                if( $cdn = 'google-cdn')
                {
                    $this->addLink($file,'js',1);
                }else{
                    JHtml::_('jquery.framework');
                }
            }
        }

        return;
    }

    private function getDirection()
    {
        if(defined('EXPOSE_FINAL')) return;
        if(isset ($_REQUEST['direction'])){
            setcookie($this->templateName.'_direction', $_REQUEST['direction'], time()+3600, '/');
            return $_REQUEST['direction'];
        }
        if(!isset($_COOKIE[$this->templateName.'_direction'])){
            if($this->document->direction == 'rtl' OR $this->get('rtl-support')){
                return 'rtl';
            }else{
                return 'ltr';
            }
        }
        else{
            return $_COOKIE[$this->templateName.'_direction'];
        }
    }

    private function setCustomCss()
    {
        if(defined('EXPOSE_FINAL')) return;

        $css = '';

        if(isset ($_REQUEST['layoutsType']))
        {
            setcookie($this->templateName.'_layoutsType',$_REQUEST['layoutsType'],time()+3600,'/');
            $layoutType = $_REQUEST['layoutsType'];
        }

        if( ($this->get('custom-css') != NULL))
        {
            $css .= $this->get('custom-css');
        }

        $this->addInlineStyles($css);

    }

    public function setPrefix($name)
    {
        $this->prefix = $name;
    }

    public function getPrefix()
    {
        if($this->prefix == '')
        {
            $this->setPrefix('ex-');
        }

        return $this->prefix;
    }

    public function addInlineStyles($content){
        $this->document->addStyleDeclaration($content);
    }


    public function displayHead()
    {
        if(defined('EXPOSE_FINAL')) return;
        if(!$this->isAdmin()){
            if($this->get('remove-joomla-metainfo'))
            {
                $this->document->setGenerator('');
            }
            //output joomla head
            echo '<jdoc:include type="head" />';
            echo "<link rel=\"apple-touch-icon-precomposed\" href=". $this->templateUrl. '/images/apple_touch_icon.png' ." />";

            if( $this->isResponsive() )
            {
                $this->document->setMetaData('viewport','width=device-width, initial-scale=1.0');
            }

        }
    }

    public function generateBodyClass()
    {
        $url            = JURI::getInstance();
        $itemid         = $url->getVar('Itemid');
        $menu           = $this->app->getMenu();
        $active         = $menu->getItem($itemid);
        $params         = $menu->getParams( $active->id );
        $class          = NULL;



        $class         .= ($this->get('style') == '-1') ? 'style-none' : $this->get('style');
        $class         .= ' align-'.$this->direction;
        $class         .= ' page-id-'. (isset($active) ? $active->id : $menu->getDefault()->id);

        //Add class of homepage if it's home
        if ($menu->getActive() == $menu->getDefault())
        {
            $class     .= ' homepage ';
        }else{
            $view       = $url->getVar('view');
            $component      = str_replace('_','-', $url->getVar('option'));
            $class     .= ' ' . $component . '-' . $view;
        }


        $class         .= ' ' . strtolower($this->browser->getBrowser());
        $class         .= ($this->displayComponent()) ? '' : ' com-disabled';
        $class         .= ' ' . $params->get( 'pageclass_sfx' );

        return 'class="'.$class.'"';
    }
    
    public function displayComponent()
    {

        if($this->get('component-disable'))
        {
            $component = JRequest::getCmd('option');
            if($component == 'com_search' OR $component == 'com_finder')
            {
                return TRUE;
            }

            $ids = $this->get('component-disable-menu-ids');

            if(!empty($ids))
            {
                $menuIds = explode(',',$ids);
                $currentMenuId = JRequest::getInt('Itemid');
                if(in_array($currentMenuId, $menuIds))
                {
                    return FALSE;

                }else{
                    return TRUE;
                }
            }else{
                return TRUE;
            }
        }else{
            return TRUE;
        }
    }
    /*
     * Get sidebar width for % values
     *
     * @since       @3.0
     * @deprecated  @4.5
     **/
    public function getSidebarsWidth($position)
    {
        $width = array();
        $layout = ExposeLayout::getInstance();
        $width = $layout->getModuleSchema($position);
        return $width[0];

    }

    public function getComponentWidth()
    {
        $grids = array();
        $layout = ExposeLayout::getInstance();
        $grids['a'] = 0;
        $grids['b'] = 0;
        $grids['component'] = 0;

        if($layout->countModulesForPosition('sidebar-a') OR $layout->countWidgetsForPosition('sidebar-a'))
        {
            $width = explode(':',$this->get('sidebar-a'));
            $grids['a'] = $width[1];

        }

        if($layout->countModulesForPosition('sidebar-b') OR $layout->countWidgetsForPosition('sidebar-b'))
        {
            $width = explode(':',$this->get('sidebar-b'));
            $grids['b'] = $width[1];
        }

        $mainBodyWidth = 12 - ($grids['a'] + $grids['b']);

        if($this->isEditpage())
        {
            $mainBodyWidth = 12;
        }

        $width['component']= $mainBodyWidth;
        $width['sidebar-a'] = $grids['a'];
        $width['sidebar-b'] = $grids['b'];

        return $width;

    }

    public function countModules($position)
    {
        $layout = ExposeLayout::getInstance();
        return $layout->countModules($position);
    }

    public function renderModules($position, $inset=FALSE)
    {
        $layout = ExposeLayout::getInstance();
        //check for the inset module position, used in content-top/bottom
        if($inset)
        {
            //Get the component grid
            $com = $this->getComponentWidth();
            $grid = $com['component'];

            $layout->renderModules($position, TRUE, $grid);
        }else{
            $layout->renderModules($position);
        }

    }

    public function renderBody()
    {
        $layout = ExposeLayout::getInstance();
        $layout->renderBody();
    }

    public function detectPlatform()
    {
        expose_import('libs.browser');
        $this->browser = new ExposeBrowser();
        $browserName = $this->browser->getBrowser();

        //we'll consider 2 mobile now iPhone and Android, iPad will treat as regular desktop device
        if($this->get('iphone-enabled') AND $this->browser->isMobile() AND $browserName == 'iPhone')
        {
            $this->platform = 'mobile';

        }elseif($this->get('android-enabled') AND $this->browser->isMobile() AND ($browserName == 'android' OR $browserName == 'Android')){

            $this->platform = 'mobile';

        }else{
            $this->platform = 'desktop';
        }

    }

    public function isResponsive()
    {
        return $this->get('responsive-enabled',1);
    }

    public function isEditpage()
    {
        //joomla content edit layout
        $layout = JRequest::getCmd('layout');
        if($layout == 'edit')
        {
            return TRUE;
        }
    }
}