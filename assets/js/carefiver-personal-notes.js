function update_note(id) {
    $("#button"+id).show();
    $('#form'+id).hide();
    $("#text"+id).show();
    var new_note = document.getElementById(id + "s").value;
    $.post("update_note", {new_note: new_note, id: id});
    document.getElementById(id + "sss").innerHTML = "Updated!";
    $('#'+id+'sss').show();
    setTimeout(function () {
        $('#'+id+'sss').fadeOut();
    }, 1000); // <-- time in milliseconds
    
    $("#text"+id).text(new_note);
}

function editNote(id){
    $("#button"+id).hide();
    $('#form'+id).show();
    $("#text"+id).hide();
    
}

$(function(){
    $.get("isAdmin", function(isAdmin){
        if(isAdmin.isAdmin === "1"){
            $('#newuserButton').removeClass("inactive");
        }
    });
});
