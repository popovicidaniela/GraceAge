/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/*
 * display text in the toast/snackbar. if text in empty, the toast wil show whatever text was already in the html
 */
function showToast(text) {
    // Get the snackbar DIV
    var x = document.getElementById("snackbar")
    if(text)x.innerHTML=text; // only change innhtml if text is not empty.

    // Add the "show" class to DIV
    x.className = "show";

    // After 3 seconds, remove the show class from DIV
    setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);
}