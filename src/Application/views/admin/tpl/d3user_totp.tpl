[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign}]

[{oxstyle include="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"}]
[{oxscript include="https://code.jquery.com/jquery-3.2.1.slim.min.js"}]
[{oxscript include="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"}]
[{oxscript include="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"}]
[{oxstyle include="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/solid.min.css"}]
[{oxstyle}]

[{assign var="totp" value=$edit->d3GetTotp()}]
[{assign var="userid" value=$edit->getId()}]
[{$totp->loadByUserId($userid)}]

[{if $readonly}]
    [{assign var="readonly" value="readonly disabled"}]
[{else}]
    [{assign var="readonly" value=""}]
[{/if}]

<style>
    td.edittext {
        white-space: normal;
    }
    .hero {
        display: inline-block;
    }
    .hero > h1 {
        padding: 0.3em 0;
    }
    .hero > div {
        text-align: right;
        color: #6c7c98;
    }
    .container-fluid {
        font-size: 13px;
    }
</style>

[{if $force2FA}]
    <div class="hero">
        <h1>[{oxmultilang ident="D3_TOTP_FORCE2FATITLE"}]</h1>
        <div>[{oxmultilang ident="D3_TOTP_FORCE2FASUB"}]</div>
    </div>
[{/if}]

<form name="transfer" id="transfer" action="[{$oViewConf->getSelfLink()}]" method="post">
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="oxid" value="[{$oxid}]">
    <input type="hidden" name="cl" value="[{$oViewConf->getActiveClassName()}]">
</form>

<form name="myedit" id="myedit" action="[{$oViewConf->getSelfLink()}]" method="post" style="padding: 0;margin: 0;height:0;">
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="cl" value="[{$oViewConf->getActiveClassName()}]">
    <input type="hidden" name="fnc" value="">
    <input type="hidden" name="oxid" value="[{$oxid}]">
    <input type="hidden" name="editval[d3totp__oxid]" value="[{$totp->getId()}]">
    <input type="hidden" name="editval[d3totp__oxuserid]" value="[{$oxid}]">

    [{if $sSaveError}]
        <table style="padding:0; border:0; width:98%;">
            <tr>
                <td></td>
                <td class="errorbox">[{oxmultilang ident=$sSaveError}]</td>
            </tr>
        </table>
    [{/if}]

    [{if $oxid && $oxid != '-1'}]
    <div class="container-fluid">
        <div class="row">
            <div class="col-6">
                <div class="card">
                    [{block name="user_d3user_totp_form1"}]
                        [{if false == $totp->getId()}]
                            <div class="card-header">
                                [{oxmultilang ident="D3_TOTP_REGISTERNEW"}]
                            </div>
                            <div class="card-body">
                                [{oxmultilang ident="D3_TOTP_QRCODE"}]

                                [{$totp->getQrCodeElement()}]
                                [{oxinputhelp ident="D3_TOTP_QRCODE_HELP"}]
                            </div>
                        [{elseif $force2FA}]
                            <div class="card-header">
                                [{oxmultilang ident="D3_TOTP_ADMINBACKEND"}]
                            </div>
                            <div class="card-body">
                                <input
                                    type="submit" class="edittext" id="oLockButton" name="delete"
                                    value="[{oxmultilang ident="D3_TOTP_ADMINCONTINUE"}]"
                                    onClick="document.myedit.fnc.value='';document.myedit.cl.value='admin_start'"
                                >
                            </div>
                        [{else}]
                            <div class="card-header">
                                [{oxmultilang ident="D3_TOTP_REGISTEREXIST"}]
                            </div>
                            <div class="card-body">
                                <dl>
                                    <dt>
                                        [{oxmultilang ident="D3_TOTP_REGISTERDELETE_DESC"}]
                                    </dt>
                                    <dd>
                                        <input type="submit" class="edittext" id="oLockButton" name="delete" value="[{oxmultilang ident="D3_TOTP_REGISTERDELETE"}]" onClick="document.myedit.fnc.value='delete'" [{$readonly}]>
                                    </dd>
                                </dl>
                            </div>
                        [{/if}]
