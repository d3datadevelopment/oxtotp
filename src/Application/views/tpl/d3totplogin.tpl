[{capture append="oxidBlock_content"}]
    [{assign var="template_title" value=""}]

    [{if $oView->previousClassIsOrderStep()}]
        [{* ordering steps *}]
        [{include file="page/checkout/inc/steps.tpl" active=2}]
    [{/if}]

    <form action="[{$oViewConf->getSelfActionLink()}]" method="post" name="login" id="login">
        [{$oViewConf->getHiddenSid()}]

        <input type="hidden" name="fnc" value="checkTotplogin">
        <input type="hidden" name="cl" value="[{$oView->getPreviousClass()}]">
        [{$navFormParams}]

        [{if $Errors.default|@count}]
            [{include file="inc_error.tpl" Errorlist=$Errors.default}]
        [{/if}]

        [{$oView->getBackupCodeCountMessage()}]

        <label for="d3totp">[{oxmultilang ident="TOTP_INPUT"}]</label>
        <input type="text" name="d3totp" id="d3totp" value="" size="49" autofocus autocomplete="off"><br>

        [{oxmultilang ident="TOTP_INPUT_HELP"}]

        [{* prevent cancel button (1st button) action when form is sent via Enter key *}]
        <input type="submit" style="display:none !important;">

        <input class="btn btn_cancel" value="[{oxmultilang ident="TOTP_CANCEL_LOGIN"}]" type="submit"
               onclick="document.getElementById('login').fnc.value='d3CancelLogin'; document.getElementById('login').submit();"
        >
        <input type="submit">
    </form>

    [{oxstyle include=$oViewConf->getModuleUrl('d3totp', 'out/admin/src/css/d3totplogin.css')}]
    [{oxstyle}]

    [{insert name="oxid_tracker" title=$template_title}]
[{/capture}]

[{include file="layout/page.tpl"}]