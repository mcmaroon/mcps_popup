<div id="mcps_popup">
    {if $config.title|count_characters}
        <p class="h1">{$config.title|strip_tags}</p>
    {/if}
    {if $config.body|count_characters}
        <div>{$config.body|unescape}</div>
    {/if}
</div>