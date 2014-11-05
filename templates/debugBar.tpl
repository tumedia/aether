<h2 id="aetherDebugBarButton" style="position: fixed; right: 0; top: 0; width: 20px; height: 20px; padding: 2px 5px; z-index: 10001; background: red; opacity: 0; color: white; margin: 0; cursor: pointer; font-size: 12px; font-weight: bold" title="Click to open">&lt;</h2>
<div id="aetherDebugBar" class="nimbus" style="text-align: left; position: fixed; width: 240px; right: -240px; top: 0; bottom: 0; overflow-y: auto; background: rgba(0,0,0,0.85); z-index: 10000; transition: right 1s; font-size: 8pt; padding: 10px 2px 4px; color: #fff; display: none">
    <div style="padding: 2px 12px 40px 12px;">
        <ul style="list-style-type: none; padding: 0px;">
        {foreach from=$timers key=name item=timer}
            <li style="width:100%; clear: both; padding-top: 15px; font-size: 9pt; letter-spacing: 1px; text-transform: uppercase; font-weight: bold; border-bottom: 1px solid #847864">{$name}:</li>
            <li>
                <ul style="list-style-type: none; padding: 0px;">
                {foreach from=$timer key=point item=data}
                    {if $point != "start"}
                    <li style="clear:left; padding: 3px 0; float: left; width: 100%;">
                        <span style="font-weight: bold; float:left; overflow:hidden;">{$point}</span>
                        <span style="float:right;{if $point != 'total'}{if $data.elapsed > 0.15}font-weight: bold;{/if}color:{if $data.elapsed > 0.05}red{else if $data.elapsed > 0.01}yellow{else}green{/if}{/if}">{round($data.elapsed*1000,1)}ms ({round($data.memory/(1024*1024), 1)} MB)</span>
                    </li>
                    {/if}
                {/foreach}
                </ul>
            </li>
        {/foreach}
        </ul>
    </div>
</div>
<script type="text/javascript">
function load() {
    // Make div openable by click
    var opener = document.getElementById("aetherDebugBarButton");
    var debugBox = opener.nextSibling.nextSibling;
    opener.addEventListener("click", aetherDebugPanelToggle, false);
    opener.addEventListener("mouseover", function (evt) {
        this.style.opacity = 1;
    }, false);
    if (!!localStorage.getItem("aetherDebugBar")) {
        var display = localStorage.getItem("aetherDebugBar");
        if (display == 'block') {
            debugBox.style.right = 0;
        }
        else {
            debugBox.style.right = "-240px";
        }
        debugBox.style.display = display;
    }
}
function aetherDebugPanelToggle(event) {
    var debugBox = event.target.nextSibling.nextSibling;
    if (debugBox.style.display == "none") {
        debugBox.style.display = 'block';
        setTimeout(function() {
            debugBox.style.right = "0px";
        }, 0);
        localStorage.setItem("aetherDebugBar", 'block');
    }
    else {
        debugBox.style.right = "-240px";
        setTimeout(function() {
            debugBox.style.display = 'none';
        }, 2000);
        this.style.opacity = 0;
        localStorage.setItem("aetherDebugBar", 'none');
    }
}
load();
</script>
