

function back(){
    window.location.href = "questionnaire";
}

function Tips_help(){
    var helpText = document.getElementById("helpText");
    // Toggle 
    helpText.style.display == "block" ? helpText.style.display = "none" : 
    helpText.style.display = "block";
}

function forward(){
    window.location.href = "score";
}


