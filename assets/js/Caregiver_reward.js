/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function submitChange(Reward, el) { // will perform an ajax .post
    var value = $(el).is(':checked');
    $.post("editReward", {'Reward': Reward, 'available': value});
}

function submitRecievedChange(Id, el) { // will perform an ajax .post
    
    var value = $(el).is(':checked');
    
    $.post("editRecievedReward", {'changed': value, 'id': Id});
}


$('#rewardForm').submit(function(){
    $.post($(this).attr('action'), $(this).serialize(), function(res){
        // Do something with the response `res`
        $('#rewards_list').html(res)
        document.getElementById('rewardForm').reset();
        
    });
    showToast();
    return false;// prevent default submit action

});


