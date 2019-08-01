[{capture append="oxidBlock_content"}]
    [{assign var="template_title" value=""}]

    [{if $oView->previousClassIsOrderStep()}]
        [{* ordering steps *}]
        [{include file="page/checkout/inc/steps.tpl" active=2}]
    [{/if}]

    <div class="row">
        <div class="col-xs-12 col-sm-10 col-md-6 col-sm-offset-1 col-md-offset-3 mainforms">
            <form action="[{$oViewConf->getSelfActionLink()}]" method="post" name="login" id="login">
                [{$oViewConf->getHiddenSid()}]

                <input type="hidden" name="fnc" value="checkTotplogin">
                <input type="hidden" name="cl" value="[{$oView->getPreviousClass()}]">
                [{$navFormParams}]

                [{if $Errors.default|@count}]
                    [{include file="inc_error.tpl" Errorlist=$Errors.default}]
                [{/if}]

                [{$oView->getBackupCodeCountMessage()}]

                <label for="d3totp">[{oxmultilang ident="D3_TOTP_INPUT"}]</label>
                <input type="text" name="d3totp" id="d3totp" value="" size="49" autofocus autocomplete="off"><br>

                [{oxmultilang ident="D3_TOTP_INPUT_HELP"}]

                <input type="submit" value="[{oxmultilang ident="D3_TOTP_SUBMIT_LOGIN"}]" class="btn btn-primary"><br>
            </form>
            <form action="[{$oViewConf->getSelfActionLink()}]" method="post" name="login" id="login">
                [{$oViewConf->getHiddenSid()}]

                <input type="hidden" name="fnc" value="cancelTotplogin">
                <input type="hidden" name="cl" value="[{$oView->getPreviousClass()}]">
                [{$navFormParams}]
                <input class="btn btn_cancel" value="[{oxmultilang ident="D3_TOTP_CANCEL_LOGIN"}]" type="submit">
            </form>
        </div>
    </div>

    [{oxstyle include=$oViewConf->getModuleUrl('d3totp', 'out/flow/src/css/d3totplogin.css')}]
    [{oxstyle}]

    [{insert name="oxid_tracker" title=$template_title}]
[{/capture}]

[{include file="layout/page.tpl"}]