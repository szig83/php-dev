<?php

use system\js_includer as js_includer;
use system\plugins;

js_includer::js_include_plugin(plugins::get_source("plugins/jquery/jquery.cookie.js"), true);
?>
<div id="enterpage">
    <div class="text-center container">
        <?php
        $enter_content = $this->get_site_details()['TEXT_ENTER_PAGE'];
        if (!empty($enter_content)) {
            echo html_entity_decode($enter_content);
        }
        ?>
        <button class="btn btn-success" id="btn_enter">Belépek</button>
    </div>
</div>
<script>
    $(function () {
        $("#btn_enter").on("click", function () {
            $.cookie("ENTER_ACCEPTED", 1, {expires: 0.25});
            navigateUrl("<?php echo system::get_current_url(true); ?>");
        });
    });

</script>

