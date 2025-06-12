<?php

use layout\template\template_content_place as tcp;

?>
<header class="header">
    <?php
    $this->write_module_to_tcp(tcp::header_pre, 'header__pre');
    $this->write_module_to_tcp(tcp::header, 'header__main');
    $this->write_module_to_tcp(tcp::header_post, 'header__post');
    ?>
</header>
