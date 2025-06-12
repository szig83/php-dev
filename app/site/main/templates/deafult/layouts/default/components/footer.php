<?php

use layout\template\template_content_place as tcp;

?>
<footer class="footer">
    <?php
    $this->write_module_to_tcp(tcp::footer_pre, 'footer__pre');
    $this->write_module_to_tcp(tcp::footer, 'footer__main');
    $this->write_module_to_tcp(tcp::footer_post, 'footer__post');
    ?>
</footer>