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
                <div class="col-4">
                    <div class="card">
                        [{block name="user_d3user_totp_form1"}]
                            [{if false == $totp->getId()}]
                                <div class="card-header">
                                    [{oxmultilang ident="D3_TOTP_REGISTERNEW"}]
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-4">
                                            [{oxmultilang ident="D3_TOTP_QRCODE" suffix="COLON"}]
                                        </div>
                                        <div class="col-8">
                                            [{$totp->getQrCodeElement()}]
                                            [{oxinputhelp ident="D3_TOTP_QRCODE_HELP"}]
                                        </div>
                                    </div>
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
                                    <div class="row">
                                        <div class="col-12">
                                            [{oxmultilang ident="D3_TOTP_REGISTERDELETE_DESC"}]
                                            <br>
                                            <br>
                                            <button type="submit" [{$readonly}] class="btn btn-primary btn-outline-danger btn-sm" onClick="document.myedit.fnc.value='delete'">
                                                [{oxmultilang ident="D3_TOTP_REGISTERDELETE"}]
                                            </button>
                                        </div>
                                    </div>
                                    <br>
                                </div>
                            [{/if}]
                        [{/block}]
                    </div>
                </div>
                <div class="col-8">
                    <div class="card">
                        [{block name="user_d3user_totp_form2"}]
                            [{if false == $totp->getId()}]
                                <div class="card-header">
                                    [{oxmultilang ident="D3_TOTP_CONFIRMATION"}]
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-4">
                                            <label for="secret">[{oxmultilang ident="D3_TOTP_SECRET" suffix="COLON"}]</label>
                                        </div>
                                        <div class="col-8">
                                            <textarea rows="3" cols="50" id="secret" name="secret" class="editinput" readonly="readonly">[{$totp->getSecret()}]</textarea>
                                            [{oxinputhelp ident="D3_TOTP_SECRET_HELP"}]
                                        </div>
                                    </div>
                                    <div class="row" style="margin-top: 20px;">
                                        <div class="col-4">
                                            <label for="otp">[{oxmultilang ident="D3_TOTP_CURROTP"}]</label>
                                        </div>
                                        <div class="col-8">
                                            <input type="text" class="editinput" size="6" maxlength="6" id="otp" name="otp" value="" autofocus="autofocus" [{$readonly}]>
                                            [{oxinputhelp ident="D3_TOTP_CURROTP_HELP"}]
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-4"></div>
                                        <div class="col-8">
                                            <button type="submit" [{$readonly}] class="btn btn-primary btn-success btn-sm" onClick="document.myedit.fnc.value='save'">
                                                [{oxmultilang ident="D3_TOTP_SAVE"}]
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            [{else}]
                                <div class="card-header">
                                    [{oxmultilang ident="D3_TOTP_BACKUPCODES"}]
                                </div>
                                <div class="card-body">
                                    [{if $oView->getBackupCodes()}]
                                        <div class="row">
                                            <div class="col-6">
                                                <label for="backupcodes">[{oxmultilang ident="D3_TOTP_BACKUPCODES_DESC"}]</label>
                                            </div>
                                            <div class="col-6">
                                                <textarea id="backupcodes" rows="10" cols="20">[{$oView->getBackupCodes()}]</textarea>
                                            </div>
                                        </div>
                                    [{else}]
                                        <div class="row">
                                            <div class="col-12">
                                                [{oxmultilang ident="D3_TOTP_AVAILBACKUPCODECOUNT" args=$oView->getAvailableBackupCodeCount()}]
                                                <br>
                                                <br>
                                                [{oxmultilang ident="D3_TOTP_AVAILBACKUPCODECOUNT_DESC"}]
                                            </div>
                                        </div>
                                    [{/if}]
                                </div>
                            [{/if}]
                        [{/block}]
                    </div>
                </div>
            </div>
        </div>
    [{/if}]
</form>

[{if !$force2FA}]
    [{include file="bottomnaviitem.tpl"}]
    [{include file="bottomitem.tpl"}]
[{/if}]
