<?php 

function parseFilename($filename) : array | bool {
    $pattern = '/([a-zA-Z]+)_([0-9]{2})([0-9]{4})_([0-9]+)/';
    if (preg_match($pattern, $filename, $matches)) {
        return [
            'bankName' => $matches[1],
            'year' => $matches[2],
            'month' => $matches[3],
            'accountNumber' => $matches[4],
        ];
    }
    
    return false;
}