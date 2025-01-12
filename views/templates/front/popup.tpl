<div id="mcps_popup" class="mcps-popup">
    <div class="popup-inner">
        <span class="popup-close">&times;</span>
        <div class="popup-content">
            {if $config.debugMode === true}
                <style>
                    .mcps-debug {
                        font-size: 11px;
                        width: 100%;
                    }
                    .mcps-debug caption {
                        caption-side: top;
                        text-align: center;
                    }
                    .mcps-debug td {
                        padding: 2px 5px;
                    }
                </style>
                <div class="mcps-debug">
                    <table id="mcps_debug_table" class="table table-hover table-bordered">
                        <caption>{l s='Debug information' mod='mcps_popup'}</caption>
                        <tr>
                            <th>{l s='Key' mod='mcps_popup'} </th>
                            <th>{l s='Body class value' mod='mcps_popup'} </th>
                            <th>{l s='Match' mod='mcps_popup'} </th>
                        </tr>
                        {foreach from=$body_classes key=bck item=bcv}
                            <tr {if $bck === $matchIndex}style="font-weight: bold;"{/if}>
                                <td>{$bck|escape:'htmlall':'UTF-8'}</td>
                                <td>{$bcv|escape:'htmlall':'UTF-8'}</td>
                                <td>{if $bck === $matchIndex}true{else}false{/if}</td>
                            </tr>
                        {/foreach}
                    </table>
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