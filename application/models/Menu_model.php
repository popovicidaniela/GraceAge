<?php
 
class Menu_model extends CI_Model {
 
    private $menu_items;
    private $help_text;
    private $help_item;
     
    function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->lang->load('elderly', $this->session->Language);
        $this->help_item = array(array('id'=>'Help_button','func'=> 'Home_help', 'name' => $this->lang->line('help'),'iconName' => 'fa fa-question-circle', 'title' => 'Help', 'link' => 'help', 'className' => 'inactive'));
        $this->menu_items = array(
            array('id'=>'Home_button', 'name' => $this->lang->line('return_to_home'),'iconName' => 'fa fa-repeat', 'title' => 'Go Home', 'link' => 'index', 'className' => 'active'),
            array('id'=>'Tips_button','name' => 'Tips', 'iconName' => 'fa fa-lightbulb-o','title' => 'Look at the tips', 'link' => 'tips', 'className' => 'inactive'),
            array('id'=>'Questionnaire_button','name' => 'Questionnaire','iconName' => 'fa fa-list-alt', 'title' => 'Fill in the questionnaire', 'link' => 'questionnaire', 'className' => 'inactive'),  
        );
    }
     
    function set_active($menutitle) {
        foreach ($this->menu_items as &$item) { // reference to item!!
            if (strcasecmp($menutitle, $item['name']) == 0) {
                $item['className'] = 'active';
            } else {
                $item['className'] = 'inactive';
            }
        }
    }
    
    function get_helpText($menutitle) {
        $this->help_text = array(array('id'=>'helpText', 'text' => $this->lang->line($menutitle )));
        return $this->help_text;
    }
    
    function get_helpItem($menutitle)
    {
        $this->help_item = array(array('id'=>'Help_button','func'=> $menutitle, 'name' => $this->lang->line('help'),'iconName' => 'fa fa-question-circle', 'title' => 'Help', 'link' => 'help', 'className' => 'inactive'));
        return $this->help_item;
    }
    
    function get_menuitems($menutitle='Home') {
        $this->set_active($menutitle);
        return $this->menu_items;
    }
     
}
