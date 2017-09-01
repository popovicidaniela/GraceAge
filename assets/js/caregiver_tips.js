var $deleted_tip = new Object();
var $language_map = new Object();
var $tips_list = $('#tips_list');
var localizedText;
$(document).ready(function(){
    $.getJSON("getTipsLocalization",function(data){
        localizedText = data;
    });
});

function register_topic(){
    var select = document.getElementById("select_topic");
    var chosen_topic = select.options[select.selectedIndex].value;
    $.post("get_tips", {topic: chosen_topic}, function(tips){
        $tips_list.empty();
        //alert(JSON.stringify(tips));
        $.each(tips, function (i, tip) {
            id = tip.idtips;
            if (tip.hasOwnProperty('dutch') && tip.dutch !== null) {

                $language_map[id] = "dutch";
                $stringdutch = "<div class='row'>" + "<div class='col-sm-10'> <li class='tipsstring'  id='" + tip.idtips + "' onClick='tipClick(this.id)'>" + tip.dutch + "</li> </div>"
                        + "<div class='col-xs-2'> <a class='edit fontfamily' id='button" + tip.idtips + "' onClick='tipClick(" + tip.idtips + ")'><i class='fa fa-pencil'></i> " +localizedText.edit+" </a><nobr><a class='delete' id='delete" + tip.idtips + "' onClick='deleteTip(" + tip.idtips + ")' value='delete'><i class='fa fa-trash'></i> delete </a></nobr></div>"
                        + "<input class='btn save' id='save" + tip.idtips + "'  type='button' onclick='updateTip(" +tip.idtips+ ")' value='save'>"+ "</div>";

                $tips_list.append($stringdutch); // make new <li> element with id = idtips 
            }
            
            if (tip.hasOwnProperty('english') && tip.english !== null) {
                
                $language_map[id] = "english";
                $stringenglish = "<div class='row'>" + "<div class='col-sm-10'> <li class='tipsstring'  id='" + tip.idtips + "' onClick='tipClick(this.id)'>" + tip.english + "</li> </div>"
                        + "<div class='col-xs-2'> <a class='edit fontfamily' id='button" + tip.idtips + "' onClick='tipClick(" + tip.idtips + ")'><i class='fa fa-pencil'></i> " +localizedText.edit+" </a><nobr><a class='delete' id='delete" + tip.idtips + "' onClick='deleteTip(" + tip.idtips + ")' value='delete'><i class='fa fa-trash'></i> delete </a></nobr></div>"
                        + "<input class='btn save' id='save" + tip.idtips + "'  type='button' onclick='updateTip(" +tip.idtips+ ")' value='save'>"+ "</div>";
                $tips_list.append($stringenglish); //old version : <li id='" + tip.idtips +"' onClick='tipClick(this.id)'>"+ tip.english +"</li>
            }
            
            $(document.getElementById("save"+tip.idtips)).hide();  //hide the save button belonging to id

        });
        
    });
    
    
}

function add_new_tip() {
    var select = document.getElementById("select_topic");
    var language = document.getElementById("select_language");
    var chosen_topic = select.options[select.selectedIndex].value;
    if (chosen_topic !== "0") {
        var chosen_language = language.options[language.selectedIndex].value;
        //alert(chosen_language);
        var new_tip = $("#new_tip").val();
        if(new_tip !=='') $.post("add_tip", {tip: new_tip, topic: chosen_topic, language: chosen_language}, function () {
            register_topic();
            //alert("yes...");
        });
        else alert(localizedText.write_a_tip);
    } else
        alert(localizedText.choose_a_topic);
}


   function tipClick(id){ // do something when a tip is clicked
            
       
      $(document.getElementById('editform')).remove(); // remove old form if it excists
      
      $("li").show(1000); // show the lines again
      $("[id^='button']").show(); //show all buttons again
      $("[id^='delete']").show(); //show all buttons again
      $("[id^='save']").hide(); //hide save button again

      
      $(document.getElementById("button"+id)).hide();  //hide the button belonging to id
      $(document.getElementById("delete"+id)).hide();  //hide the button belonging to id
      $(document.getElementById("save"+id)).show();  //show the save button belonging to id


      $element = $(document.getElementById(id));
      $element.hide();
      
      var text_value = document.getElementById(id).innerHTML;     
      //$(document.getElementById(id)).after("<form id='editform' ></form>"); // show a form here to update or delete the question
      //$(document.getElementById('editform')).append("<input type='text' id='newtext' value='"+text_value+"'>");
      //$(document.getElementById('editform')).append("<input type='button' onclick='updateTip(" + id+ ")' value='update'>"); // button run updateTip(id) on klick
      //$(document.getElementById('editform')).append("<input type='button' onclick='deleteTip(" + id+ ")' value='delete'>");
      
      $formHTML = "<form id='editform' >" + "<div class='col-sm-12'>" + "<input type='text' id='newtext' value='"+text_value+"'>" + "</div>" + "</form>";
      
      
       $element.after($formHTML);
       $("#editform").hide();
       $("#editform").show(500);
       
};

function updateTip(id){
    var select = document.getElementById("select_topic");
    var chosen_topic = select.options[select.selectedIndex].value;
    var new_tip = $("#newtext").val();
    var lang = "dutch";
    if($language_map[id] === "english"){
        //alert("inside if!");
        lang = "english";
    }
    
    //alert(lang);
    if(!new_tip) alert(localizedText.write_a_tip);
    else{
        $.post("update_tip", {tip: new_tip, topic: chosen_topic, id: id, language:lang}, function(){
            register_topic(); //refresh the tips
        });
    }
};

function undo(){
    $('#undo').addClass("inactive");
    $.post("add_tip", {tip: $deleted_tip.tip, topic: $deleted_tip.topic, language: $deleted_tip.language}, function () {
        register_topic();
    });
}

function deleteTip(id){
        // delete the tip
        $('#undo').removeClass("inactive");
        var select = document.getElementById("select_topic");
        $deleted_tip.tip = document.getElementById(id).innerHTML;
        $deleted_tip.topic = select.options[select.selectedIndex].value;
        $deleted_tip.language = $language_map[id];
        $.post("delete_tip", {id: id}, function(data){
            if(data){
                register_topic();
            }
            else{
                deleteTip(id);
            }
        });
    
    
    showToast(localizedText.notify_deleted);
    
}



