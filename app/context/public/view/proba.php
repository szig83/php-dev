<?php

use App\Core\Database;

#fileWrite('page');
echo $this->config->get('app.name');


var_dump($this->database);

#$stmt =$this->database->prepare("SELECT * FROM table_name");
#$stmt->execute(['id' => 1]);
#$result = $stmt->fetchAll();
#print_r($result);


#var_dump($this->config->get('database'));
