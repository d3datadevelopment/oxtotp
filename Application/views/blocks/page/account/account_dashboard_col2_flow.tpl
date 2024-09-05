[{$smarty.block.parent}]

<div class="panel panel-default">
    <div class="panel-heading">
        <a href="[{oxgetseourl ident=$oViewConf->getSelfLink()|cat:"cl=d3_account_totp"}]">[{oxmultilang ident="D3_TOTP_ACCOUNT"}]</a>
        <a href="[{oxgetseourl ident=$oViewConf->getSslSelfLink()|cat:"cl=d3_account_totp"}]" class="btn btn-default btn-xs pull-right">
            <i class="fa fa-arrow-right"></i>
        </a>
    </div>
    <div class="panel-body">[{oxmultilang ident="D3_TOTP_ACCOUNT_DESC"}]</div>
</div>