<?php

require_once('init.php');

echo <<<_END
<html><head><title>Financial Panel</title></head>

<form>
    <input type="hidden" value="1" name ="opt"/>
    AC List: 君安 000 华安 000<hr/>
    <label>A/P:</label>
    <input type="edit" value="000000" placeholder="account" name="account" id="account"/>
    <input type="password" placeholder="password" name="passwd" id="passwd"/>
    <input type="edit" placeholder="verify code" name="verify_code" id="verify_code"/>
    <input type="button" value="GetVerifyPic" onclick="fi.getVerifyPic()"/> |
    <input type="button" value="Login" onclick="fi.login()"/><hr/>
    <label>Year:</label>
    <input type="edit" value="2019" placeholder="year" name="year" id="year"/>
    <input type="button" value="Acquire Data" onclick="fi.acquireData()"/>
</form>
<hr/>

<div id="ctn">
<div id="des"/></div><hr>
<!-- Verify image -->
<img id="vr_img"/>
</div>

<script src="../libs/jquery/jquery.min.js"></script>
<script>

var fi = {};
fi.getVerifyPic = function() {

    $('#des').html('');
    $('#vr_img').attr('src', '');

    $.post('financial_opt.php', { opt : 'getVerifyPic'}, function(resp) {
        $('#des').html(resp);
        if (resp.indexOf('got_verify_pic') != -1) {
            $('#vr_img').attr('src', 'tmp/verify.jpg');
            // $('#vr_img').refresh();
        }
	});
}

fi.login = function() {
    let code = $('#verify_code').val();
    if (!code) {
        alert('Please input verify code first!');
        return;
    }

    $.post('financial_opt.php',
        { opt : 'login',
            veriCode : code,
            passwd : $('#passwd').val(),
            account : $('#account').val()
        }, function(resp) {
            $('#des').html(resp);
        });
}

fi.acquireData = function() {

    $.post('financial_opt.php',
        {
            opt : 'acquireData',
            account : $('#account').val(),
            year : $('#year').val()
        }, function(resp) {
            $('#des').html(resp);
        });
}


var wdAct = {};

wdAct.click = function() {
    let text = $('#wd_link').text();
    $.post('word_opt.php', { opt : 'accessWord', word : text}, function(resp) {
        console.log(resp);
    });

    let audios = $('td[word=' + text +'] > audio');
    if (audios.length) {
        let v = audios[0];
        v.play();
    }
}
    
$(document).ready(function(){
    // $('#wd_shown').click();
});

</script>

<style>
</style>

_END;





