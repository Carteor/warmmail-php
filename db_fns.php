<?php

function db_connect()
{
    $result = new mysqli('localhost', 'mail', 'password', 'mail');
    if (!$result) {
        echo "Can\'t connect to database";
    } else {
        return $result;
    }
}

?>