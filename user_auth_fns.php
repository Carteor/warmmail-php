<?php

function check_auth_user() {
    if (isset($_SESSION['auth_user'])) {
        return true;
    } else {
        return false;
    }
}


function login($username, $passwd) {
    $conn = db_connect();
    $query = "SELECT * FROM users
              WHERE username='".$username."'
              AND password = '".$passwd."'";
    $result = $conn->query($query);
    if (!$result) {
        return false;
    } else {
        return true;
    }
}