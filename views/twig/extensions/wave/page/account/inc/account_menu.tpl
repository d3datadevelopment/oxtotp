[{$smarty.block.parent}]
<li class="list-group-item[{if $active_link == "d3totp"}] active[{/if}]">
    <a class="[{* wave *}] list-group-link" href="[{oxgetseourl ident=$oViewConf->getSelfLink()|cat:"cl=d3_account_totp"}]" title="[{oxmultilang ident="D3_TOTP_ACCOUNT"}]">[{oxmultilang ident="D3_TOTP_ACCOUNT"}]</a>
</li>