<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CaregiverController
 *
 * @author orditech
 */
class CaregiverController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        
        $this->load->library('session');
        $this->load->library('parser'); //This will allow us to use the parser in function index.
        $this->load->helper('url'); //This allows to use the base_url function for loading the css.
        $this->lang->load('caregiver', $this->session->Language);
        $this->lang->load('login', $this->session->Language);
        $this->lang->load('caregiver_menu', $this->session->Language);
        $this->lang->load('tip', $this->session->Language);
        $this->lang->load('reward', $this->session->Language);
        $this->load->model('Caregiver_Menu_model');
        $this->load->model('Caregiver_Home_model');
        $this->load->model('Account_model');
        $this->load->model('Tip_model');
        $this->load->model('Reward_model');
        $this->load->model('Account_model');
    }
    
    private function loadCommonData() {
        $data['show_navbar'] = true;
        $data['profile_func'] = base_url() . 'CaregiverController/profile';
        $data['profile'] = $this->session->Name;
        $data['caregiver_profile_items'] = $this->Caregiver_Menu_model->get_profileitems();
        $data['profile_class'] = $this->Caregiver_Menu_model->get_profile_class();  //active or inactive
        $data['navbar_content'] = 'Caregiver/caregiverNavbar.html';
        return $data;
    }

    function index() {
        if (!$this->is_logged_in()){ 
            return;
        }
        $data = $this->loadIndexData();
        $this->parser->parse('master.php', $data);
    }

    function getTitle(){
        $this->output->set_content_type("application/json")
                ->append_output($this->Caregiver_Home_model->get_chart_title());
    }
    
    function isAdmin(){
        $this->output->set_content_type("application/json")
                ->append_output(json_encode(array("isAdmin" => $this->session->isAdmin)));
    }
    
    function getArray() {
        $this->output->set_content_type("application/json")
                ->append_output($this->Caregiver_Home_model->get_topic_with_score());
    }
    
    function getPersonalScores(){
        $id = $this->input->post('id');
        $this->output->set_content_type("application/json")
                ->append_output($this->Caregiver_Home_model->topicscorejson($id));
    }
    
    function getChartData(){
        $jsondata = $this->Caregiver_Home_model->getJSONtable()->JSONCODE;
        return $jsondata;
    }
    
    function personal() {
        if (!$this->is_logged_in()) {
            return;
        }
        
        $data = $this->loadPersonalData();
        $this->parser->parse('master.php', $data);
    }


    function tips() {
        if (!$this->is_logged_in()) {
            return;
        }
        $data = $this->loadTipsData();
        $this->parser->parse('master.php', $data);
    }

    function rewards() {
        if (!$this->is_logged_in()) {
            return;
        }
        $data = $this->loadRewardsData();

        $data["boughtRewards"] = $this->Reward_model->getRewardsByPatient();
        foreach ($data["boughtRewards"] as $value) {
            if ($value->Recieved) {
                $value->Recieved = "checked";
            } else
                $value->Recieved = "";
        }
        $this->parser->parse('master.php', $data);
    }

    function rewardPost(){
        if (!$this->is_logged_in()) {
            return;
        }
        
        if (!empty($_POST["new_reward"]) && !empty($_POST["price"])){
            $reward = filter_input(INPUT_POST, 'new_reward');
            $price = filter_input(INPUT_POST, 'price');
            $language = $_POST['language'];
            
            $this->Reward_model ->add_reward($reward,$price, $language);
        }
        
        else{
            echo "error";              
        }
        //redirect(base_url() . 'CaregiverController/rewards');
        $data['allrewards'] = $this->Reward_model->get_rewards();
        $this->parser->parse('Caregiver/rewardsList.html', $data);
    }   
    
    function editReward(){
        $reward = $_POST['Reward'];
        if($_POST['available'] == "true") {
            $available = "checked";  
        }
        else {
            $available = " ";
        }
        $this->Reward_model ->edit_reward($reward, $available);
        //redirect(base_url() . 'CaregiverController/rewards');
    }   
        function editRecievedReward(){
        
        if($_POST['changed'] == "true") {
            $recieved = 1;  
        }
        else {
            $recieved = 0;
        }
        $id = $_POST['id'];
        echo var_dump($id);
        echo var_dump($recieved);
        $this->Reward_model->editRewardsRecieved($id,$recieved);
        //redirect(base_url() . 'CaregiverController/rewards');
    } 
    
    function get_tips(){
        $topic = $this->input->post('topic');
        $this->output->set_content_type("application/json")->append_output(
                $this->Tip_model->get_tips_as_json($topic));
    }
    
    function add_tip(){
        $topic = $this->input->post('topic');
        $tip = $this->input->post('tip');
        $language = $this->input->post('language');
        $this->Tip_model->add_tip($topic, $tip, $language);
    }
    
    function delete_tip(){
        $id = $this->input->post('id');        
        $success = $this->Tip_model->remove_tip($id);
        $this->output->set_content_type("application/json")->append_output($success);
    }
    
    function update_tip(){
        $tipId = $this->input->post('id');
        $topic = $this->input->post('topic');
        $tip = $this->input->post('tip');
        $language = $this->input->post('language');
        
        $this->Tip_model->update_tip($tipId,$topic, $tip, $language);
    }
    
    function update_note(){
        $note = $this->input->post('new_note');
        $id = $this->input->post('id');
        
        $this->Caregiver_Home_model->updatenote($note, $id);
    }
    
    function send_message(){
        $message = $this->input->post('message');
        
        $this->output->set_content_type("application/json")->append_output($this->Caregiver_Home_model->add_message($message));
    }

    function profile() {
        if (!$this->is_logged_in()) {
            return;
        }
        
        $data = $this->loadProfileData();
        $this->parser->parse('master.php', $data);   
    }

    function logout() {
        session_destroy();
        redirect(base_url() . 'AccountController/login');
    }

    function change_language() {
        $newlang = $this->input->post('language');
        $data["changed_lang"] = false;
        if ($newlang !== $this->session->Language) {
            $this->session->set_userdata('Language', $newlang);
            $this->Account_model->changeLanguage($this->session->userType,$newlang,$this->session->idCaregiver);
            $data['changed_lang']= true;
            $data['err_msg'] = $this->lang->line('language') . $this->lang->line('saved_changes');
        }
        $this->output->set_content_type("application/json")->append_output(json_encode($data));
    }
    
    function change_profile() {
        $this->lang->load('login', $this->session->Language);
        $home_address = $this->input->post('home_address'); 
        $email = $this->input->post('email');
        $mobile = $this->input->post('mobile');
        $mydata['changes_made'] = false;
        if ($home_address != null) {
            $this->Account_model->changeHomeAddress($this->session->userType,$home_address, $this->session->idCaregiver);
            $mydata['changes_made'] = true;
        }
        if ($email != null) {
            $this->Account_model->changeEmail($this->session->userType,$email, $this->session->idCaregiver);
            $mydata['changes_made'] = true;
        }
        if ($mobile != null) {
            $this->Account_model->changeMobile($this->session->userType,$mobile, $this->session->idCaregiver);
            $mydata['changes_made'] = true;
        }
        if($mydata['changes_made']){
            $mydata['err_msg'] = $this->lang->line('changes_were_saved');
        }
        $this->output->set_content_type("application/json")->append_output(json_encode($mydata));
    }
    
    function register() {
        if(!$this->session->isAdmin){
            session_destroy();
            redirect(base_url() . 'AccountController/login');
        }
        $data = $this->loadRegisterData();
        $this->parser->parse('master.php', $data);
    }
    
    function registerPost() {
        $this->lang->load('login', $this->session->Language);
        $return_data['err_msg'] = $this->lang->line('register_form_incomplete');
        $return_data['success'] = false; 
        $usertype = $this->input->post("usertype");
        $language = $this->input->post("language");
        $username = $this->input->post("username");
        $password1 = $this->input->post("password1");
        $password2 = $this->input->post("password2");
        $gender = $this->input->post("gender");
        $birthdate = $this->input->post("birthdate");
        if($username!=NULL && $usertype!=NULL && $password1!=NULL && $password2!=NULL 
                && $gender!=NULL && $language!=NULL && $birthdate!=NULL){
            if ($password1 === $password2) {
                $password = password_hash($password1, PASSWORD_DEFAULT);
                if ($this->Account_model->addUser($usertype, $language, $username, $password, $gender, $birthdate)) {
                    $return_data['err_msg'] = $this->lang->line('account_created');
                    $return_data['success'] = true;
                } 
                else {
                    $return_data['err_msg'] = $this->lang->line('user_exists');
                }
            } 
            else {
                $return_data['err_msg'] = $this->lang->line('different_passwords');
            }
        }
        $this->output->set_content_type("application/json")->append_output(json_encode($return_data));
    }

    function change_password() {
        if (!$this->is_logged_in()) {
            return;
        }
        $this->lang->load('login', $this->session->Language);
        $data['success'] = false;
        $data['err_msg'] = " ";
        $verif = $this->Account_model->getUser($this->session->Name);
        $old = $this->input->post('old_password');
        $new = $this->input->post('new_password');
        $conf = $this->input->post('conf_password');
        if ($old || $new || $conf){
            $data['err_msg'] = $this->lang->line('errorbox_password').$this->lang->line('register_form_incomplete');
        }
        if($old && $new && $conf){
            $data['err_msg'] = $this->lang->line('errorbox_password').$this->lang->line('different_passwords');
            if ($conf === $new) {
                $data['err_msg'] = $this->lang->line('errorbox_password').$this->lang->line('incorrect_password');
                if (password_verify($old, $verif["password"])) {
                    $password = password_hash($new, PASSWORD_DEFAULT);
                    $this->Account_model->changePassword($this->session->userType,$password, $this->session->idCaregiver);
                    $data['err_msg'] = $this->lang->line('errorbox_password').$this->lang->line('saved_changes');
                    $data['success'] = true;
                }
            }
        }
        $this->output->set_content_type("application/json")->append_output(json_encode($data));
    }
    
    private function is_logged_in() { // returns true if valid user is logged in, else returns false and redirects to login page
        if ($this->session->userType == "Caregiver")
            return true;
        else {
            echo "You are not allowed to access this page";
            $this->output->set_header('refresh:3; url=' . base_url("AccountController/login"));
            return false;
        }
    }
    
    function loadRegisterData(){
        $data = $this->loadCommonData();
        $this->lang->load('login', $this->session->Language);
        $data['confirm'] = $this->lang->line('confirm');
        $data['confirm_ph'] = $this->lang->line('caregiver_confirm_placeholder');
        $data['username'] = $this->lang->line('username');
        $data['password'] = $this->lang->line('password');
        $data['new_user'] = lang('new_user');
        $data['username_ph'] = $this->lang->line('caregiver_username_placeholder');
        $data['password_ph'] = $this->lang->line('caregiver_password_placeholder');
        $data['user_type'] = $this->lang->line('user_type');
        $data['patient'] = $this->lang->line('patient');
        $data['caregiver'] = $this->lang->line('caregiver');
        $data['language'] = $this->lang->line('language');
        $data['page_title'] = 'Add Profile';
        $data['caregiver_menu_items'] = $this->Caregiver_Menu_model->get_menuitems($this->lang->line('caregiver_menu_register'));
        $data['page_content'] = 'Account/register.html';
        $data['birthdate'] = $this->lang->line("birthdate");
        $data['gender'] = $this->lang->line("gender");
        $data['male'] = $this->lang->line("male");
        $data['female'] = $this->lang->line("female");
        
        $scripts[] = array('source' => "../../assets/js/jquery.min.js");
        $scripts[] = array('source' => "../../assets/js/login.js");
        $scripts[] = array('source' => "../../assets/js/toastMessage.js");
        $links[] = array('source' => "../../assets/css/caregiver_register_settings.css");
        $links[] = array('source' => "../../assets/css/snackbar.css");
        $links[] = array('source' => "../../assets/css/caregiver_navbar.css");
        
        $data['scripts'] = $scripts;
        $data['css_links'] = $links;
        return $data;
    }

    private function loadIndexData() {
        $data = $this->loadCommonData();
        $data['page_title'] = 'Caregiver Home';
        $data['header1'] = 'Welcome to Caregiver Home';
        $data['caregiver_menu_items'] = $this->Caregiver_Menu_model->get_menuitems($this->lang->line('caregiver_menu_general'));
        $data['topics'] = $this->Caregiver_Home_model->get_topics();
        $data['urgent'] = $this->Caregiver_Home_model->calculate_avg();
        $data['chart_title'] = $this->lang->line('chart_title');
        $data['urgent_patient_title'] = $this->lang->line('caregiver_urgent_patients');
        $data['messages_title'] = $this->lang->line('messages_title');
        $data['send_button_text'] = $this->lang->line('send_button');
        $data['input_text_placeholder'] = $this->lang->line('input_text_placeholder');
        $data['content'] = "";
        $data['page_content'] = 'Caregiver/index.html';
        //$data['messages'] = $this->Caregiver_Home_model->add_message($this->input->get('messagesend'));
        $data ['show'] = $this->Caregiver_Home_model->show_messages();
        
        $scripts[] = array('source' => "../../assets/js/jquery.min.js");
        $scripts[] = array('source' => "../../assets/js/caregiver_messages.js");
        $scripts[] = array('source' => "https://www.gstatic.com/charts/loader.js");
        $scripts[] = array('source' => "../../assets/js/chart.js");
        
        $links[] = array('source' => "../../assets/css/caregiver_general.css");
        $links[] = array('source' => "../../assets/css/caregiver_navbar.css");
        
        $data['scripts'] = $scripts;
        $data['css_links'] = $links;
        
        return $data;
    }
    
    private function loadPersonalData() {
        $data = $this->loadCommonData();
        $data['page_title'] = 'Personal Patient Information';
        $data['caregiver_menu_items'] = $this->Caregiver_Menu_model->get_menuitems($this->lang->line('caregiver_menu_personal'));
        $data['content'] = lang(''); //to check whether internationalization set up works
        $data['patients'] = $this->Caregiver_Home_model->get_patients();
        $data['table'] = $this->Caregiver_Home_model->getJSONtable();
        $data['table_titles'] = $this->Caregiver_Home_model->get_table_header($this->lang->line('caregiver_datatable_titles'));
        $data['table_titles_mobile'] = $this->Caregiver_Home_model->get_table_header_mobile($this->lang->line('caregiver_datatable_titles'));
        //$data['table_titles'] = $this->lang->line('caregiver_datatable_titles'); //Gets the titles for the datatable in the correct language.
        $data['currentuser'] = $this->Caregiver_Home_model->current_user($this->input->get('username'));
        $data['page_content'] = 'Caregiver/personal.html';
        $data['edit'] = $this->lang->line('edit');
        $data['personal_patients'] = $this->lang->line('personal_patients_capital');
        
        $scripts[] = array('source' => "https://www.gstatic.com/charts/loader.js");
        $scripts[] = array('source' => "../../assets/js/jquery.min.js");
        $scripts[] = array('source' => "../../assets/js/carefiver-personal-notes.js");
        $scripts[] = array('source' => "https://cdn.datatables.net/v/bs/dt-1.10.12/datatables.min.js");
        $scripts[] = array('source' => "../../assets/js/personal_datatable.js");
        //$scripts[] = array('source' => "../../assets/js/chart.js");
        
        $links[] = array('source' => "https://cdn.datatables.net/v/bs/dt-1.10.12/datatables.min.css");
        $links[] = array('source' => "https://code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css");
        $links[] = array('source' => "../../assets/css/caregiver_patients.css");
        $links[] = array('source' => "../../assets/css/caregiver_navbar.css");
        
        $data['scripts'] = $scripts;
        $data['css_links'] = $links;
        return $data;
    }

    private function loadProfileData() {
        $data = $this->loadCommonData();
        $data['new_placeholder'] = $this->lang->line('caregiver_new_placeholder');
        $data['old_placeholder'] = $this->lang->line('caregiver_old_placeholder');
        $data['conf_placeholder'] = $this->lang->line('caregiver_conf_placeholder');
        $data['change_password'] = $this->lang->line('caregiver_change_password');
        $data['name'] = $this->lang->line('name');
        $data['date_of_birth'] = $this->lang->line('date_of_birth');
        $data['gender'] = $this->lang->line('gender');
        $data['home_address'] = $this->lang->line('home_address');
        $data['email'] = $this->lang->line('email');
        $data['mobile'] = $this->lang->line('mobile');
        $data['language'] = $this->lang->line('language');
        $data['save'] = $this->lang->line('save');
        //load data from database directly 
        $result = $this->db->query("SELECT * FROM Caregiver where Name=?", $this->session->Name)->row();
        $data['Birthday'] = $result->Birthday;
        $data['Gender'] = $result->Gender;
        $data['HomeAddress'] = $result->HomeAddress;
        $data['Email'] = $result->Email;
        $data['Mobile'] = $result->Mobile;
        
        $data['page_title'] = 'Edit Profile';
        $data['caregiver_menu_items'] = $this->Caregiver_Menu_model->get_menuitems($this->lang->line('caregiver_menu_profile'));
        $data['caregiver_profile_items'] = $this->Caregiver_Menu_model->get_profileitems($this->lang->line('settings'));
        $data['profile_class'] = $this->Caregiver_Menu_model->get_profile_class();
        $data['page_content'] = 'Account/caregiver_profile.html';
        $data['Person_Name'] = $this->session->Name;
        $data['checked_dutch'] = ($this->session->Language == "dutch") ? "selected" : " ";
        $data['checked_english'] = ($this->session->Language == "english") ? "selected" : " ";
        
        $scripts[] = array('source' => "../../assets/js/jquery.min.js");
        $scripts[] = array('source' => "../../assets/js/toastMessage.js");
        $scripts[] = array('source' => "../../assets/js/profilescript.js");
        
        $links[] = array('source' => "../../assets/css/caregiver_register_settings.css");
        $links[] = array('source' => "../../assets/css/caregiver_navbar.css");
        $links[] = array('source' => "../../assets/css/snackbar.css");
        
        $data['scripts'] = $scripts;
        $data['css_links'] = $links;
        return $data;
    }

    private function loadTipsData() {
        $data = $this->loadCommonData();
        $data['page_title'] = 'Tips';
        $data['caregiver_menu_items'] = $this->Caregiver_Menu_model->get_menuitems($this->lang->line('caregiver_menu_tips'));
        $data['content'] = "Tips to be added.";
        $data['options'] = $this->Caregiver_Home_model->get_topics();
        $data['choose_option'] = $this->lang->line('tip_choose_topic');
        $data['add_new_tip'] = $this->lang->line('tip_add_new_tip');
        $data['page_content'] = 'Caregiver/tips.html';
        $data['write_new_tip'] = $this->lang->line('tip_write_new');
        $data['tip_undo'] = $this->lang->line('undo_tip');
        
        $scripts[] = array('source' => "../../assets/js/jquery.min.js");
        $scripts[] = array('source' => "../../assets/js/toastMessage.js");
        $scripts[] = array('source' => "../../assets/js/caregiver_tips.js");
        
        $links[] = array('source' => "../../assets/css/caregiver_tips_rewards.css");
        $links[] = array('source' => "../../assets/css/snackbar.css");
        $links[] = array('source' => "../../assets/css/caregiver_navbar.css");
        
        $data['scripts'] = $scripts;
        $data['css_links'] = $links;
        return $data;
    }

    private function loadRewardsData() {
        $data = $this->loadCommonData();
        $data['profile_func'] = base_url() . 'CaregiverController/rewards';

        $data['page_title'] = 'Rewards';

        $data['caregiver_menu_items'] = $this->Caregiver_Menu_model->get_menuitems($this->lang->line('caregiver_menu_reward'));
        $data['write_new_reward'] = $this->lang->line('write_new_reward');
        $data['caregiver_menu_reward'] = lang('caregiver_menu_reward');
        $data['add_new_reward'] = $this->lang->line('add_new_reward');
        $data['price'] = $this->lang->line('price');
        $data['allrewards'] = $this->Reward_model->get_rewards();
        $data['page_content'] = 'Caregiver/reward.html';
        $data['rewards_list'] = $this->lang->line('rewards_list');
        $data['bought_rewards'] = $this->lang->line("bought_rewards");
        $data['date'] = $this->lang->line("date");
        $data['value'] = $this->lang->line("value");
        $data['received'] = $this->lang->line("received");
        $data['name'] = $this->lang->line("name");
        $data['reward'] = $this->lang->line("reward");
        $data['available'] = $this->lang->line("available");
        $data['reward_added'] = $this->lang->line('reward_added');
        
        $scripts[] = array('source' => "../../assets/js/jquery.min.js");
        $scripts[] = array('source' => "../../assets/js/toastMessage.js");
        $scripts[] = array('source' => "../../assets/js/Caregiver_reward.js");
        
        $links[] = array('source' => "../../assets/css/caregiver_tips_rewards.css");
        $links[] = array('source' => "../../assets/css/caregiver_navbar.css");
        $links[] = array('source' => "../../assets/css/snackbar.css");
        
        $data['scripts'] = $scripts;
        $data['css_links'] = $links;
        return $data;
    }
    
    function getTipsLocalization(){
        $data['edit'] = $this->lang->line('edit');
        $data['save'] = $this->lang->line('save');
        $data['choose_a_topic'] = $this->lang->line('tip_choose_topic');
        $data['confirm'] = $this->lang->line('confirm_action');
        $data['write_a_tip'] = $this->lang->line('write_a_tip');
        $data['notify_deleted'] = $this->lang->line('notify_deleted');
        $jsondata = json_encode($data);
        $this->output->set_content_type("application/json")
                ->append_output($jsondata);
    }

}
