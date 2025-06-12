<?php

define('TEMPLATE_VERSION', '2.1'); #Template verzio
define('TEMPLATE_CSS_PATH_PUBLIC', $this->get_template_path_client(true) . '/css/'); #Template css stilus konyvtara.
define('TEMPLATE_CSS_PATH_PUBLIC_SERVER', str_replace('//', '/', DOC_ROOT . TEMPLATE_CSS_PATH_PUBLIC)); #Template css stilus konyvtara szerver szinten.
define('TEMPLATE_JS_PATH_PUBLIC', $this->get_template_path_client(true) . '/scripts/'); #Template egyedi scriptjeinek konyvtara.
define('TEMPLATE_IMAGE_PATH_PUBLIC', $this->get_template_path_client(true) . '/images/'); #Publikus template-en beluli kepek linkelesehez szukseges eleresi ut.

define('TEMPLATE_BUTTON_PANEL', 'button_panel'); #Gomb panel azonosito
define('TEMPLATE_MODAL_WRAPPER', 'modal_wrapper'); #Modal wrapper azonosito
define('LANGUAGE_TEXT', SYSTEM_LANGUAGES . $this->get_site_language_code() . '.xml'); #rendszer nyelv file

/**
 * Bootstrap foverzio
 */
define('BOOTSTRAP_VERSION',4);

define('GZIP_ENABLED', true);
define('DEFAULT_404', 1);
define('RANDOM_404', false);
