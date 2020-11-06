<?php

include('libs/helpers.php');

$disk_info = shell_exec('df -h');
$formatted = parseDfResponse($disk_info);
$server_disk =$formatted['filesystems']['/dev/nvme0n1p6'];
$free_space = parse_free_space($server_disk['available']);
print_r($free_space);












