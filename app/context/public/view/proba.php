<?php
fileWrite('page');
echo $this->config->get('app.name');

$_SESSION['page'] = 1;