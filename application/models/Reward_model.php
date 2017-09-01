<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class Reward_model extends CI_Model{
               
    function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
    }  
    function get_rewards() {
        $query = $this->db->query("select Reward, Price, Language, Available from Rewards order by Available=' ', Id desc");
        return $query->result();
    }
    
    function rewardExists($reward){
        $query = $this->db->query('SELECT Reward FROM Rewards where Reward=?', $reward);
        if ($query->num_rows()==0) {
            return false;
        }
        else {
            return true;
        }
    }
    
    function add_reward($reward,$price, $language){ //add a new reward with language
        if ($this->rewardExists($reward)) {
            return false;
        }
        else {
            $data = array(
            'Reward' => $reward,
            'Price' => $price,
            'Language' => $language
        );
        $this->db->insert('a16_webapps_2.Rewards' , $data);
        return true;
        }
    }
    
    function edit_reward($reward,$available){  //change the state of a reward
        $data = array(
            'Available' => $available
        );
        $this->db->where('Reward' , $reward);
        $this->db->update('Rewards' , $data);
    }
    
    function getRewardsByPatient(){
        $query = $this->db->query("Select R.Reward, P.Name, PR.Date , PR.Recieved, PR.PatientId, PR.RewardId, PR.Id from Rewards as R, Patient as P, PatientReward as PR where P.idPatient = PR.PatientId and R.Id = PR.RewardId and PR.Recieved = 0 order by Date desc");
        $result =$query->result();
                  foreach ($result as $row) {
            list($date, $time) = explode(" ", $row->Date); // splits database version of date
            $originalDate = $date;
            $newDate = date("d-m-Y", strtotime($originalDate));
            $row->Date = $newDate;
        }

        return $result;
    }
    function editRewardsRecieved($id ,$checked){
        $this->db->set('Recieved', $checked);
        $this->db->where('Id', $id);
       
        $this->db->update('PatientReward');
    }
}