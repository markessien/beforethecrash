<?php

/* RETRIEVE CURRENTLY LOGGED IN USERS */
function get_current_users() {
    $logged_in_users = shell_exec('who');
    print_r($logged_in_users);
}

get_current_users();