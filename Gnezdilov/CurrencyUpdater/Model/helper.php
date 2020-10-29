<?php 

function dd($d)
{
    print_r($d);
    die();
}

function d($d)
{
    print_r($d);
}

/** check if header of url has 404 responde in all headers */
function hasResponde200($url)
{
    $file_headers = @get_headers($url);
    //info($file_headers);
    $exists = false;

    if ($file_headers) {
        foreach (array_reverse($file_headers) as $header) {
            if ($header == 'HTTP/1.1 200' || $header == 'HTTP/1.1 200 200' || $header == 'HTTP/1.1 200 OK') {
                $exists = true;
            }
        }
    }
    return $exists;
}

