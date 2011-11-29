<?php
/**
 * @package     Expose
 * @version     3.0.0
 * @author      ThemeXpert http://www.themexpert.com
 * @copyright   Copyright (C) 2010 - 2011 ThemeXpert
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3
 **/

/**
 * Abstract class for Gists
 **/
abstract class ExposeGist extends ExposeCore{

    protected $name = NULL;
    protected $enabled = NULL;
    protected $position = NULL;


    public function isEnabled()
    {
        if(!isset($this->enabled))
        {
            $this->enabled = (int) $this->get('enabled');
        }

        return $this->enabled;
    }

    public function getPosition()
    {
        if(!isset($this->position))
        {
            $this->position = $this->get('position');
        }

        return $this->position;
    }

    public function isInPosition($position)
    {
        if ($this->getPosition() == $position) return TRUE;

        return FALSE;
    }

    public function get($param)
    {
        $field = $this->name . '-' .$param;

        return parent::get($field);
    }

    public function init(){

    }

    public function render(){
        
    }


}
