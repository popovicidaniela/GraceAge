<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Caregiver_Menu_model
 *
 * @author orditech
 */
class Caregiver_Menu_model extends CI_Model{
    
    private $caregiver_menu_items;
    private $caregiver_profile_items;
    private $caregiver_profile_class;
    
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->lang->load('caregiver_menu', $this->session->Language);
        $this->caregiver_menu_items = array(
            array('showID' => "",'name' => $this->lang->line('caregiver_menu_general'), 'title' => 'Algemene informatie', 'link' => 'index', 'className' => 'active', 'gridClass' => 'col-lg-2', 'text' => $this->lang->line('caregiver_menu_general')),
            array('showID' => "",'name' => $this->lang->line('caregiver_menu_personal'), 'title' => 'Persoonlijk', 'link' => 'personal', 'className' => 'inactive', 'gridClass' => 'col-lg-2', 'text' => $this->lang->line('caregiver_menu_personal')),
            array('showID' => "id = 'tipsTab'",'name' => $this->lang->line('caregiver_menu_tips'), 'title' => 'Bekijk de tips', 'link' => 'tips', 'className' => 'inactive', 'gridClass' => 'col-lg-2', 'text' => $this->lang->line('caregiver_menu_tips')),
            array('showID' => "",'name' => $this->lang->line('caregiver_menu_reward'), 'title' => 'Bekijk de reward', 'link' => 'rewards', 'className' => 'inactive', 'gridClass' => 'col-lg-2','text' => $this->lang->line('caregiver_menu_reward')),
            array('showID' => "id = 'settingsMobile'", 'name' => $this->lang->line('caregiver_menu_profile'), 'title' => 'Afmelden', 'link' => 'profile', 'className' => 'active', 'gridClass' => 'col-lg-2', 'text' => $this->lang->line('settings')),
        );        
        $this->caregiver_profile_items = array(
            array('name' => $this->lang->line('settings'), 'title' => 'Afmelden', 'link' => 'profile', 'className' => 'inactive'),
            array('name' => $this->lang->line('logout'), 'title' => 'Afmelden', 'link' => '../AccountController/logout', 'className' => 'inactive')
            );
        $this->caregiver_profile_class = "inactive";
    }
    
    function set_active($menutitle) {
        if (strcasecmp($menutitle, $this->lang->line('caregiver_menu_profile')) == 0){
            $this->caregiver_profile_class = "active";
        }
        else{
            $this->caregiver_profile_class = "inactive";
        }
        foreach ($this->caregiver_menu_items as &$item) { // reference to item!!
            if (strcasecmp($menutitle, $item['name']) == 0) {
                $item['className'] = 'active';
            } else {
                $item['className'] = 'inactive';
            }
        }
    }
     
    function get_menuitems($menutitle='Algemeen') {
        if($this->session->isAdmin){
            $this->caregiver_menu_items[] = array(
                'showID' => "id = 'newuserTab'",
                'name' => $this->lang->line('caregiver_menu_register'), 
                'title' => 'Register', 
                'link' => 'register', 
                'className' => 'inactive', 
                'gridClass' => 'col-lg-2', 
                'text' => $this->lang->line('caregiver_menu_register'));
        }
        $this->set_active($menutitle);
        return $this->caregiver_menu_items;
    }
    
    function get_profile_class(){
        return $this->caregiver_profile_class;
    }
    
    function get_profileitems($menutitle = NULL){
        foreach ($this->caregiver_profile_items as &$item) { // reference to item!!
            if (strcasecmp($menutitle, $item['name']) == 0) {
                $item['className'] = 'active';
            } else {
                $item['className'] = 'inactive';
            }
        }
        return $this->caregiver_profile_items;
    }
}
