
<?php

include('libs/helpers.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require 'vendor/autoload.php';

date_default_timezone_set("Africa/Lagos");


$disk_info = shell_exec('df -Th');
$blks =shell_exec('lsblk -a -o NAME,MOUNTPOINT');


$formatted = parseDfResponse($disk_info);
$formatted_blk_device =parseBKResponse($blks);




$all_server_disks =$formatted['filesystems'];


// foreach($all_server_disks as $key=>$server_disk){
//     $free_space = parse_free_space($server_disk['available']);

//     if($free_space <= 1024){
//         echo "warning email";
//     }
//     echo $free_space."<br/>";
// }
$date =  date('d M Y h:i:sa');
file_put_contents("datastore/disk_info.json", json_encode($formatted['filesystems']));

$mounted_block_devices = filter_block_devices($formatted_blk_device['blockdevices']);


$mounted_disk = filter_disk_drives($mounted_block_devices,$all_server_disks);
$table = format_email_body($mounted_disk);


$email_body = "
    <p>Disk Drives information as at $date</p>
    <table  width='100%' cellpadding='0' cellspacing='0' style='min-width:100%;'>
        
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

    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = 'smtp1.example.com';                    // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = 'user@example.com';                     // SMTP username
        $mail->Password   = 'secret';                               // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        $mail->Port       = 587;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
    
        //Recipients
        $mail->setFrom('from@example.com', 'Mailer');
        $mail->addAddress('joe@example.net', 'Joe User'); 
    
        // Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = "HDisk Drives information as at $date";
        $mail->Body    = $email_body;
    
        $mail->send();
        echo 'Message has been sent';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
    echo $email_body;




?>








