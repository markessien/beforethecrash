<?php

/* RETRIEVE CURRENTLY LOGGED IN USERS */
function get_current_users() {
    $logged_in_users = shell_exec('who');
    $split_users = explode("\n", $logged_in_users);
    
    $from = date('d-m-y');
    $to = date('d-m-y', strtotime('+ 7 days'));
    $path = "datastore/usage ~ ${from} _ ${to}.php";

    $get_files = array_slice(scandir("datastore/"), 2);
    
    if ( count($get_files) ) {
        $last_file = $get_files[count($get_files) - 1];
        
        print_r($last_file);
    }

    // print_r($get_files);

    // foreach ($split_users as $user) {
    //     if ( !empty(trim($user)) ) {
    //         file_put_contents($path, "\n" . trim($user), FILE_APPEND);
    //     }
    // }
}

get_current_users();