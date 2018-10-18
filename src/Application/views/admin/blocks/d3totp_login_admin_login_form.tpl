[{if $request_totp}]
    <input autocomplete="false" name="hidden" type="text" style="display:none;">
    [{$oViewConf->getHiddenSid()}]

    <input type="hidden" name="fnc" value="checklogin">
    <input type="hidden" name="cl" value="login">

    [{if $Errors.default|@count}]
    [{include file="inc_error.tpl" Errorlist=$Errors.default}]
    [{/if}]

    <label for="d3totp">[{oxmultilang ident="TOTP_INPUT"}]</label>
    <input type="text" name="d3totp" id="d3totp" value="" size="49" autofocus><br>

    [{oxmultilang ident="TOTP_INPUT_HELP"}]
[{else}]
    [{$smarty.block.parent}]
[{/if}]