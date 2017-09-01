var $password = $("#password");
var $username = $("#username");
var $password1 = $("#password1");
var $password2 = $("#password2");
var $usertype = $("#usertype");
var $language = $("#language");
var $errorbox = $("#errorbox");

function login(){
    var pass = $("#password").val();
    var user = $("#username").val();
    $.post('login_valid', {'password':pass, 'username':user}, function(data){
        if(data.valid_user && data.correct_password){
            window.location.href = "loginPost";
        }
        else{
            
            $("#error1").removeClass('inactive');
            $("#error1").html(data.errormessage);
            $("#errorbox").removeClass('inactive');
            $("#errorbox").html(data.errormessage);
        }
    });
}

function register(){
    var user = $("#username").val();
    var pass1 = $("#password1").val();
    var pass2 = $("#password2").val();
    var lang = $("#language").val();
    var type = $("#usertype").val();
    var gender = $('#gender').val();
    var birthdate= $('#birthdate').val();
    $.post('registerPost', {username: user, password1:pass1, password2:pass2, usertype:type, language:lang, gender:gender, birthdate:birthdate}, function(data){
        if(data.success){
            $("#errorbox").removeClass('alert-warning');
            $("#errorbox").addClass('alert-success');
            $("#errorbox").hide();
            showToast(data.err_msg);
        }
        else{
            $("#errorbox").show();
            $("#errorbox").removeClass('alert-success');
            $("#errorbox").addClass('alert-warning');
        }
        $("#errorbox").removeClass('inactive');
        $("#errorbox").html(data.err_msg);
        
    });
}



