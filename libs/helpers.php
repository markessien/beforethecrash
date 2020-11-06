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
            $join['filesystems'][$further[0]]['1k_blocks'] = $further[1];
            $join['filesystems'][$further[0]]['used'] = $further[2];
            $join['filesystems'][$further[0]]['available'] = $further[3];
            $join['filesystems'][$further[0]]['usage_percentage'] = $further[4];
            $join['filesystems'][$further[0]]['mount_point'] = $further[5];


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
        $size = intval(substr($free_space,0,$str_length-2)) * 1024;
    }

    if($last_char == 'B'){
        $second_last = substr($free_space,$str_length-2,1);
        if($second_last == 'M'){
     
                $size = intval(substr($free_space,0,$str_length-3));
        }

        if($second_last == 'K'){
            $size = (intval(substr($free_space,0,$str_length-3)) / 1024) /1024;
        }
    }

    return $size;
}


function trigger_warning($free_space){
    if($free_space <= 1024){
        echo "warning email";
    }
}

?>