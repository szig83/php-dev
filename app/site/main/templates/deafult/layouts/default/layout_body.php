<?php

use system\angularjs,
    layout\animation\animation_type,
    system\js_includer,
    system\plugins,
    layout\style;

js_includer::js_include_plugin_to_container(plugins::get_source("plugins/angularjs/angular-utf8-base64.min.js"), false);
js_includer::js_include_plugin_to_container(plugins::get_source("plugins/angularjs/recaptcha/angular-recaptcha.min.js"), false);
js_includer::js_include_plugin_to_container(plugins::get_source("plugins/angularjs/checklist-model.js"), false);

$angular = new angularjs();
$angular->add_module('ab-base64');
$angular->add_module('checklist-model');
$angular->add_module('vcRecaptcha');


$google = new google($google_ids = array(
    "analytics" => $this->get_site_details()['GOOGLE_ID'] ?? '',
    "tag_manager" => $this->get_site_details()['GOOGLE_TAG_MANAGER_ID'] ?? '')
);

$custom_class_name = style::add_custom_template_font($this->template);
?>
<body
        ng-app="<?php echo $angular->get_web_id(); ?>"
        class="animated <?php echo animation_type::fadeIn; ?><?php echo' '.($custom_class_name ?? ''); ?>"
        style="<?php echo $this->get_page_html_body_style(); ?>"
>
<?php

if ($this->is_load_enter_page()) {
    require_once('components/enterpage.php');
};

$angular->write();
echo $google->get_sources();
echo '<script>var AJAX_WRAPPER = "' . AJAX_WRAPPER . '"; var BASE_DIR = "' . BASE_DIR . '"; </script>';

echo '<div class="layout-wrapper container">';
echo $this->get_alternative_content_warning_source();
require_once('components/header.php'); #Fejlec
require_once('components/main.php'); #Tartalom
require_once('components/footer.php'); #Lablec
echo '</div>';

?>
<div id="upload_wrapper"></div>
<div id="modal_wrapper"></div>
<?php
echo cookie::get_cookie_info_ajax_source();
system\jquery::add_document_ready_script('setTimeout(function() {cookie(1);}, 1000);');
new \system\debug();
echo $this->get_hidden_message_to_template();
?>
</body>
