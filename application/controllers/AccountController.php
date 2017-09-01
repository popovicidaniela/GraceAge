<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class AccountController extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->library('parser');
        $this->load->helper('url');        
        $this->load->library('session');
        $this->load->model('Account_model');
        $this->lang->load('login', $this->session->language);
    }

    function return_to_home(){
        redirect(base_url(). 'CaregiverController/index');
    }
    
    public function change_language() {
        $language =  $this->session->language;
        if ($language == "english") {
            $this->session->set_userdata('language', "dutch");
        } 
        else {
            $this->session->set_userdata('language', "english");
        }
        $this->lang->load('login', $this->session->language);
        redirect(base_url() . 'AccountController/login');
    }

    private function common_data() {
        $data['show_navbar'] = false;
        $data['username'] = lang('username');
        $data['password'] = lang('password');
        $data['confirm'] = lang('confirm');
        $data['navbar_content'] = 'Elderly/elderlyNavbar.html';
        return $data;
    }
    
    private function login_data() {
        $data['page_title'] = lang('LOG_IN') . " Grace Age";
        $data['BAGDE'] = lang('BAGDE');
        $data['LOG_IN'] = lang('LOG_IN');
        $data['show_your_badge'] = lang('show_your_badge');
        $data['information_badge'] = lang('information_badge');
        $data['no_badge'] = lang('no_badge');
        $data['no_camera'] = lang('no_camera');
        $data['credentials'] = lang('credentials');
        $scripts[] = array('source' => "../../assets/js/login.js");
        $scripts[] = array('source' => "../../assets/js/jsqrcode-combined.min.js");
        $scripts[] = array('source' => "../../assets/js/html5-qrcode.min.js");
        $scripts[] = array('source' => "../../assets/js/getqrdata.js");
        $links[] = array('source' => "../../assets/css/login.css");
        
        $data['scripts'] = $scripts;
        $data['css_links'] = $links;
        return $data;
    }

    //login page
    public function login() {
        if(!$this->session->has_userdata('language')){
            $this->session->set_userdata('language', "dutch");  //default dutch
        }
        $this->lang->load('login', $this->session->language);
        $data['other_language'] = $this->lang->line('other_language');
        $data['loggedin'] = lang('not_logged_in');
        $data = array_merge($data, $this->common_data(), $this->login_data());
        $data['page_content'] = 'Account/login.html';
        $this->parser->parse('master.php', $data);  //page content in master page
    }

    // check login
    function login_valid(){ 
        $this->lang->load('login', $this->session->language);
        if (isset($_POST["username"]) && !empty($_POST["username"]) && isset($_POST["password"]) && !empty($_POST["password"])) { // check if input is set
            $username = filter_input(INPUT_POST, 'username');
            $password = filter_input(INPUT_POST, 'password');
            $result = $this->Account_model->getUser($username); //data from database
            $data2['valid_user'] = ($result != null);
            $data2['correct_password'] = password_verify($password, $result["password"]);   //true/false
            $data2['errormessage'] = $this->lang->line('wrong_credentials');
            if ($result != NULL) {
                if (password_verify($password, $result["password"])) {
                    $data2['usertype'] = $result['userType'];
                    $result["password"] = NULL;
                    $this->session->set_userdata($result);
                }
            
            }
            $this->output->set_content_type("application/json")->append_output(json_encode($data2));
        }
        else{
            $this->output->set_content_type("application/json")->append_output(json_encode(array(
                'valid_user' => false,
                'correct_password' => false,
                'errormessage' => $this->lang->line('empty_credentials')
            )));
        }
    }
    
    function loginPost() {   //redirect to the corresponding index page according to the usertype
        if ($this->session->userType == "Patient") {
            redirect(base_url() . 'ElderlyController/index');
        } else if($this->session->userType == "Caregiver") { 
            redirect(base_url() . 'CaregiverController/index');
        }
    }

    public function logOut() { //destroy current session and goto login page
        session_destroy();
        redirect(base_url() . 'AccountController/login');
    }
    
    public function getQrError(){
       
        echo $this->lang->line('QrError');
    }

}
