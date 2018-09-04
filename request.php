<?php

require 'CloudOnex.php';

$api = new CloudOnex(['base_url' => "https://www.google.com/recaptcha/api/"]);

// print_r($_SERVER['REMOTE_ADDR']);

$response = $api->post('siteverify',[
    'secret' => '6Ld3XG4UAAAAAKG3EagEivFyT_5B0QtutVJLvBcr', # Customer Full Name
    'response' => $_REQUEST['g-recaptcha-response'],
    'remoteip' => $_SERVER['REMOTE_ADDR'],
])->response();

print_r($_REQUEST);
echo "<hr/>";
var_dump(json_decode($response));