[{*****}]
                    [{/block}]
                </div>
            </div>
            <div class="col-6">
                <div class="card">
                    [{block name="user_d3user_totp_form2"}]
                        [{if false == $totp->getId()}]
                            <div class="card-header">
                                [{oxmultilang ident="D3_TOTP_BACKUPCODES"}]
                                Bestaetigung
                            </div>
                            <div class="card-body">
                                <label for="secret">[{oxmultilang ident="D3_TOTP_SECRET"}]</label>
                                <textarea rows="3" cols="50" id="secret" name="secret" class="editinput" readonly="readonly">[{$totp->getSecret()}]</textarea>
                                [{oxinputhelp ident="D3_TOTP_SECRET_HELP"}]

                                <label for="otp">[{oxmultilang ident="D3_TOTP_CURROTP"}]</label>
                                <input type="text" class="editinput" size="6" maxlength="6" id="otp" name="otp" value="" [{$readonly}]>
                                [{oxinputhelp ident="D3_TOTP_CURROTP_HELP"}]

                                <input type="submit" class="edittext" id="oLockButton" name="save" value="[{oxmultilang ident="D3_TOTP_SAVE"}]" onClick="document.myedit.fnc.value='save'" [{$readonly}]>
                            </div>
                        [{else}]
                            <div class="card-header">
                                [{oxmultilang ident="D3_TOTP_BACKUPCODES"}]
                            </div>
                            <div class="card-body">
                                [{if $oView->getBackupCodes()}]
                                    <tr>
                                        <td>
                                            <label for="backupcodes">[{oxmultilang ident="D3_TOTP_BACKUPCODES_DESC"}]</label>
                                            <br>
                                            <br>
                                            <textarea id="backupcodes" rows="10" cols="20">[{$oView->getBackupCodes()}]</textarea>
                                        </td>
                                    </tr>
                                [{else}]
                                    <tr>
                                        <td>
                                            [{oxmultilang ident="D3_TOTP_AVAILBACKUPCODECOUNT" args=$oView->getAvailableBackupCodeCount()}]
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            [{oxmultilang ident="D3_TOTP_AVAILBACKUPCODECOUNT_DESC"}]
                                        </td>
                                    </tr>
                                [{/if}]
                            </div>
                        [{/if}]
                    [{/block}]
                </div>
            </div>
        </div>
    </div>

        <table style="padding:0; border:0; width:98%;">
            <tr>
