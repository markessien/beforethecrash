
<?php

include('libs/helpers.php');
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$threshold = $_ENV['SMTP_MAILTO'];
$smtp_creds = [
    'username' => $_ENV['SMTP_USER'],
    'password' => $_ENV['SMTP_PASSWORD'],
    'server' => $_ENV['SMTP_SERVER'],
    'mailfrom' => $_ENV['SMTP_MAILFROM'],
    'mailto' => $_ENV['SMTP_MAILTO'],
];
// echo $_ENV['SMTP_MAILTO'];

    date_default_timezone_set("Africa/Lagos");


    $disk_info = shell_exec('df -Th');
    $blks =shell_exec('lsblk -a -o NAME,MOUNTPOINT');


    $formatted = parseDfResponse($disk_info);
    $formatted_blk_device =parseBKResponse($blks);




    $all_server_disks =$formatted['filesystems'];





    $mounted_block_devices = filter_block_devices($formatted_blk_device['blockdevices']);


    $mounted_disk = filter_disk_drives($mounted_block_devices,$all_server_disks);
    check_disk_space($mounted_disk,$smtp_creds,$threshold);




    




?>








