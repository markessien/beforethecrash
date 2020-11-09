<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require 'vendor/autoload.php';



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

function check_disk_space($all_server_disk,$creds,$threshold){


$get_files = array_slice(scandir("datastore/disk_info/"), 2);

$date =  date('d M Y h:i:sa');

    $table='';
    $low_space_disk = false;

    foreach($all_server_disk as $key=>$server_disk){
        get_disk_name($key);
        $free_space = parse_free_space($server_disk['available']);
      
        $table_tr = '';
        $disk_name = "<td valign='top' style='padding:5px; font-family: Arial,sans-serif; font-size: 16px; line-height:20px;'>$key</td>";
        foreach($server_disk as $item){
            $table_tr .="<td valign='top' style='padding:5px; font-family: Arial,sans-serif; font-size: 16px; line-height:20px;'>$item</td>";
        }
        $free_space = parse_free_space($server_disk['available']);

        if($free_space >= $threshold){
            $status = "<td valign='top' style='padding:5px; font-family: Arial,sans-serif; font-size: 16px; line-height:20px;'>Good</td>";
        }else{
            //trigger warning
            $low_space_disk = true;
            $warning_table = format_warning_mail($server_disk,$disk_name);
            $status ="<td valign='top' style='padding:5px; font-family: Arial,sans-serif; font-size: 16px; line-height:20px;'>Bad</td>";
        }
        $table .= "<tr>
        $disk_name
        $table_tr
        $status
        </tr>";
    }

    if ( count($get_files) ) {
        $last_file = trim($get_files[count($get_files) - 1]);
        $filename =  explode('.', $last_file)[0];
        $date_string = explode('_',$filename)[2];
        if ( strtotime($date_string) < time() ) { //if last saved file is up to 7 days, create new file path and sends week mail
      
            $from = date('d-m-Y');
            $to = date('d-m-Y', strtotime('+ 7 days'));
            $path = "datastore/disk_info/info_${from}_${to}.json";

            $email_body = format_email_body($date,$table);
            sendmail($email_body,$date,$creds);

        } else {
            $path = "datastore/disk_info/${last_file}";
        }
    } else {

        $from = date('d-m-Y');
        $to = date('d-m-Y', strtotime('+ 7 days'));
        $email_body = format_email_body($date,$table);
        sendmail($email_body,$date,$creds);
        $path = "datastore/disk_info/info_${from}_${to}.json";
    }

 
    file_put_contents($path, json_encode($all_server_disk));


  

    if($low_space_disk){
        $warning_body = format_email_body($date,$warning_table);
        sendmail($warning_body,$date,$creds);
    }

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



function sendmail($body,$date,$creds){

    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = $creds['server'];                    // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = $creds['username'];                     // SMTP username
        $mail->Password   = $creds['password'];                               // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        $mail->Port       = 465;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
    
        //Recipients
        $mail->setFrom($creds['mailfrom']);
        $mail->addAddress($creds['mailto']); 
    
        // Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = "HDisk Drives information as at $date";
        $mail->Body    = $body;
    
        $mail->send();
        echo 'Message has been sent';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}


function format_warning_mail($disk,$disk_name){

    $table_td = '';
    foreach($disk as $item){
        $table_td .="<td valign='top' style='padding:5px; font-family: Arial,sans-serif; font-size: 16px; line-height:20px;'>$item</td>";
    }

    $table_td .= "<td valign='top' style='padding:5px; font-family: Arial,sans-serif; font-size: 16px; line-height:20px;'>Bad</td>";
    $table_tr = "<tr>
    $disk_name
    $table_td
    </tr>";

    return $table_tr;
}

function format_email_body($date,$table){

 $body=   "
        <p>Disk Drives information as at $date</p>
        <table border='1'  width='100%' cellpadding='0' cellspacing='0' style='min-width:100%; border-collapse: collapse;'>
            
        <thead>
            <th scope='col' style='padding:5px; font-family: Arial,sans-serif; font-size: 16px; line-height:20px;line-height:30px'>Disk Name</td>
            <th scope='col' style='padding:5px; font-family: Arial,sans-serif; font-size: 16px; line-height:20px;line-height:30px'>Type</td>
            <th scope='col' style='padding:5px; font-family: Arial,sans-serif; font-size: 16px; line-height:20px;line-height:30px'>Total Space</td>
            <th scope='col' style='padding:5px; font-family: Arial,sans-serif; font-size: 16px; line-height:20px;line-height:30px'>Used Space</td>
            <th scope='col' style='padding:5px; font-family: Arial,sans-serif; font-size: 16px; line-height:20px;line-height:30px'>Free Space</td>
            <th scope='col' style='padding:5px; font-family: Arial,sans-serif; font-size: 16px; line-height:20px;line-height:30px'>Percentage Used Space</td>
            <th scope='col' style='padding:5px; font-family: Arial,sans-serif; font-size: 16px; line-height:20px;line-height:30px'>Mount Point</td>
            <th scope='col' style='padding:5px; font-family: Arial,sans-serif; font-size: 16px; line-height:20px;line-height:30px'>Status</td>
        </thead>
        <tbody>
        
            $table
        
        </tbody>
        </table>

    ";  
    return $body ;
}

?>