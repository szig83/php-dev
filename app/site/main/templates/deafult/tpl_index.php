
<?php
require_once 'ini/config.php'; #Template konfiguralo file-janak betoltese.
$this->get_content_inside();
?>
<!DOCTYPE html>
<html lang="<?php echo \system\session_manager::get_session_data('site_language'); ?>">
    <?php $this->write_application(); ?>
</html>