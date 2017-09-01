<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class Tip_model extends CI_Model{
               
    function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->lang->load('tip', $this->session->Language);        
        
        $this->navigationbuttons = array(
            array('name' => $this->lang->line('return_to_question'),'class' => 'btn  btn-arrow-left btn-block',  'func' => 'back()'),
            array('name' => $this->lang->line('go_to_score'),'class' => 'btn btn-arrow-right btn-block', 'func' => 'forward()'),
        );
    }    
    
    function get_tip($topic) {         // get a random tip (not null) from a given topic   
        $language = $this->session->Language;
        $array = array('topic' => $topic, $language." != " => NULL);
        $query = $this->db->select($language)->where($array)->get('tips');
        if($query->num_rows() > 0){
            $random = rand(1, $query->num_rows());
            if($this->session->Language === 'english'){
                $row = $query->row($random);
                return $row->english;
            }
            if($this->session->Language === 'dutch'){
                return $query->row($random)->dutch;
            }
        }        
    }
    
    function get_tips_as_json($topic) {    //get all tips from a topic as json data   
        $query = $this->db->select('english, dutch')->select('tips.idtips')->where('topic', $topic)->order_by('idtips', 'desc')->get('tips');
        return json_encode($query->result());
    }
    
    function add_tip($topic, $tip, $language){ //add a new tip to a topic
        $data = array(
            'topic' => $topic,
            $language => $tip
        );
        $this->db->insert('a16_webapps_2.tips' , $data);
    }
    
    function remove_tip($tipId){ //remove a tip with given id
        $this->db->where('idtips', $tipId);
        $success = $this->db->delete('tips');
        return $success;
    }
    
    function update_tip($tipId,$topic, $tip, $language){ //update the content of a tip
        $this->db->set($language, $tip);
        $this->db->set('topic', $topic);
        $this->db->where('idtips', $tipId);
        $this->db->update('tips');
    }
    
    function get_navigationbuttons(){
        return $this->navigationbuttons;
    }
}