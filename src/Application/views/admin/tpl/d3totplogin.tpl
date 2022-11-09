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

            [{if !empty($Errors.default)}]
                [{include file="inc_error.tpl" Errorlist=$Errors.default}]
            [{/if}]

            [{$oView->getBackupCodeCountMessage()}]

            <label for="d3totp">[{oxmultilang ident="TOTP_INPUT"}]</label>
            <input type="text" name="d3totp" id="d3totp" value="" size="49" autofocus autocomplete="off"><br>

            [{oxmultilang ident="TOTP_INPUT_HELP"}]

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
