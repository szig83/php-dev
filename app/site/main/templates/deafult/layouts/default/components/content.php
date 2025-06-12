<?php

use layout\template\template_content_place as tcp;

if ($this->check_content_place(tcp::content)) {
    $tcpp_content = $this->get_content_place_param(tcp::content);
    ?>
    <section id="tcp_<?php echo tcp::content; ?>"
             class="app__main--content <?php echo $tcpp_content['class']; ?> <?php echo $tcpp_content['class_cols']; ?>"
             style="<?php echo $tcpp_content['style']; ?>"
    >

        <article class="app__main--content-inside">
            <?php
            if (
                !boolval($this->get_content_details()['IS_PROTECTED']) ||
                (boolval($this->get_content_details()['IS_PROTECTED']) && site\login::is_login())
            ) {

                /*$extra_tcp_params = array(
                    "wrapper_start" => "<div class='container-fluid'><div class='row'>",
                    "wrapper_stop" => "</div></div>"
                );*/
                $extra_tcp_params = [];


                $this->write_module_to_tcp(tcp::page_title_pre, 'app-page-title-pre', true, $extra_tcp_params);
                if ($this->get_content_details()['IS_TITLE_SHOW']) {
                    ?>
                    <h<?php echo $this->get_content_details()['TITLE_H_TAG'] ?? 1; ?>
                            class="app__main--content-title "><?php
                        echo '<span class="title-text">'.$this->get_page_title().'</span>';
                        if ($this->get_content_details()['SEO_LINK'] !== HOME_PAGE) {
                            ?>
                            <span class="button-back pointer"
                                  title="<?php echo language::get_text("buttons[0]->back_to_prev_page"); ?>"
                                  data-tooltip="tooltip" data-placement="left" onclick="go_back()"><i
                                        class="fa fa-chevron-circle-left" aria-hidden="true"></i></span>
                        <?php } ?>
                    </h<?php echo $this->get_content_details()['TITLE_H_TAG'] ?? 1; ?>>
                    <?php
                }
                $this->write_module_to_tcp(tcp::content_pre, 'app-content-pre', false, $extra_tcp_params);
                ?>
                <div class="container-fluid">
                    <div class="row flex-column">
                        <?php
                        $this->write_module_to_tcp(tcp::page_content_pre, 'app-page-content-pre', true, $extra_tcp_params);
                        echo $this->write_content_inside();
                        $this->write_module_to_tcp(tcp::page_content_post, 'app-page-content-post', true, $extra_tcp_params);
                        ?>
                    </div>
                </div>
                <?php
                $this->write_module_to_tcp(tcp::content_post, 'app-content-post', false, $extra_tcp_params);
            } else {
                $info = new layout\info(layout\info\info_type::warning);
                $info->set_content("<b>Bejelentkezés szükséges!</b>"
                    . "<br /><br />"
                    . "Ahhoz, hogy le tudja tölteni az adott dokumentumokat be kell jelentkeznie a kapott felhasználónév és "
                    . "jelszó adatokkal.<br />"
                    . "Ezeket az információkat társaságunk adja ki tagjainak.<br /><br />");
                echo $info->write();
            }
            ?>
        </article>
    </section>
    <?php
}