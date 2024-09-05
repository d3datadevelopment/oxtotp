[{capture append="oxidBlock_content"}]

    <h1 class="page-header">[{oxmultilang ident="D3_TOTP_ACCOUNT"}]</h1>

    [{assign var="totp" value=$user->d3GetTotp()}]
    [{assign var="userid" value=$user->getId()}]
    [{$totp->loadByUserId($userid)}]

    <style>
        .registerNew {
            display: none;
        }
        dt, dd {
            width: 50%;
            float: left;
            margin-bottom: 10px;
        }
        dd textarea {
            max-width: 98%;
        }
        dt label {
            font-weight: bold;
        }
        [{if false == $totp->getId()}]
            .submitBtn {
                display: none;
            }
        [{/if}]
    </style>

    [{block name="d3_account_totp"}]
        <form action="[{$oViewConf->getSelfActionLink()}]" name="d3totpform" class="form-horizontal" method="post">
            <div class="hidden">
                [{$oViewConf->getHiddenSid()}]
                [{$oViewConf->getNavFormParams()}]
                <input type="hidden" id="fncname" name="fnc" value="">
                <input type="hidden" name="cl" value="[{$oViewConf->getActiveClassName()}]">
            </div>

            <p>
                <input id="totp_use" value="1" type="checkbox" name="totp_use" [{if $totp->getId()}] checked[{/if}] [{if false == $totp->getId()}]onclick="$('.registerNew').toggle(); $('.submitBtn').toggle();"[{/if}]>
                <label for="totp_use">[{oxmultilang ident="D3_TOTP_ACCOUNT_USE"}]</label>
            </p>

            [{if false == $totp->getId()}]
                <div class="registerNew [{* flow *}] panel panel-default [{* wave *}] card">
                    <div class="[{* flow *}] panel-heading [{* wave *}] card-header">
                        [{oxmultilang ident="D3_TOTP_REGISTERNEW"}]
                    </div>
                    <div class="[{* flow *}] panel-body [{* wave *}] card-body">
                        <dl>
                            <dt>
                                [{oxmultilang ident="D3_TOTP_QRCODE"}]&nbsp;
                            </dt>
                            <dd>
                                [{$totp->getQrCodeElement()}]
                            </dd>
                        </dl>
                        <p>
                            [{oxmultilang ident="D3_TOTP_QRCODE_HELP"}]
                        </p>

                        <hr>

                        <dl>
                            <dt>
                                <label for="secret">[{oxmultilang ident="D3_TOTP_SECRET"}]</label>
                            </dt>
                            <dd>
                                <textarea rows="3" cols="50" id="secret" name="secret" class="editinput" readonly="readonly">[{$totp->getSecret()}]</textarea>
                            </dd>
                        </dl>
                        <p>
                            [{oxmultilang ident="D3_TOTP_SECRET_HELP"}]
                        </p>

                        <hr>

                        <dl>
                            <dt>
                                <label for="otp">[{oxmultilang ident="D3_TOTP_CURROTP"}]</label>
                            </dt>
                            <dd>
                                <input type="text" class="editinput" size="6" maxlength="6" id="otp" name="otp" value="" [{$readonly}]>
                            </dd>
                        </dl>
                        <p>
                            [{oxmultilang ident="D3_TOTP_CURROTP_HELP"}]
                        </p>
                    </div>
                </div>
            [{/if}]

            [{if $totp->getId()}]
                [{block name="d3_account_totp_deletenotes"}]
                    <div class="[{* flow *}] panel panel-default [{* wave *}] card">
                        <div class="[{* flow *}] panel-heading [{* wave *}] card-header">
                            [{oxmultilang ident="D3_TOTP_REGISTEREXIST"}]
                        </div>
                        <div class="[{* flow *}] panel-body [{* wave *}] card-body">
                            [{oxmultilang ident="D3_TOTP_REGISTERDELETE_DESC"}]
                        </div>
                    </div>
                [{/block}]

                [{block name="d3_account_totp_backupcodes"}]
                    <div class="[{* flow *}] panel panel-default [{* wave *}] card">
                        <div class="[{* flow *}] panel-heading [{* wave *}] card-header">
                            [{oxmultilang ident="D3_TOTP_BACKUPCODES"}]
                        </div>
                        <div class="[{* flow *}] panel-body [{* wave *}] card-body">
                            [{if $oView->getBackupCodes()}]
                                [{block name="d3_account_totp_backupcodes_list"}]
                                    <label for="backupcodes">[{oxmultilang ident="D3_TOTP_BACKUPCODES_DESC"}]</label>
                                    <textarea id="backupcodes" rows="10" cols="20">[{$oView->getBackupCodes()}]</textarea>
                                [{/block}]
                            [{else}]
                                [{block name="d3_account_totp_backupcodes_info"}]
                                    [{oxmultilang ident="D3_TOTP_AVAILBACKUPCODECOUNT" args=$oView->getAvailableBackupCodeCount()}]<br>
                                    [{oxmultilang ident="D3_TOTP_AVAILBACKUPCODECOUNT_DESC"}]
                                [{/block}]
                            [{/if}]
                        </div>
                    </div>
                [{/block}]
            [{/if}]

            <p class="submitBtn">
                <button type="submit" class="btn btn-primary"
                    [{if $totp->getId()}]
                        onclick="
                            if(false === document.getElementById('totp_use').checked && false === confirm('[{oxmultilang ident="D3_TOTP_REGISTERDELETE_CONFIRM"}]')) {return false;}
                            document.getElementById('fncname').value = 'delete';
                        "
                    [{else}]
                        onclick="document.getElementById('fncname').value = 'create';"
                    [{/if}]
                >
                    [{oxmultilang ident="D3_TOTP_ACCOUNT_SAVE"}]
                </button>
            </p>
        </form>
    [{/block}]
[{/capture}]

[{capture append="oxidBlock_sidebar"}]
    [{include file="page/account/inc/account_menu.tpl" active_link="d3totp"}]
[{/capture}]
[{include file="layout/page.tpl" sidebar="Left"}]