<div id="mcps_popup" class="mcps-popup">
    <div class="popup-inner">
        <span class="popup-close">&times;</span>
        <div class="popup-content">
            {if $config.debugMode === true}
                <div class="mcps-debug">
                    <pre>
                        {$body_classes|@print_r}
                    </pre>
                </div>
            {/if}
            {if isset($config.title) and isset($config.title[$id_language]) and $config.title[$id_language]|count_characters}
                <p class="popup-title">{$config.title[$id_language]|strip_tags}</p>
            {/if}
            {if isset($config.body) and isset($config.body[$id_language]) and $config.body[$id_language]|count_characters}
                <div class="popup-body">{$config.body[$id_language] nofilter}</div>
            {/if}
            {if $config.displayReturnToSiteBtn === true}
                <span class="btn btn-success popup-close">{l s='Return to site' mod='mcps_popup'}</span>
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