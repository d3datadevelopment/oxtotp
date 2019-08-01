[{$smarty.block.parent}]
<li class="list-group-item[{if $active_link == "downloads"}] active[{/if}]">
    <a href="[{oxgetseourl ident=$oViewConf->getSelfLink()|cat:"cl=d3_account_totp"}]" title="[{oxmultilang ident="D3_TOTP_ACCOUNT"}]">[{oxmultilang ident="D3_TOTP_ACCOUNT"}]</a>
</li>