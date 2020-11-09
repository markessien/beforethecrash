<?php

function parseDfResponse($data) {

    # First of all, we trim the data to remove any un-needed spaces at the start and end of the contents.
    $data = trim($data);

    # We break the data on the basis of newlines. Using this, we can obtain every row.
    $data = explode(PHP_EOL, $data);

    # We loop through every row now.

    foreach ($data as $value) {

        # We're replacing the spaces into a special character (§) for splitting in the next process.
        $d = str_replace(" ", "§", $value);

        # We're using a regex expression to remove all duplicated special characters (§).
        $d = preg_replace("/(§)\\1+/", "$1", $d);

        # We're filling the newly created content in an array.
        $step[] = $d;

    }

    $key = 0;

    # Looping through the array we created now.

    foreach ($step as $value) {

        # We don't need to include the header row which includes the field names.

        if ($key !== 0) {

            # Spiting with the special character and filling out the values in a new array.

            $further = explode("§", $value);
            $join['filesystems'][$further[0]]['type'] = $further[1];
            $join['filesystems'][$further[0]]['1k_blocks'] = $further[2];
            $join['filesystems'][$further[0]]['used'] = $further[3];
            $join['filesystems'][$further[0]]['available'] = $further[4];
            $join['filesystems'][$further[0]]['usage_percentage'] = $further[5];
            $join['filesystems'][$further[0]]['mount_point'] = $further[6];


        }

        # Giving increments.

        $key++;

    }

    return $join;

}


function parseBKResponse($data) {

    # First of all, we trim the data to remove any un-needed spaces at the start and end of the contents.
    $data = trim($data);

    # We break the data on the basis of newlines. Using this, we can obtain every row.
    $data = explode(PHP_EOL, $data);

    # We loop through every row now.

    foreach ($data as $value) {

        # We're replacing the spaces into a special character (§) for splitting in the next process.
        $d = str_replace(" ", "§", $value);
       
        # We're using a regex expression to remove all duplicated special characters (§).
        $d = preg_replace("/(§)\\1+/", "$1", $d);

        # We're filling the newly created content in an array.
        $step[] = $d;

    }

    $key = 0;

    # Looping through the array we created now.

    foreach ($step as $value) {

        # We don't need to include the header row which includes the field names.

        if ($key !== 0) {

            # Spiting with the special character and filling out the values in a new array.

            $further = explode("§", $value);
            $join['blockdevices'][$further[0]]['name'] = $further[0];
            $join['blockdevices'][$further[0]]['mountpoint'] = $further[1];


        }

        # Giving increments.

        $key++;

    }

    return $join;

}


function parse_free_space($free_space){
    $str_length = strlen($free_space);
    $last_char = substr($free_space,$str_length -1,1);

    if($last_char =='G'){
        $size = intval(substr($free_space,0,$str_length-1)) * 1024;
    }

    if($last_char == 'B'){
        $second_last = substr($free_space,$str_length-2,1);
        if($second_last == 'M'){
     
                $size = intval(substr($free_space,0,$str_length-3));
        }

        if($second_last == 'K'){
            $size = (intval(substr($free_space,0,$str_length-2)) / 1024) /1024;
        }
    }

    return $size;
}


function trigger_warning($free_space){
    if($free_space <= 1024){
        echo "warning email";
    }
}

function format_email_body($all_server_disk){


    $table='';


    foreach($all_server_disk as $key=>$server_disk){
        get_disk_name($key);
        $free_space = parse_free_space($server_disk['available']);
      
        $table_tr = '';
        $disk_name = "<td valign='top' style='padding:5px; font-family: Arial,sans-serif; font-size: 16px; line-height:20px;'>$key</td>";
        foreach($server_disk as $item){
            $table_tr .="<td valign='top' style='padding:5px; font-family: Arial,sans-serif; font-size: 16px; line-height:20px;'>$item</td>";
        }
        $free_space = parse_free_space($server_disk['available']);

        if($free_space >= 1024){
            $status = "<td valign='top' style='padding:5px; font-family: Arial,sans-serif; font-size: 16px; line-height:20px;'>Good</td>";
        }else{
            $status ="<td valign='top' style='padding:5px; font-family: Arial,sans-serif; font-size: 16px; line-height:20px;'>Bad</td>";
        }
        $table .= "<tr>
        $disk_name
        $table_tr
        $status
        </tr>";
        
    }

    return $table;

}


function filter_block_devices($block_devices){

    $mounted_block_devices = array();

    foreach($block_devices as $key=>$block_device){
        if($block_device['mountpoint'] != ''){
            $new_name = str_replace('|-','',$key);
            $mounted_block_devices[$new_name] = $block_device;
            // array_push($mounted_block_devices,$block_device);
        }
    }
    return $mounted_block_devices;

}


function filter_disk_drives($block_devices,$disk_drives){
    $filtered = array();
    foreach($disk_drives as $key=>$disk_drive){
        $name = trim(get_disk_name($key));
        if(array_key_exists($name,$block_devices) && $disk_drive['type'] != "squashfs"){

            $filtered[$name] = $disk_drive;
        }
    }
    return $filtered;
}


function get_disk_name($name){
    $arr = explode('/',$name);
    $diskname =$arr[count($arr)-1];

    return $diskname;
}

function check_disks($all_disks){
    foreach($all_disks as $server_disk){

    }
}

?>