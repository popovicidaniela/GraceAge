<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Question_model
 *
 * @author orditech
 */

class Question_model extends CI_Model{
    
    private $answers;
    private $navigationbuttons;
            
    function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->lang->load('question', $this->session->Language);
        $this->answers = array(
            array('id' => '1', 'onclick' => 'mark_answer(this.id)', 'name' => $this->lang->line('question_never'), 'className' => 'answer_button btn-block'),
            array('id' => '2', 'onclick' => 'mark_answer(this.id)', 'name' => $this->lang->line('question_rarely'), 'className' => 'answer_button btn-block'),
            array('id' => '3', 'onclick' => 'mark_answer(this.id)', 'name' => $this->lang->line('question_sometimes'), 'className' => 'answer_button btn-block'),
            array('id' => '4', 'onclick' => 'mark_answer(this.id)', 'name' => $this->lang->line('question_usually'), 'className' => 'answer_button btn-block'),
            array('id' => '5', 'onclick' => 'mark_answer(this.id)', 'name' => $this->lang->line('question_always'), 'className' => 'answer_button btn-block'),
        );
        
        $this->navigationbuttons = array(
            array('id'=>'prev' ,'name' => $this->lang->line('question_previous'),'class' => 'btn  btn-arrow-left btn-block', 'title' => 'previous_navbutton', 'func' => 'previous()'),
            array('id'=>'next', 'name' => $this->lang->line('question_next'),'class' => 'btn btn-arrow-right btn-block', 'title' => 'next_navbutton', 'func' => 'next()'),
        );
        
