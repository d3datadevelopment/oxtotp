[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign}]

[{if $readonly}]
    [{assign var="readonly" value="readonly disabled"}]
[{else}]
    [{assign var="readonly" value=""}]
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
    <input type="hidden" name="editval[d3totp__oxuserid]" value="[{$oxid}]">
    <table cellspacing="0" cellpadding="0" border="0" style="width:98%;">
        <tr>
            <td valign="top" class="edittext" style="padding-top:10px;padding-left:10px; width: 50%;">
                <table cellspacing="0" cellpadding="0" border="0">
                    [{block name="user_d3user_totp_form1"}]
                        <tr>
                            <td class="edittext" width="120">
                                [{oxmultilang ident="D3_TOTP_ACTIVE"}]
                            </td>
                            <td class="edittext">
                                <input type="hidden" name="editval[d3totp__usetoptp]" value="0">
                                <input class="edittext" type="checkbox" name="editval[d3totp__usetotp]" value='1' [{if $edit->getFieldData('usetotp') == 1}]checked[{/if}] [{$readonly}]>
                                [{oxinputhelp ident="D3_TOTP_ACTIVE_HELP"}]
                            </td>
                        </tr>

                    [{/block}]

                    <tr>
                        <td class="edittext" colspan="2"><br><br>
                            <input type="submit" class="edittext" id="oLockButton" name="saveArticle" value="[{oxmultilang ident="ARTICLE_MAIN_SAVE"}]" onClick="Javascript:document.myedit.fnc.value='save'" [{if !$edit->oxarticles__oxtitle->value && !$oxparentid}]disabled[{/if}] [{$readonly}]>
                            [{if $oxid!=-1 && !$readonly}]
                                <input type="submit" class="edittext" name="save" value="[{oxmultilang ident="ARTICLE_MAIN_ARTCOPY"}]" onClick="Javascript:document.myedit.fnc.value='copyArticle';" [{$readonly}]>&nbsp;&nbsp;&nbsp;
                            [{/if}]
                        </td>
                    </tr>
                </table>
            </td>
            <!-- Anfang rechte Seite -->
            <td valign="top" class="edittext" align="left" style="height:99%;padding-left:5px;padding-bottom:30px;padding-top:10px; width: 50%;">
                <table cellspacing="0" cellpadding="0" border="0">
                    [{block name="user_d3user_totp_form2"}]

                        <tr>
                            <td class="edittext">
                                [{oxmultilang ident="D3_TOTP_QRCODE"}]&nbsp;
                            </td>
                            <td class="edittext">
                                <img src="[{$edit->getQrCodeUri()}]">
                                [{*
                                                                <input type="text" class="editinput" size="32" maxlength="[{$edit->oxarticles__oxtitle->fldmax_length}]" id="oLockTarget" name="editval[oxarticles__oxtitle]" value="[{$edit->oxarticles__oxtitle->value}]">
                                                                [{oxinputhelp ident="HELP_ARTICLE_MAIN_TITLE"}]
                                *}]
                            </td>
                        </tr>
                        <tr>
                            <td class="edittext">
                                [{oxmultilang ident="D3_TOTP_SECRET"}]&nbsp;
                            </td>
                            <td class="edittext">
                                [{$edit->getSecret()}]
                                [{*
                                                                <input type="text" class="editinput" size="32" maxlength="[{$edit->oxarticles__oxartnum->fldmax_length}]" name="editval[oxarticles__oxartnum]" value="[{$edit->oxarticles__oxartnum->value}]" [{$readonly}]>
                                                                [{oxinputhelp ident="HELP_ARTICLE_MAIN_ARTNUM"}]
                                *}]
                            </td>
                        </tr>

                        <tr>
                            <td class="edittext">
                                [{oxmultilang ident="D3_TOTP_CURROTP"}]&nbsp;
                            </td>
                            <td class="edittext">
                                <input type="text" class="editinput" size="6" maxlength="6" name="otp" value="">
                                [{oxinputhelp ident="D3_TOTP_CURROTP_HELP"}]
                            </td>
                        </tr>
                    [{/block}]
                </table>
            </td>
            <!-- Ende rechte Seite -->
        </tr>
    </table>
</form>

[{include file="bottomnaviitem.tpl"}]
[{include file="bottomitem.tpl"}]