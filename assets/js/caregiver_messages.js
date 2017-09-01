$(function(){
    if($("#message_scrollbox").length){
        $("#message_scrollbox").scrollTop($("#message_scrollbox")[0].scrollHeight);
    }
});


function send_msg() {
    var message = document.getElementById("btn-input").value;
    $.post("send_message", {message: message}, function(message) {
        $("#msgbox1").append("<li class='list-group-item'><div class='chat-body clearfix'><div class='header'><b>"+
                message.Name +
                "</b><small class='pull-right text-muted'>"+
                message.Date +
                "</small></div><p>" +
                message.Message +
                "</p></div></li>");
        document.getElementById("updatebox").innerHTML = "Sent!";
        $('#updatebox').show();
        setTimeout(function () {
            $('#updatebox').fadeOut();
        }, 1500); // <-- time in milliseconds
        document.getElementById("btn-input").value = "";
        $(".panel-body").scrollTop($(".panel-body")[0].scrollHeight);
    });

}
;