        date_default_timezone_set("Europe/Brussels");
    }
    
    
    function get_answerbuttons(){
        return $this->answers;
    }
    
    function get_navigationbuttons(){
        return $this->navigationbuttons;
    }
       
    function get_question($i, $language) {
        $array = array('QuestionNumber' => $i, 'Language' => $language);
        $query = $this->db->select('Topic, Question')->where($array)->get('Question');
        return $query->result();
    }

    function get_previous_question_as_json(){
        $this->session->unset_userdata('selected_answer');
        if($this->session->question_id > 1){
            $this->session->set_userdata('question_id', $this->session->question_id - 1);
            $this->undo_answer(
                        $this->session->n_questionaire, 
                        $this->session->idPatient,
                        $this->session->question_id);
        }
        $this->db->reconnect();
        do{
            $array = array('QuestionNumber' => $this->session->question_id, 'Language' => $this->session->Language);
            $query = $this->db->select('Topic, Question, QuestionNumber')
                    ->where($array)
                    ->get('a16_webapps_2.Question');
        } while($query->num_rows() < 1);

        $this->session->unset_userdata('selected_answer');
        
        return json_encode($query->result());
    }
    
    function get_next_question_as_json(){
        if($this->session->userdata('selected_answer')){
            $this->submit_answer(
                    $this->session->selected_answer, 
                    $this->session->question_id, 
                    $this->session->n_questionaire,
                    $this->session->idPatient);
        }
        $this->session->set_userdata('question_id', $this->session->question_id + 1);
        if ($this->session->question_id > 52){
            $this->session->set_userdata('question_id', 1);
            $this->session->set_userdata('n_questionaire', $this->session->n_questionaire +1);
        }
           
        $this->db->reconnect();
        do{
            $array = array('QuestionNumber' => $this->session->question_id, 'Language' => $this->session->Language);
            $query = $this->db->select('Topic, Question, QuestionNumber')
                    ->where($array)
                    ->get('a16_webapps_2.Question');
        } while($query->num_rows() < 1);       
        $this->session->unset_userdata('selected_answer');
        return json_encode($query->result());
    }
    
    function delete_old_data($user_id, $current_q_number){
        $this->db->query("DELETE FROM a16_webapps_2.Patient_Answered_Question WHERE "
                . "Patient_idPatient = " .$user_id ." AND "
                . "Questionaire_Number < " .$current_q_number - 2 ." ;");
    }
    
    function get_progress()
    {
        return $this->session->question_id;
    }
    
    function get_initial_state(){
        $query = $this->db->query("SELECT * "
                . "FROM a16_webapps_2.Patient_Answered_Question "
                . "WHERE Patient_idPatient = " . $this->session->idPatient . " "
                . "ORDER BY DateTime DESC "
                . "LIMIT 1;");
        $result = $query->row();
        if(isset($result)){
            $this->session->set_userdata('n_questionaire', $result->Questionaire_Number);
            $this->session->set_userdata('question_id', $result->Question_Number +1);
        }
        else{
            $this->session->set_userdata('n_questionaire', 1);
            $this->session->set_userdata('question_id', 1);
        }
        if ($this->session->question_id > 52){ // might want to add a count here later on
            $this->session->set_userdata('question_id', 1);
            $this->session->set_userdata('n_questionaire', $this->session->n_questionaire +1);
        }
        return;
    }
    
    function undo_answer($n_questionaire, $p_id, $q_id){
        $this->db->delete('a16_webapps_2.Patient_Answered_Question', array(
            'Patient_idPatient' => $p_id,
            'Question_Number' => $q_id,
            'Questionaire_Number' => $n_questionaire,
        ));
        if($this->db->affected_rows() > 0){
            $this->updatePatientScore($p_id, -1); // lose a point for not having aswerd a question
        }
    }
    function submit_answer($answer, $q_id, $n_questionaire, $p_id){
        $this->db->reconnect();
        $data = array(
            'Patient_idPatient' => $p_id,
            'Question_Number' => $q_id,
            'Questionaire_Number' => $n_questionaire,
            'Answer' => $answer,
            'DateTime' => date('Y-m-d H:i:s')
        );
        $this->db->insert('a16_webapps_2.Patient_Answered_Question', $data);
        
        $this->updatePatientScore($p_id, 1); // gain a point for answering a question
        return;
    }
    
    
    
    function updatePatientScore($pid, $increment, $add = true) { // pi = user id, increment = number with witch to increment current score, can be negative
        if ($add) {
            $score = $this->getPatientScore($pid) + $increment; //default
        } else {$score = $increment;}

        $this->db->set('score', $score);
        $this->db->where('idPatient', $pid);
        $this->db->update('Patient');
    }

    function getPatientScore($pid) {
        $this->db->reconnect();
        $query = $this->db->select('score')->where('idPatient', $pid)->get('Patient');
        $row = $query->row();
        $score = 0;
        if (isset($row)) {
            $score = $row->score;
            if ($score == NULL){
                $score = 0;
            }
        }
        return $score;
    }
    
    
    function getRewards($language) {
        $query = $this->db->query("select Reward, Price from Rewards where Language=? and Available='checked' Order BY Price Asc", $language);
        $temp = $query->result();
        $rewards = array();
        $newreward = array();
        foreach ($temp as $reward){
            $newreward['Reward_url'] = rawurlencode($reward->Reward);
            $newreward['Reward'] = $reward->Reward;
            $newreward['Price'] = $reward->Price;
            $rewards[]=$newreward;
        }
        return $rewards;
    }
    
    function getRewardsNodes($language) {
        $query = $this->db->query("select distinct Price from Rewards where Language=? and Available='checked' Order BY Price Asc", $language);
        $rewards = $query->result();
        return $rewards;
    }
    
    function getRewardsBought($patientId) {
        $query = $this->db->query("select Date, Reward, Price from Rewards JOIN PatientReward ON Rewards.Id = PatientReward.RewardId where PatientId=? order by Date desc limit 2", $patientId);
        $rewardsBought = $query->result();
        return $rewardsBought;
    }
    
    function buyReward($reward, $idPatient) {
        $getRewardIdPrice = $this->db->query("SELECT Id, Price FROM Rewards where Reward = ?", $reward);
        $rewardIdPrice = $getRewardIdPrice->row();
        
        if ($this->getPatientScore($idPatient) >= $rewardIdPrice->Price) {

            $data = array(
                'PatientId' => $idPatient,
                'RewardId' => $rewardIdPrice->Id
            );

            $this->db->insert('PatientReward', $data);

            $score = $this->getPatientScore($idPatient) - $rewardIdPrice->Price;

            $this->db->set('score', $score);
            $this->db->where('idPatient', $idPatient);
            $this->db->update('Patient');
            
            return true;
        }
        return false;
    }
}