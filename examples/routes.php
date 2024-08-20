<?php
switch($data->getMethod()){
    case 'get':
        #$router->addRule('/example/:id', array('controller' => 'example', 'action' => 'get_by_id'));
        break;
    case 'post':
        break;
    case 'delete':
        break;
    case 'put':
        break;
}

$router->init();
