<?php

use system\js_includer as js_includer,
    layout\style,
    system\plugins,
    system\debug,
    system\jquery,
    system\session_manager

;

$id = $this->get_site_details()['SITE_IDENTIFICATION_CODE'] ?? '';
$language = session_manager::get_session_data('site_language_long', true, 'hu-HU');
$install_date = $this->get_site_details()['SITE_INSTALL_DATE'] ?? '2016-01-01';


$title = $this->get_html_title();
$description = $this->get_html_description();
$extra_meta_tags = $this->get_html_extra_meta_tags();

#$page_description = $this->get_content_data('TEXT_DESCRIPTION') ?? '';
#$site_description = $this->get_site_details()['TEXT_DESCRIPTION'] ?? '';
#$description = !empty($page_description) ? $page_description : $site_description;
?>
<head>
    <?php echo $this->get_favicons(); ?>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
    <meta http-equiv="Content-Language" content="<?php echo $language; ?>" />
    <meta name="description" content="<?php echo $description; ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=yes" />
    <title><?php echo $title; ?></title>

    <!-- Dublin core -->
    <link rel="schema.dcterms" href="http://purl.org/dc/terms/">
    <meta name="DC.identifier" content="<?php echo $id; ?>" />
    <meta name="DC.type" content="website" />
    <meta name="DC.format" content="text/html" />
    <meta name="DC.language" content="<?php echo $language; ?>" />
    <meta name="DC.date" content="<?php echo system::get_date_format($install_date); ?>" />
    <meta name="DC.title" content="<?php echo $title; ?>" />
    <meta name="DC.description" content="<?php echo $description; ?>" />

    <!-- Open Graph Protocol -->
    <meta property="og:url" content="<?php echo ($this->get_page_param()!=='404'? $this->get_current_url() : $this->get_current_url(false).'/404'); ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:locale" content="<?php echo $language; ?>" />
    <meta property="og:site_name" content="<?php echo $this->get_site_title(); ?>" />
    <meta property="og:title" content="<?php echo $title; ?>" />
    <meta property="og:description" content="<?php echo $description; ?>" />

    <?php
    if (!empty($extra_meta_tags)) {
        echo implode(" ",$extra_meta_tags);
    }

    $bootstrap = new \layout\bootstrap(BOOTSTRAP_VERSION);

    $bootstrap->load_css();
    style::css_includer(TEMPLATE_CSS_PATH_PUBLIC . 'style.min.css');
    style::css_includer(plugins::get_source('plugins/animate/css/animate.min.css'));
    style::css_includer(plugins::get_source('plugins/font-awesome/5.10.2/css/all.min.css'));

    style::css_includer(plugins::get_source('plugins/totop/css/ui.totop.css'));
    style::css_includer(plugins::get_source('plugins/toast/jquery.toast.min.css'));

    if (isset(style::$css_includer_array['styles'])) {
        foreach (style::$css_includer_array['styles'] as $css_value) {
            echo '<link href="' . $css_value['css'] . '" rel="stylesheet" type="text/css" ' . $css_value['extra'] . ' />';
        }
    }

    #Generalt css class-ok betoltese
    if (!empty(style::$css_class_array)) {
        echo '<style>';
        foreach (style::$css_class_array as $css_class_value) {
            echo $css_class_value;
        }
        echo '</style>';
    }

    js_includer::js_include_plugin(plugins::get_source("plugins/jquery/v3/jquery-3.3.1.min.js"));
    js_includer::js_include_plugin(plugins::get_source("plugins/angularjs/v1.7.9/angular.min.js"));
    js_includer::js_include_plugin(plugins::get_source("plugins/angularjs/v1.7.9/angular-sanitize.min.js"));
    js_includer::js_include_plugin(plugins::get_source("plugins/angularjs/v1.7.9/angular-cookies.min.js"));
    js_includer::js_include_plugin(plugins::get_source("plugins/angularjs/ui_bootstrap/ui-bootstrap-tpls-2.5.0.min.js"));
    js_includer::js_include_plugin(plugins::get_source("plugins/jquery/jquery.easing.1.3.js"));

    $bootstrap->load_js();
    js_includer::js_include_plugin_to_container(plugins::get_source("plugins/animate/janimate.min.js"));
    js_includer::js_include_plugin_to_container(plugins::get_source("plugins/totop/js/jquery.ui.totop.js"));
    js_includer::js_include_plugin_to_container(plugins::get_source("plugins/dotdotdot/jquery.dotdotdot.min.js"));
    js_includer::js_include_plugin_to_container(plugins::get_source("plugins/toast/jquery.toast.min.js"));

    js_includer::js_include_to_container(TEMPLATE_JS_PATH_PUBLIC, "system/waitforimages.min.js");
    js_includer::js_include_to_container(TEMPLATE_JS_PATH_PUBLIC, "system/functions.min.js");
    js_includer::js_include_to_container(TEMPLATE_JS_PATH_PUBLIC, "system/events.min.js");
    js_includer::js_include_to_container(TEMPLATE_JS_PATH_PUBLIC, "layout/bootstrap-modal.min.js", false);

    echo js_includer::compile_container_js(CONTAINER_JS_COMPILER_OVERWRITE);


    if (isset(js_includer::$js_includer_array['group_path'])) {
        foreach (js_includer::$js_includer_array['group_path'] as $js_value) {
                echo '<script type="text/javascript" src="' . $js_value['path'] . '" ' . $js_value['extra'] . '></script>';
        }
    }

    jquery::add_document_ready_script('$().UItoTop({easingType: "easeOutQuart"});');
    jquery::add_document_ready_script('bar_fixer("floating-menu");');

    echo jquery::document_ready_script(jquery::get_document_ready_script());
    echo debug::debug_client_generated_time(); #Kliens generalasi ido merese debug modban
    ?>
</head>
