<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Caregiver_Home_model
 *
 * @author orditech
 */
class Caregiver_Home_model extends CI_Model {

    private $all_answers;

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('date');
        $this->lang->load('topics', $this->session->Language);
        $this->lang->load('caregiver', $this->session->Language);
    }
    
    function get_table_header($headers_array) {
        $caregiver_table_headers = array(
            array('text' => $headers_array[0]["Name"], 'gridClass' => 'col-sm-3'),
            array('text' => $headers_array[1]["Name"], 'gridClass' => 'col-sm-2'),
            array('text' => $headers_array[2]["Name"], 'gridClass' => 'col-sm-1'),
            array('text' => $headers_array[3]["Name"], 'gridClass' => 'col-sm-6'),
        );
        return $caregiver_table_headers;
    }
    
    function get_table_header_mobile($headers_array) {
        $caregiver_table_headers = array(
            array('text' => $headers_array[0]["Name"], 'gridClass' => 'col-sm-5'),
            array('text' => $headers_array[1]["Name"], 'gridClass' => 'col-sm-4'),
            array('text' => $headers_array[2]["Name"], 'gridClass' => 'col-sm-3'),
        );
        return $caregiver_table_headers;
    }

    function get_topics() {
        $query = $this->db->distinct()->select('Topic')->get('Question');
        return $query->result_array();
    }

    function get_topics_as_json() {
        $query = $this->db->distinct()->select('Topic')->get('Question');
        return json_encode($query->result_array());
    }

    function get_name($id) {
        $query = $this->db->query("SELECT Name FROM a16_webapps_2.Caregiver WHERE idCaregiver = " . $id);
        return $query->row()->Name;
    }

    function get_patients() {
        $query = $this->db->select('Name')->get('Patient');
        return $query->result();
    }

    /*
     * To reduce the amount of queries on the general page, we query once to get 
     * the info we need and store it in an array.
     * This query gets a joint table with all the answer scores of questions 
     * answered in a certain period of time, with their corresponding topic.
     */
    function get_answer_array() {
        $query = $this->db->select('Question.Topic, Patient_Answered_Question.Answer')
                ->from('Question')
                ->join('Patient_Answered_Question', 'Question.QuestionNumber=Patient_Answered_Question.Question_Number')
                ->where('DateTime >=', 'now() - INTERVAL 1 MONTH', FALSE)  //Get Answers from past month. FALSE has to be added for MYSQL functions.
                ->get();
        $this->all_answers = $query->result_array(); //Store the data in an array;
    }
    
    /*
     * Calculate the scores per topic and return them in percentage.
     */
    function calculate_score($topic) {
        $sum_of_answers = 0;
        $iterations = 0;
        
        for ($i = 0; $i < count($this->all_answers); $i++/*, $iterations++*/) {
            if ($this->all_answers[$i]['Topic'] == $topic) {
                $sum_of_answers += $this->all_answers[$i]['Answer']-1; //Do the '-1' to fit in an interval of [0; 4] instead of [1; 5].
                $iterations++;
            }
        }
        //Check if a topic hasn't been answered.
        if($iterations > 0){
            return ($sum_of_answers / ($iterations * 4)) * 100; //Adapt to percentage.
        }else{
            return 0;
        }
    }

    /*
     * Returns a json encoded string containing all the topics and their
     * corresponding score, calculated for a certain time interval (e.g. past
     * month).
     */
    function get_topic_with_score() {
        $this->get_answer_array();
        $topics = $this->get_topics();
        $topic_array = $this->lang->line('topic_array'); //Get the topics in the language of the caregiver currently trying to access the scores.
        $scores = [];
        for ($j = 0; $j < count($topic_array); $j++) {
            $scores[$j + 1]['Topic'] = $topic_array[$j];
            $scores[$j + 1]['Score'] = $this->calculate_score($topics[$j]['Topic']);
        }
        $scores[$j + 1]['Topic'] = "";
        $scores[$j + 1]['Score'] = $this->lang->line('recent');
        return json_encode($scores);
    }

    function get_topics_with_lowest_scores($number) {
        $this->get_answer_array();
        $topics = $this->get_topics();
        $n = count($topics);
        $scores;
        for ($i = 0, $j = 0; $j < $n; $j++, $i++) {
            $scores[$i]['Topic'] = $topics[$j]['Topic'];
            $scores[$i]['Score'] = $this->calculate_score($topics[$j]['Topic']);
        }
        foreach ($scores as $key => $row) {
            $topic[$key] = $row['Topic'];
            $score[$key] = $row['Score'];
        }
        array_multisort($score, SORT_ASC, $topic, SORT_ASC, $scores);
        $top = array_slice($scores, 0, $number);
        $topics;
        for ($i = 0; $i < $number; $i++) {
            $topics[$i] = $top[$i]['Topic']; //echo $topics[$i];
        }
        return $topics;
    }

    function get_username_id() {
        $namesid;
        $query = $this->db->select('Name, idPatient')->get('Patient');
        return $query->result();
    }
    
    /////////////////////////////////////////////
    //  Get all answers from all patients, 
    //  itterate through all the patients, per patients through all answers and calculate avg score for that patient then
    //  Result is array with all average scores per patient, then sort ascending and get 10 worst scoring patients
    //
    ////////////////////////////////////////////

    function calculate_avg() {
        $namesid;
        $allanswers;
        $temp;
        $temp2;
        $results = array();
        $avg = 0;
        $k = 0;
        $query = $this->db->select('Name, idPatient')->order_by('idPAtient', 'ASC')->get('Patient');
        $names = $query->row()->Name;

        $namesid = $query->result();
        for ($i = 0; $i < count($namesid); $i++) {      //get all last answers from all patients
            $id = $namesid[$i]->idPatient;
            $query2 = $this->db->select('Patient_idPatient, Answer')->where('Patient_idPatient', $id)->order_by('DateTime', 'DESC')->limit(52)->get('Patient_Answered_Question');
            $temp = $query2->result();
            if (isset($temp2)) {
                $allanswers = array_merge($temp2, $temp);
            } else {
                $allanswers = $temp;
            }

            $temp2 = $allanswers;
        }

        for ($i = 0; $i < count($namesid); $i++) {          //iterate through all patients
            $sum = 0;
            $id = $namesid[$i]->idPatient;
            for ($j = 0; $j < count($allanswers); $j++) {   //iterate through all the answers the patient has answered
                if ($allanswers[$j]->Patient_idPatient == $namesid[$i]->idPatient) {
                    $sum += $allanswers[$j]->Answer - 1;
                    $k++;
                }
            }
            if ($k == 0) {
                $avg = 0;
            } else {
                $avg = $sum * 25 / $k;
            }
            $k = 0;
            $nombre_format_francais = number_format($avg, 2, ',', ' ');

            if ($avg != 100 && $avg != 0) {     //check if the patient has allready filled in answers
                array_push($results, array('Score' => $nombre_format_francais, 'Name' => $namesid[$i]->Name));
            }
        }
        foreach ($results as $key => $row) {
            $score[$key] = $row['Score'];
            $name[$key] = $row['Name'];
        }


        array_multisort($score, SORT_ASC, $name, SORT_ASC, $results);
        $temp = array_slice($results, 0, 15);
        foreach ($temp as $patient) {
            $patient['Name_url'] = rawurlencode($patient['Name']);
            $urgent[] = $patient;
        }
        return $urgent;
    }
    
    /////////////////////////////////////////////
    //  Funtction to supply the charts in the personal tab (chart per person)
    //  
    //  First see if patient is english or dutch -> different topics and questions belinging to it
    //  
    //  Then itterate per topic through the questions, 
    //  Array will be Topic -> score
    //  
    //
    ////////////////////////////////////////////

    function topicscorejson($id) {
        $topic_array = $this->lang->line('topic_array');
        $topicsenglish = $this->db->distinct()->select('Topic')->where('Language', 'english')->get('Question')->result();
        $topicsdutch = $this->db->distinct()->select('Topic')->where('Language', 'dutch')->get('Question')->result();
        $questionsenglish = $this->db->select('Topic, QuestionNumber, Question')->where('Language', 'english')->get('Question')->result();
        $questionsdutch = $this->db->select('Topic, QuestionNumber, Question')->where('Language', 'dutch')->get('Question')->result();
        $patient = $this->db->select('idPatient, Name, Language, Note')->where('idPatient', $id)->get('Patient')->result();
        $query = $this->db->select('Answer, Question_Number')->where('Patient_idPatient', $patient[0]->idPatient)->order_by('DateTime', 'DESC')->limit(52)->get('Patient_Answered_Question');
        $answers = $query->result();

        if ($patient[0]->Language == 'dutch') { //dutch case
            //calculate topics
            $display = array();
            $display2 = array();
            
            for ($j = 0; $j < count($topicsdutch); $j++) {       //itterate through the topics
                $current = $topicsdutch[$j]->Topic;

                $topicscore = 0;
                $topicavg;
                $k = 0;     //amount of questions in the topic

                for ($a = 0; $a < count($questionsdutch); $a++) {    //itterate through the questions
                    if ($topicsdutch[$j]->Topic == $questionsdutch[$a]->Topic) {  //if question is part of topic do...s
                        $questionnr = $questionsdutch[$a]->QuestionNumber;
                        for ($o = 0; $o < count($answers); $o++) {
                            if ($questionnr == $answers[$o]->Question_Number) {
                                $topicscore += $answers[$o]->Answer - 1;
                                $k++;
                            }
                        }
                    }
                }
                if ($k == 0) {
                    $topicavg = 0;
                } else {
                    $topicavg = $topicscore * 25 / $k;
                }
                $nombre_format_francais = number_format($topicavg, 2, ',', ' ');
                array_push($display, array('Topic' => $topic_array[$j], 'Score' => $nombre_format_francais));
                $jsoncode = json_encode($display);
                
            }
        } else { //english case
            //calculate score per topic
            $display = array();
            $display2 = array();
            for ($j = 0; $j < count($topicsenglish); $j++) {       //itterate through the topics
                $current = $topicsenglish[$j]->Topic;

                $topicscore = 0;
                $topicavg;
                $k = 0;     //amount of questions in the topic

                for ($a = 0; $a < count($questionsenglish); $a++) {    //itterate through the questions
                    if ($topicsenglish[$j]->Topic == $questionsenglish[$a]->Topic) {  //if question is part of topic do...s
                        $questionnr = $questionsenglish[$a]->QuestionNumber;
                        for ($o = 0; $o < count($answers); $o++) {
                            if ($questionnr == $answers[$o]->Question_Number) {
                                $topicscore += $answers[$o]->Answer - 1;
                                $k++;
                            }
                        }
                    }
                }
                if ($k == 0) {
                    $topicavg = 0;
                } else {
                    $topicavg = $topicscore * 25 / $k;
                }
                $nombre_format_francais = number_format($topicavg, 2, ',', ' ');
                array_push($display, array('Topic' => $topic_array[$j], 'Score' => $nombre_format_francais));
                $jsoncode = json_encode($display);
                
            }
        }return $jsoncode;
    }

    function current_user($username) {
        if ($username == NULL) {
            return " nobody, please select someone.";
        }
        return $username;
    }

    function getJSONtable() {
        $bar = "danger";
        $resultarray = array();
        $newDate = $this->lang->line('not_filled_in');
        $topic_array = $this->lang->line('topic_array');
        $topicsenglish = $this->db->distinct()->select('Topic')->where('Language', 'english')->get('Question')->result();
        $topicsdutch = $this->db->distinct()->select('Topic')->where('Language', 'dutch')->get('Question')->result();
        $questionsenglish = $this->db->select('Topic, QuestionNumber, Question')->where('Language', 'english')->get('Question')->result();
        $questionsdutch = $this->db->select('Topic, QuestionNumber, Question')->where('Language', 'dutch')->get('Question')->result();
        $query = $this->db->select('idPatient, Name, Language, Note, RoomNumber, Gender, Birthday')->get('Patient');
        $patients = $query->result();
        for ($i = 0; $i < count($patients); $i++) {  //get all the date for one person
            $query = $this->db->select('Answer, Question_Number, DateTime')->where('Patient_idPatient', $patients[$i]->idPatient)->order_by('DateTime', 'DESC')->limit(52)->get('Patient_Answered_Question');
            $answers = $query->result();
            $note = $patients[$i]->Note;
            $id = $patients[$i]->idPatient;
            
                if ($patients[$i]->Language == 'dutch') { //dutch case
                //get date of last filled in question
                $lastq = array_slice($answers, 0, 1);
                foreach ($answers as $row) {
                    list($date, $time) = explode(" ", $row->DateTime); // splits database version of date
                    $originalDate = $date;
                    $newDate = date("d-m-Y", strtotime($originalDate));
                }
                if (empty($answers)) {
                    $newDate = $this->lang->line('not_filled_in');
                }

                //calculate topics
                $display = array();     //to calculate avg scores per topic
                $display2 = array();
                $badanswers = array();
                $extradata = array();
                for ($j = 0; $j < count($topicsdutch); $j++) {       //itterate through the topics
                    $current = $topicsdutch[$j]->Topic;

                    $topicscore = 0;
                    $topicavg;
                    $k = 0;     //amount of questions in the topic

                    for ($a = 0; $a < count($questionsdutch); $a++) {    //itterate through the questions
                        if ($topicsdutch[$j]->Topic == $questionsdutch[$a]->Topic) {  //if question is part of topic do...s
                            $questionnr = $questionsdutch[$a]->QuestionNumber;
                            for ($o = 0; $o < count($answers); $o++) {
                                if ($questionnr == $answers[$o]->Question_Number) {
                                    $topicscore += $answers[$o]->Answer - 1;
                                    $k++;
                                }
                            }
                        }
                    }
                    if ($k == 0) {
                        $topicavg = 0;
                    } else {
                        $topicavg = $topicscore * 25 / $k;
                    }
                    $nombre_format_francais = number_format($topicavg, 2, ',', ' ');
                    array_push($display, array('Topic' => $topic_array[$j], 'Score' => $nombre_format_francais));
                    $jsoncode = json_encode($display);
                    array_push($display2, array('Topic' => $topic_array[$j], 'Score' => $nombre_format_francais));
                }
                
                //worst topic
                $display2 = $display;
                foreach ($display2 as $key1 => $row1) {
                    $topiccc[$key1] = $row1['Topic'];
                    $scoreee[$key1] = $row1['Score'];
                }
                array_multisort($scoreee, SORT_ASC, $topiccc, SORT_ASC, $display2);
                $lowesttopic = array_slice($display2, 0, 1);
                
                //calculate avg 
                $amount = 0;
                $score = 0;
                for ($m = 0; $m < count($answers); $m++) {
                    $score += $answers[$m]->Answer - 1;
                    $amount++;
                } 
                if ($amount == 0) {
                    $personavg = 0;
                    $nombre_format_francais = 0;
                } else {
                    $personavg = $score * 25 / $amount;
                    $nombre_format_francais = number_format($personavg, 0, ',', ' ');
                }

                //print out questions with worst answers
                    for ($a = 0; $a < count($answers); $a++) {
                    if ($answers[$a]->Answer == 1) {
                        for ($b = 0; $b < count($questionsdutch); $b++) {
                            if ($questionsdutch[$b]->QuestionNumber == $answers[$a]->Question_Number) {
                                array_push($badanswers, array('Question' => $questionsdutch[$b]->Question));
                            }
                        }
                    }
                }

                //calculate some extra data: room, gender...
                if ($patients[$i]->RoomNumber != NULL) {
                    $roomnumber = $patients[$i]->RoomNumber;
                } else {
                    $roomnumber = "";
                }
                if ($patients[$i]->Gender != NULL) {
                    $gender = $patients[$i]->Gender;
                } else {
                    $gender = "";
                }
                if ($patients[$i]->Birthday != NULL) {
                    $bday = $patients[$i]->Birthday;
                } else {
                    $bday = "";
                }

                array_push($extradata, array('Roomnumber' => $roomnumber, 'Gender' => $gender, 'Bday' => $bday));
                array_push($resultarray, array('Last' => $newDate, 'Extra' => $extradata, 'Bar' => $bar, 'BadAnswer' => $badanswers, 'Name' => $patients[$i]->Name, 'Topic' => $lowesttopic[0]['Topic'], 'Score' => $nombre_format_francais, 'Topicscores' => $display, /* 'JSONCODE' => $jsoncode, */ 'Note' => $note, 'Count' => $patients[$i]->idPatient, 'id' => $id));
            
                
                } else { //english case
            
                //get date of last filled in question
                $lastq = array_slice($answers, 0, 1);
                foreach ($answers as $row) {
                    list($date, $time) = explode(" ", $row->DateTime);
                    //echo $date;
                    $originalDate = $date;
                    $newDate = date("d-m-Y", strtotime($originalDate));
                }
                
                if (empty($answers)) {
                    $newDate = $this->lang->line('not_filled_in');
                }

                //calculate score per topic
                $display = array();
                $badanswers = array();
                $display2 = array();
                $extradata = array();
                for ($j = 0; $j < count($topicsenglish); $j++) {       //itterate through the topics
                    $current = $topicsenglish[$j]->Topic;

                    $topicscore = 0;
                    $topicavg;
                    $k = 0;     //amount of questions in the topic

                    for ($a = 0; $a < count($questionsenglish); $a++) {    //itterate through the questions
                        if ($topicsenglish[$j]->Topic == $questionsenglish[$a]->Topic) {  //if question is part of topic do...s
                            $questionnr = $questionsenglish[$a]->QuestionNumber;
                            for ($o = 0; $o < count($answers); $o++) {
                                if ($questionnr == $answers[$o]->Question_Number) {
                                    $topicscore += $answers[$o]->Answer - 1;
                                    $k++;
                                }
                            }
                        }
                    }
                    
                    if ($k == 0) {
                        $topicavg = 0;
                    } else {
                        $topicavg = $topicscore * 25 / $k;
                    }
                    $nombre_format_francais = number_format($topicavg, 2, ',', ' ');
                    array_push($display, array('Topic' => $topic_array[$j], 'Score' => $nombre_format_francais));
                    //array_push($display, array('Topic' => $topicsenglish[$j]->Topic, 'Score' => $nombre_format_francais));
                    $jsoncode = json_encode($display);
                    array_push($display2, array('Topic' => $topic_array[$j], 'Score' => $nombre_format_francais));
                    //array_push($display2, array('Topic' => $topicsenglish[$j]->Topic, 'Score' => $nombre_format_francais));
                }

                //calculate worst topic here
                $display2 = $display;

                foreach ($display2 as $key2 => $row2) {
                    $topicccc[$key2] = $row2['Topic'];
                    $scoreeee[$key2] = $row2['Score'];
                }

                array_multisort($scoreeee, SORT_ASC, $topicccc, SORT_ASC, $display2);
                $lowesttopic = array_slice($display2, 0, 1);

                //calculate avg
                $amount = 0;
                $score = 0;
                for ($m = 0; $m < count($answers); $m++) {
                    $score += $answers[$m]->Answer - 1;
                    $amount++;
                }if ($amount == 0) {
                    $personavg = 0;
                    $nombre_format_francais = 0;
                } else {
                    $personavg = $score * 25 / $amount;
                    $nombre_format_francais = number_format($personavg, 0, ',', ' ');
                    
                }
                //get the answers where worst answers is given
                $badanswers = array();
                for ($a = 0; $a < count($answers); $a++) {
                    if ($answers[$a]->Answer == 1) {
                        $qnr = $answers[$a]->Question_Number;
                        for ($b = 0; $b < count($questionsenglish); $b++) {
                            if ($questionsenglish[$b]->QuestionNumber == $answers[$a]->Question_Number) {
                                array_push($badanswers, array('Question' => $questionsenglish[$b]->Question));
                            }
                        }
                    }
                }
//            if(count($answerarray == 0)){
//                array_push($answerarray, " ");
//            }
                //calculate some extra data: room, gender...
                if ($patients[$i]->RoomNumber != NULL) {
                    $roomnumber = $patients[$i]->RoomNumber;
                } else {
                    $roomnumber = "";
                }
                if ($patients[$i]->Gender != NULL) {
                    $gender = $patients[$i]->Gender;
                } else {
                    $gender = "";
                }
                if ($patients[$i]->Birthday != NULL) {
                    $bday = $patients[$i]->Birthday;
                } else {
                    $bday = "";
                }

                array_push($extradata, array('Roomnumber' => $roomnumber, 'Gender' => $gender, 'Bday' => $bday));
                array_push($resultarray, array('Last' => $newDate, 'Extra' => $extradata, 'Bar' => $bar, 'BadAnswer' => $badanswers, 'Name' => $patients[$i]->Name, 'Topic' => $lowesttopic[0]['Topic'], 'Score' => $nombre_format_francais, 'JSONCODE' => $jsoncode, 'Topicscores' => $display, 'Count' => $patients[$i]->idPatient, 'Note' => $note, 'id' => $id));
            }
        }
        return $resultarray;
    }

    function updatenote($note, $id) {

        $this->db->set('Note', $note);
        $this->db->where('idPatient', $id);
        $this->db->update('Patient');
    }

    function add_message($message) {
        if ($message == "" || strlen($message) > 255) {
            //error message, nothing filled in or too long
        } else {
            //echo "..".$message."..";
            $name = $this->session->Name;
            date_default_timezone_set("Europe/Brussels");
            $data = array(
                'Name' => $name,
                'Message' => $message,
                'Date' => date('Y-m-d H:i:s')
            );
            $this->db->insert('a16_webapps_2.Messages', $data);
            return json_encode($data);
        }
    }

    function show_messages() {
        $messages;
        $query = $this->db->select('Name, Message, Date')->order_by('Date', 'DESC')->limit(10)->get('Messages');
        $messages = $query->result();
        $result = array();
        $messageshow = array();

        for ($i = count($messages) - 1; $i >= 0; $i--) {
            // $date = strtotime($$messages[$i]['Date']);
            //$mysqldate = date( 'Y-m-d H:i:s', $date );
            array_push($result, array('Name' => $messages[$i]->Name, 'Message' => $messages[$i]->Message, 'Date' => $messages[$i]->Date));
        }


//        for($j = 0; $j < count($result); $j++){
//            echo " ".$result[$j]['Message'];
//        }
        return $result;
    }

}
