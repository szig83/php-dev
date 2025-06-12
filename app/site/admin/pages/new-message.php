<?php

use system\js_includer,
    site\user,
    system\security,
    trumbowyg\trumbowyg_angular

;

js_includer::js_include(TEMPLATE_JS_PATH_PUBLIC, "message/message-new.js");
$angular_controller_id = 'new_message';
?>
<div class="content-box">
<script>
    angular_message_new(
            '<?php echo AJAX_WRAPPER_ANGULAR; ?>',
            '<?php echo $angular_controller_id; ?>'
            );</script>
<div
    ng-controller="<?php echo $angular_controller_id; ?>"
    ng-init="ini('<?php echo security::token_generate_from_ajax_id('message_send'); ?>')"
    >
        <?php
        $form = new form("frm_new_message");
        $form->add_attribute("class", "form-horizontal");

        $title = new control\textbox("email_subject");
        $title->add_attribute("ng-model", "data.email_subject");
        $title->set_is_form_group(true);
        $title->set_has_feedback(true);
        $title->set_label("Tárgy", ":", "control-label col-sm-1");
        $title->set_required(true);
        $title->add_wrapper("div", array("class" => "col-sm-11"), "w1");

        $editor = new trumbowyg_angular("email_editor");
        $editor->set_toolbar_type(\wysiwyg_toolbar_type::standard);
        $editor->add_attribute("ng-model", "data.email_text");
        $editor->set_required(true);
        $editor->set_height(300);
        $editor->set_is_form_group(true);
        $editor->set_has_feedback(false);
        $editor->set_label("Üzenet", ":", "control-label col-sm-1");

        $editor->add_wrapper("div", array("class" => "col-sm-11"), "w1");

        $user_groups = new \control\combobox("user_group");
        $user_groups->add_attribute("ng-model", "data.user_group_id");
        $user_groups->add_attribute("convert-to-number");
        $user_groups->set_is_form_group(true);
        $user_groups->set_has_feedback(true);
        $user_groups->set_label("Csoport", ":", "control-label col-sm-1");
        $user_groups->set_required(true);
        $user_groups->add_wrapper("div", array("class" => "col-sm-11"), "w1");
        $user_groups->add_item("-1", "-");
        $user_groups->set_items_from_array(user::user_group_list(), false, 'USER_GROUP_ID', 'USER_GROUP_NAME');


        $button = new control\button("", "", "Küldés");
        $button->set_class("btn btn-success");
        $button->add_attribute("ng-click", "send()");
        $button->set_is_form_group(false);


        $form->add_content($title->write());
        $form->add_content($editor->write());
        $form->add_content($user_groups->write());
        $form->add_content("<div class='text-center'>" . $button->write() . "</div>");

        echo $form->write();
        ?>
</div>
</div>