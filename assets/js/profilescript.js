/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
var $errorbox = $("#errorbox");
var $message = "null";
var $reload = false;

function post_common() {
    $message = "";
    var select = document.getElementById("language");
    var lang = select.options[select.selectedIndex].value;
    $.post("change_language", {language: lang}, function(data){
        if(data.changed_lang){
            $reload = true;
        }
    });
    var old = $("#old").val();
    var newpass = $("#new").val();
    var conf = $("#conf").val();
    $.post("change_password", {old_password: old, new_password: newpass, conf_password: conf}, function(data){
        $message += data.err_msg;
    });
}

function Profile_help(){
    var helpText = document.getElementById("helpText");
    // Toggle 
    helpText.style.display == "block" ? helpText.style.display = "none" : 
    helpText.style.display = "block";
}

function post_elderly(){
    post_common();
    var room = $("#room_number").val();
    var phone = $("#phone_number").val();
    $.post("change_profile",{room_number: room,phone_number: phone},  function(data){
            //alert("pass_data = " + data.err_msg);
            if(data.changes_made){
                $message += data.err_msg;
            }
        if($reload){
            location.reload();
        }
        else{
            $("#errorbox").html($message);
            $("#errorbox").removeClass('inactive');
        }
    });
}

function post_caregiver(){
    post_common();
    var home = $("#home_address").val();
    var email = $("#email").val();
    var mobile = $("#mobile").val();
    $.post("change_profile",{home_address:home,email: email,mobile:mobile},  function(data){
            //alert("pass_data = " + data.err_msg);
        if(data.changes_made){
            $message += data.err_msg;
        }
        if($reload){
            location.reload();
        }
        else{
            $("#errorbox").html($message);
            $("#errorbox").removeClass('inactive');
        }
    });
}



