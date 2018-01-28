<div id="mcps_popup" class="mcps-popup">    
    <div class="popup-inner">
        <span class="popup-close">&times;</span>
        <div class="popup-content">
            {assign 'title' $config.title[$id_language] }
            {assign 'body' $config.body[$id_language] }
            {assign 'link' $config.link[$id_language] }
            {if $title|count_characters}
                <p class="popup-title">{$title|strip_tags}</p>
            {/if}
            {if $body|count_characters}
                <div class="popup-body">{$body|unescape}</div>
            {/if}
            {if $config.displayReturnToSiteBtn === true}
                <span class="btn btn-success popup-close">{l s="Return to site" mod="mcps_popup"}</span>
            {/if}
        </div>
    </div>
</div>

{if $config.useModuleCoreJs === true}
    {literal}
        <script>
            window.onload = function () {
                var popup = document.getElementById('mcps_popup');
                popup.style.display = "block";

                popup.onclick = function () {
                    popup.style.display = "none";
                };

                var popupClose = document.getElementsByClassName("popup-close")[0];

                popupClose.onclick = function () {
                    popup.style.display = "none";
                };
            };
        </script>
    {/literal}
{/if}