[{***
                <td class="edittext" style="vertical-align: top; padding-top:10px;padding-left:10px; width: 50%;">
                    <table style="padding:0; border:0">
                        [{block name="user_d3user_totp_form1"}]
                            [{if false == $totp->getId()}]
                                <tr>
                                    <td class="edittext" colspan="2">
                                        <h4>[{oxmultilang ident="D3_TOTP_REGISTERNEW"}]</h4>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="edittext">
                                        [{oxmultilang ident="D3_TOTP_QRCODE"}]&nbsp;
                                    </td>
                                    <td class="edittext">
                                        [{$totp->getQrCodeElement()}]
                                        [{oxinputhelp ident="D3_TOTP_QRCODE_HELP"}]
                                    </td>
                                </tr>
                            [{elseif $force2FA}]
                                <tr>
                                    <td class="edittext" colspan="2">
                                        <h4>[{oxmultilang ident="D3_TOTP_ADMINBACKEND"}]</h4>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="edittext" colspan="2">
                                        <input
                                            type="submit" class="edittext" id="oLockButton" name="delete"
                                            value="[{oxmultilang ident="D3_TOTP_ADMINCONTINUE"}]"
                                            onClick="document.myedit.fnc.value='';document.myedit.cl.value='admin_start'"
                                        >

                                    </td>
                                </tr>
                            [{else}]
                                <tr>
                                    <td class="edittext" colspan="2">
                                        <h4>[{oxmultilang ident="D3_TOTP_REGISTEREXIST"}]</h4>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="edittext" colspan="2">
                                        [{oxmultilang ident="D3_TOTP_REGISTERDELETE_DESC"}]
                                    </td>
                                </tr>
                                <tr>
                                    <td class="edittext" colspan="2"><br><br>
                                        <input type="submit" class="edittext" id="oLockButton" name="delete" value="[{oxmultilang ident="D3_TOTP_REGISTERDELETE"}]" onClick="document.myedit.fnc.value='delete'" [{$readonly}]>
                                    </td>
                                </tr>
                            [{/if}]

                        [{/block}]
                    </table>
                </td>
**}]
                <!-- Anfang rechte Seite -->
                <td class="edittext" style="text-align: left; vertical-align: top; height:99%;padding-left:5px;padding-bottom:30px;padding-top:10px; width: 50%;">
                    <table style="padding:0; border:0">
                        [{block name="user_d3user_totp_form2"}]
                            [{if false == $totp->getId()}]
                                <tr>
                                    <td class="edittext" colspan="2">
                                        <h4>&nbsp;</h4>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="edittext">
                                        <label for="secret">[{oxmultilang ident="D3_TOTP_SECRET"}]</label>
                                    </td>
                                    <td class="edittext">
                                        <textarea rows="3" cols="50" id="secret" name="secret" class="editinput" readonly="readonly">[{$totp->getSecret()}]</textarea>
                                        [{oxinputhelp ident="D3_TOTP_SECRET_HELP"}]
                                    </td>
                                </tr>

                                <tr>
                                    <td class="edittext">
                                        <label for="otp">[{oxmultilang ident="D3_TOTP_CURROTP"}]</label>
                                    </td>
                                    <td class="edittext">
                                        <input type="text" class="editinput" size="6" maxlength="6" id="otp" name="otp" value="" [{$readonly}]>
                                        [{oxinputhelp ident="D3_TOTP_CURROTP_HELP"}]
                                    </td>
                                </tr>
                                <tr>
                                    <td class="edittext" colspan="2"><br><br>
                                        <input type="submit" class="edittext" id="oLockButton" name="save" value="[{oxmultilang ident="D3_TOTP_SAVE"}]" onClick="document.myedit.fnc.value='save'" [{$readonly}]>
                                    </td>
                                </tr>
                            [{else}]
                                <tr>
                                    <td class="edittext" colspan="2">
                                        <h4>[{oxmultilang ident="D3_TOTP_BACKUPCODES"}]</h4>
                                    </td>
                                </tr>
                                [{if $oView->getBackupCodes()}]
                                    <tr>
                                        <td>
                                            <label for="backupcodes">[{oxmultilang ident="D3_TOTP_BACKUPCODES_DESC"}]</label>
                                            <br>
                                            <br>
                                            <textarea id="backupcodes" rows="10" cols="20">[{$oView->getBackupCodes()}]</textarea>
                                        </td>
                                    </tr>
                                [{else}]
                                    <tr>
                                        <td>
                                            [{oxmultilang ident="D3_TOTP_AVAILBACKUPCODECOUNT" args=$oView->getAvailableBackupCodeCount()}]
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            [{oxmultilang ident="D3_TOTP_AVAILBACKUPCODECOUNT_DESC"}]
                                        </td>
                                    </tr>
                                [{/if}]
                            [{/if}]

                        [{/block}]
                    </table>
                </td>
                <!-- Ende rechte Seite -->
            </tr>
        </table>
    [{/if}]
</form>

[{if !$force2FA}]
    [{include file="bottomnaviitem.tpl"}]
    [{include file="bottomitem.tpl"}]
[{/if}]
