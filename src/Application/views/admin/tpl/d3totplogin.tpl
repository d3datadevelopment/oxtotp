<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <title>[{oxmultilang ident="LOGIN_TITLE"}]</title>
    <meta http-equiv="Content-Type" content="text/html; charset=[{$charset}]">
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <link rel="shortcut icon" href="[{$oViewConf->getImageUrl()}]favicon.ico">
    <link rel="stylesheet" href="[{$oViewConf->getResourceUrl()}]login.css">
    <link rel="stylesheet" href="[{$oViewConf->getResourceUrl()}]colors_[{$oViewConf->getEdition()|lower}].css">
</head>
<body>

<div class="admin-login-box">

    <div id="shopLogo"><img src="[{$oViewConf->getImageUrl('logo_dark.svg')}]" alt="" /></div>

    <form action="[{$oViewConf->getSelfLink()}]" method="post" id="login">

        [{block name="admin_login_form"}]
            [{$oViewConf->getHiddenSid()}]

            <input type="hidden" name="fnc" value="checklogin">
            <input type="hidden" name="cl" value="[{$oViewConf->getActiveClassName()}]">

            <h3>[{oxmultilang ident="TOTP_INPUT"}]</h3>

            [{if !empty($Errors.default)}]
                [{include file="inc_error.tpl" Errorlist=$Errors.default}]
            [{/if}]

            [{$oView->getBackupCodeCountMessage()}]

            <div class="container">
                <label for="1st">erste TOTP-Ziffer</label>
                <input type="text" name="d3totp[]" class="digit" id='1st' inputmode="numeric" pattern="[0-9]*" maxlength="1" required onkeyup="clickEvent('2nd')" autofocus autocomplete="off">
                <label for="2nd">zweite TOTP-Ziffer</label>
                <input type="text" name="d3totp[]" class="digit" id="2nd" inputmode="numeric" pattern="[0-9]*" maxlength="1" required onkeyup="clickEvent('3rd')" autocomplete="off">
                <label for="3rd">dritte TOTP-Ziffer</label>
                <input type="text" name="d3totp[]" class="digit" id="3rd" inputmode="numeric" pattern="[0-9]*" maxlength="1" required onkeyup="clickEvent('4th')" autocomplete="off">
                <label for="4th">vierte TOTP-Ziffer</label>
                <input type="text" name="d3totp[]" class="digit" id="4th" inputmode="numeric" pattern="[0-9]*" maxlength="1" required onkeyup="clickEvent('5th')" autocomplete="off">
                <label for="5th">fünfte TOTP-Ziffer</label>
                <input type="text" name="d3totp[]" class="digit" id="5th" inputmode="numeric" pattern="[0-9]*" maxlength="1" required onkeyup="clickEvent('6th')" autocomplete="off">
                <label for="6th">sechste TOTP-Ziffer</label>
                <input type="text" name="d3totp[]" class="digit" id="6th" inputmode="numeric" pattern="[0-9]*" maxlength="1" required autocomplete="off">
            </div>

            [{capture name="d3js"}]
                function clickEvent(next){
                    const digits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
                    if(digits.includes(event.key)){
                        document.getElementById(next).focus();
                    }
                }
                document.addEventListener("paste", function(e) {
                    if (e.target.type === "text") {
                        var data = e.clipboardData.getData('Text');
                        data = data.split('');
                        [].forEach.call(document.querySelectorAll("#login input[type=text]"), (node, index) => {
                            node.value = data[index];
                        });
                    }
                });
            [{/capture}]
            [{oxscript add=$smarty.capture.d3js}]

            <div>[{oxmultilang ident="TOTP_INPUT_HELP"}]</div>

            <input type="submit" value="[{oxmultilang ident="LOGIN_START"}]" class="btn"><br>

            <input class="btn btn_cancel" value="[{oxmultilang ident="TOTP_CANCEL_LOGIN"}]" type="submit"
                   onclick="document.getElementById('login').fnc.value='d3CancelLogin'; document.getElementById('login').submit();"
            >

            [{oxstyle include=$oViewConf->getModuleUrl('d3totp', 'out/admin/src/css/d3totplogin.css')}]
            [{oxstyle}]



[{**





            [{$oViewConf->getHiddenSid()}]

            <input type="hidden" name="fnc" value="">
            <input type="hidden" name="cl" value="login">

            [{if !empty($Errors.default)}]
                [{include file="inc_error.tpl" Errorlist=$Errors.default}]
            [{/if}]

            <div class="d3webauthn_icon">
                <div class="svg-container">
                    [{include file=$oViewConf->getModulePath('d3webauthn', 'out/img/fingerprint.svg')}]
                </div>
                <div class="message">[{oxmultilang ident="WEBAUTHN_INPUT_HELP"}]</div>
            </div>
**}]
            [{* prevent cancel button (1st button) action when form is sent via Enter key *}]
[{**
            <input type="submit" style="display:none !important;">

            <input class="btn btn_cancel" value="[{oxmultilang ident="WEBAUTHN_CANCEL_LOGIN"}]" type="submit"
                   onclick="document.getElementById('login').fnc.value='d3WebauthnCancelLogin'; document.getElementById('login').submit();"
            >

            [{oxstyle include=$oViewConf->getModuleUrl('d3webauthn', 'out/admin/src/css/d3webauthnlogin.css')}]
            [{oxstyle}]
**}]
        [{/block}]
    </form>
</div>

[{oxscript}]
<script type="text/javascript">if (window !== window.top) top.location.href = document.location.href;</script>

</body>
</html>
