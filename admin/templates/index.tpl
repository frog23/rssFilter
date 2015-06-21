{extends file="page.tpl"}

{block name="body"}
    <h2>public feeds</h2>
    <ul>
    {foreach $feeds as $feed}
        {if $feed["publicOrPrivate"] eq "public"}
            <li>
            {if $feed["hash"]}<a href="{$base_url}/index.php?hash={$feed["hash"]}">{$feed["feed"]}</a>{else}
                <a href="{$base_url}/index.php?id={$feed["ID"]}">{$feed["feed"]}</a>
            {/if}
            </li>
        {/if}
    {/foreach}
    </ul>
{/block}