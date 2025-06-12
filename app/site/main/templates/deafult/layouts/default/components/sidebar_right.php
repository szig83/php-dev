<?php

use layout\template\template_content_place as tcp;


if ($this->check_content_place(tcp::side_right)) {
    $tcpp_content = $this->get_content_place_param(tcp::side_right, true);
    ?>
    <section id="tcp_<?php echo tcp::side_right; ?>"
             class="app__side--right <?php echo $tcpp_content['class']; ?> <?php echo $tcpp_content['class_cols']; ?>"
             style="<?php echo $tcpp_content['style']; ?>"
    >
        <?php

        $this->write_module_to_tcp(tcp::side_right_pre, 'app__side--right-pre');
        #$this->write_module_to_tcp(tcp::side_right, 'app__side--right');
        /**
         * Ide johet barmi kodreszlet, ami megjelenik a panelban.
         */

        #echo '<div class="ws__filter--wrapper"></div>';
        $this->write_module_to_tcp(tcp::side_right_post, 'app__side--right-post');
        ?>
    </section>
    <?php
}