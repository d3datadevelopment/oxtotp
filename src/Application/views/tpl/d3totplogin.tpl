[{capture append="oxidBlock_content"}]
    [{assign var="template_title" value=""}]

    [{if $oView->previousClassIsOrderStep()}]
        [{* ordering steps *}]
        [{include file="page/checkout/inc/steps.tpl" active=2}]
    [{/if}]

    <div class="row">
        <div class="col-xs-12 col-sm-10 col-md-6 [{* flow *}] col-sm-offset-1 col-md-offset-3 [{* wave *}] offset-sm-1 offset-md-3 mainforms">
            <form action="[{$oViewConf->getSelfActionLink()}]" method="post" name="login" id="login">
                [{$oViewConf->getHiddenSid()}]

                <input type="hidden" name="fnc" value="d3TotpCheckTotpLogin">
                <input type="hidden" name="cl" value="[{$oView->getPreviousClass()}]">
                [{$navFormParams}]

                <h3>[{oxmultilang ident="D3_TOTP_INPUT"}]</h3>

                [{if !empty($Errors.default)}]
                    [{include file="inc_error.tpl" Errorlist=$Errors.default}]
                [{/if}]

                [{$oView->getBackupCodeCountMessage()}]

                <div class="container">
                    <label for="1st">erste TOTP-Ziffer</label>
                    <input type="text" name="d3totp[]" class="digit" id='1st' inputmode="numeric" pattern="[0-9]*" maxlength="1" onkeyup="clickEvent(null, '2nd')" autofocus autocomplete="off">
                    <label for="2nd">zweite TOTP-Ziffer</label>
                    <input type="text" name="d3totp[]" class="digit" id="2nd" inputmode="numeric" pattern="[0-9]*" maxlength="1" onkeyup="clickEvent('1st', '3rd')" autocomplete="off">
                    <label for="3rd">dritte TOTP-Ziffer</label>
                    <input type="text" name="d3totp[]" class="digit" id="3rd" inputmode="numeric" pattern="[0-9]*" maxlength="1" onkeyup="clickEvent('2nd', '4th')" autocomplete="off">
                    <label for="4th">vierte TOTP-Ziffer</label>
                    <input type="text" name="d3totp[]" class="digit" id="4th" inputmode="numeric" pattern="[0-9]*" maxlength="1" onkeyup="clickEvent('3rd', '5th')" autocomplete="off">
                    <label for="5th">fünfte TOTP-Ziffer</label>
                    <input type="text" name="d3totp[]" class="digit" id="5th" inputmode="numeric" pattern="[0-9]*" maxlength="1" onkeyup="clickEvent('4th', '6th')" autocomplete="off">
                    <label for="6th">sechste TOTP-Ziffer</label>
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

                <div>[{oxmultilang ident="D3_TOTP_INPUT_HELP"}]</div>

                <button type="submit" class="btn btn-primary">
                    [{oxmultilang ident="D3_TOTP_SUBMIT_LOGIN"}]
                </button><br>

            </form>
            <form action="[{$oViewConf->getSelfActionLink()}]" method="post" name="login" id="login">
                [{$oViewConf->getHiddenSid()}]

                <input type="hidden" name="fnc" value="d3TotpCancelTotpLogin">
                <input type="hidden" name="cl" value="[{$oView->getPreviousClass()}]">
                [{$navFormParams}]

                <button class="btn btn_cancel" type="submit">
                    [{oxmultilang ident="D3_TOTP_CANCEL_LOGIN"}]
                </button>

            </form>
        </div>
    </div>

    [{oxstyle include=$oViewConf->getModuleUrl('d3totp', 'out/flow/src/css/d3totplogin.css')}]
    [{oxstyle}]

    [{insert name="oxid_tracker" title=$template_title}]
[{/capture}]

[{include file="layout/page.tpl"}]