<?php

use layout\template\template_content_place as tcp
    ;


if ($this->check_content_place(tcp::side_left)) {
    $tcpp_content = $this->get_content_place_param(tcp::side_left, true);
    ?>
    <section id="tcp_<?php echo tcp::side_left; ?>"
             class="app__side--left <?php echo $tcpp_content['class']; ?> <?php echo $tcpp_content['class_cols']; ?>"
             style="<?php echo $tcpp_content['style']; ?>"
    >
        <?php

        #$this->write_module_to_tcp(tcp::side_left, 'app__side--left');
        /**
         * Ide johet barmi kodreszlet, ami megjelenik a panelban.
         */

        $this->write_module_to_tcp(tcp::side_left_post, 'app__side--left-post');
        ?>
    </section>
    <?php
}