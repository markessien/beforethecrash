<?php

include('libs/helpers.php');
date_default_timezone_set("Africa/Lagos");
$disk_info = shell_exec('df -h');
$formatted = parseDfResponse($disk_info);
$server_disk =$formatted['filesystems']['/dev/nvme0n1p6'];
$all_server_disks =$formatted['filesystems'];
foreach($all_server_disks as $server_disk){
    $free_space = parse_free_space($server_disk['available']);
    if($free_space <= 1024){
        echo "warning email";
    }
    echo $free_space."<br/>";
}
$formatted['filesystems']['created_at'] =  date('d M Y h:i:sa');
file_put_contents("datastore/disk_info.json", json_encode($formatted['filesystems']));



print_r(getenv());







