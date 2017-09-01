<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Account_model
 *
 * @author axel
 */
class Account_model extends CI_Model {


    function __construct() {
        parent::__construct();
        $this->load->database();
    }
       /*
        * @param: username of Patient or Caregiver. if caregivers and patients with the same username exist, it will return the patient
        * @return: array that represents a tuple from the db + usertype, eg the keys are the atribute names, Values are values
        */
    function getUser($username) {
        $query = $this->db->query("SELECT * FROM Patient where Name=?", $username);
        $row = $query->row_array();

        if (!isset($row)) { // not a patient
            $query = $this->db->query("SELECT * FROM Caregiver where Name=?", $username);
            $row = $query->row_array();
            if (!isset($row)) {
                return NULL;
            } // also not a caregiver
            else {
                $row["userType"] = "Caregiver"; // is a caregiver
                return $row;
            }
        } else {
            $row["userType"] = "Patient"; //is a patient
            return $row;
        }
    }

    function addUser($usertype, $language, $username, $password, $gender, $birthdate) {
        $query = $this->db->query("SELECT Name FROM Patient where Name=?", $username);
        $row = $query->row();
        if (isset($row)) {// existing patient
            return false; // no succes
        }
        $query = $this->db->query("SELECT Name FROM Caregiver where Name=?", $username);
        $row = $query->row();
        if (isset($row)) {// existing caregiver
            return false; // no succes
        }
        // make the new user
        $data = array(
            'Language' => $language,
            'Name' => $username,
            'password' => $password,
            'Gender' => $gender,
            'Birthday' => $birthdate
        );
        $this->db->insert($usertype, $data);
        return true;
    }
    
    function changeLanguage($userType, $lang, $idPatient){
        $this->db->set('Language', $lang);
        $this->db->where('id'.$userType, $idPatient);
        $this->db->update($userType);
    }
    
    function changePassword($userType,$password, $idPatient){
        $this->db->set('password', $password);
        $this->db->where('id'.$userType, $idPatient);
        $this->db->update($userType);
        
    }
    function changeRoom($userType,$room_number, $idPatient){ //only for elderly
        $this->db->set('RoomNumber', $room_number);
        $this->db->where('id'.$userType, $idPatient);
        $this->db->update($userType);               
    }
    
    function changePhone($userType,$phone_number, $idPatient){ //only for elderly
        $this->db->set('PhoneNumber', $phone_number);
        $this->db->where('id'.$userType, $idPatient);
        $this->db->update($userType);      
    }

    function changeHomeAddress($userType,$home_address, $idCaregiver){//only for caregiver
        $this->db->set('HomeAddress', $home_address);
        $this->db->where('id'.$userType, $idCaregiver);
        $this->db->update($userType); 
    }
    function changeEmail($userType,$email, $idCaregiver){//only for caregiver
        $this->db->set('Email', $email);
        $this->db->where('id'.$userType, $idCaregiver);
        $this->db->update($userType); 
    }
    function changeMobile($userType,$mobile, $idCaregiver){//only for caregiver
        $this->db->set('Mobile', $mobile);
        $this->db->where('id'.$userType, $idCaregiver);
        $this->db->update($userType); 
    }
}
