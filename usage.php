<?php

/* RETRIEVE CURRENTLY LOGGED IN USERS */
function get_current_users() {
    $logged_in_users = shell_exec('who');
    $split_users = explode("\n", $logged_in_users);

    $get_files = array_slice(scandir("datastore/usage_files/"), 2);
    
    if ( count($get_files) ) {
        $last_file = trim($get_files[count($get_files) - 1]);
        $date_string = trim(explode('_', explode('.', $last_file)[0])[1]);
        if ( strtotime($date_string) < time() ) {
            $from = date('d-m-y');
            $to = date('d-m-y', strtotime('+ 7 days'));
            $path = "datastore/usage_files/usage ~ ${from} _ ${to}.php";
        } else {
            $from = date('d-m-y');
            $to = date('d-m-y', strtotime('+ 7 days'));
            $path = "datastore/usage_files/${last_file}";
        }
    } else {
        $from = date('d-m-y');
        $to = date('d-m-y', strtotime('+ 7 days'));
        $path = "datastore/usage_files/usage ~ ${from} _ ${to}.php";
    }

    foreach ($split_users as $user) {
        if ( !empty(trim($user)) ) {
            file_put_contents($path, "\n" . trim($user), FILE_APPEND);
        }
    }
}

/* FIND TOP TEN LARGEST FILES */
function find_large_files() {
    $large_files = shell_exec("find ./ -size +100M -ls");
    print_r($large_files);
}

get_current_users();
find_large_files();