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
            <input type="hidden" name="profile" value="[{$selectedProfile}]">
            <input type="hidden" name="chlanguage" value="[{$selectedChLanguage}]">

            [{if !empty($Errors.default)}]
                [{include file="inc_error.tpl" Errorlist=$Errors.default}]
            [{/if}]

            [{$oView->getBackupCodeCountMessage()}]

            <div class="auth_container">
                <h3>[{oxmultilang ident="TOTP_INPUT"}]</h3>

                <div class="container">
                    <label for="1st">[{oxmultilang ident="D3_TOTP_INPUT_FIRST"}]</label>
                    <input type="text" name="d3totp[]" class="digit" id='1st' inputmode="numeric" pattern="[0-9]*" maxlength="1" onkeyup="clickEvent(null, '2nd')" autofocus autocomplete="off">
                    <label for="2nd">[{oxmultilang ident="D3_TOTP_INPUT_SECOND"}]</label>
                    <input type="text" name="d3totp[]" class="digit" id="2nd" inputmode="numeric" pattern="[0-9]*" maxlength="1" onkeyup="clickEvent('1st', '3rd')" autocomplete="off">
                    <label for="3rd">[{oxmultilang ident="D3_TOTP_INPUT_THIRD"}]</label>
                    <input type="text" name="d3totp[]" class="digit" id="3rd" inputmode="numeric" pattern="[0-9]*" maxlength="1" onkeyup="clickEvent('2nd', '4th')" autocomplete="off">
                    <label for="4th">[{oxmultilang ident="D3_TOTP_INPUT_FOURTH"}]</label>
                    <input type="text" name="d3totp[]" class="digit" id="4th" inputmode="numeric" pattern="[0-9]*" maxlength="1" onkeyup="clickEvent('3rd', '5th')" autocomplete="off">
                    <label for="5th">[{oxmultilang ident="D3_TOTP_INPUT_FIFTH"}]</label>
                    <input type="text" name="d3totp[]" class="digit" id="5th" inputmode="numeric" pattern="[0-9]*" maxlength="1" onkeyup="clickEvent('4th', '6th')" autocomplete="off">
                    <label for="6th">[{oxmultilang ident="D3_TOTP_INPUT_SIXTH"}]</label>
                    <input type="text" name="d3totp[]" class="digit" id="6th" inputmode="numeric" pattern="[0-9]*" maxlength="1" onkeyup="clickEvent('5th', null)" autocomplete="off">
                </div>

                [{capture name="d3js"}]
                    function clickEvent(previous, next){
                        const digitKeys = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
                        const deleteKeys = ['Backspace', 'Delete'];
                        if(next && digitKeys.includes(event.key)){
                            document.getElementById(next).focus();
                        } else if(previous && deleteKeys.includes(event.key)){
                            document.getElementById(previous).focus();
                        }
                    }
                    document.addEventListener("paste", function(e) {
                        if (!e.target.classList.contains('digit')) {
                            return;
                        }

                        e.preventDefault();

                        const data = e.clipboardData.getData('Text').split('');

                        document.querySelectorAll('#login .digit').forEach((node, index) => {
                            node.value = data[index] ?? '';
                        });
                    });
                [{/capture}]
                [{oxscript add=$smarty.capture.d3js}]

                <div>[{oxmultilang ident="TOTP_INPUT_HELP"}]</div>
            </div>

            <div class="bc_container hidden_container">
                <h3>[{oxmultilang ident="TOTP_INPUT_BC"}]</h3>

                <div>[{oxmultilang ident="TOTP_INPUT_BCHELP" suffix="COLON"}]</div>

                <input type="text" name="d3totpbc" class="bcinput" autocomplete="off" disabled="disabled">
            </div>

            <div>
                <input type="submit" value="[{oxmultilang ident="LOGIN_START"}]" class="btn"><br>

                <input class="btn btn_cancel" value="[{oxmultilang ident="TOTP_CANCEL_LOGIN"}]" type="submit"
                    onclick="document.getElementById('login').fnc.value='d3CancelLogin'; document.getElementById('login').submit();"
                >
            </div>

            <div class="auth_container modelink">
                <a href="#" id="switchToAuth">[{oxmultilang ident="TOTP_MODE_AUTH"}]</a>
            </div>

            <div class="bc_container modelink hidden_container">
                <a href="#" id="switchToBc">[{oxmultilang ident="TOTP_MODE_BC"}]</a>
            </div>

            [{oxstyle include=$oViewConf->getModuleUrl('d3totp', 'out/admin/src/css/d3totplogin.css')}]
            [{oxstyle}]

            [{capture name="d3js2"}]
                function toggleMode(e) {
                    e.preventDefault();

                    document.querySelectorAll('.auth_container, .bc_container')
                        .forEach(el => el.classList.toggle('hidden_container'));

                    document.querySelectorAll('.auth_container input, .bc_container input')
                        .forEach(el => el.disabled = !el.disabled);
                }

                document.querySelectorAll('.modelink a')
                    .forEach(el => el.addEventListener('click', toggleMode));
            [{/capture}]
            [{oxscript add=$smarty.capture.d3js2}]
        [{/block}]
    </form>
</div>

[{oxscript}]
<script type="text/javascript">if (window !== window.top) top.location.href = document.location.href;</script>

</body>
</html>
