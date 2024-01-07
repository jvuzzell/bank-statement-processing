<?php

function parseFilename($filename) : array | bool {
    $pattern = '/^([a-zA-Z]+)-([a-zA-Z]+)_([a-zA-Z0-9]+)_(\d{2})(\d{4})_(\d{4})(?:-(\d{4}))?(?:-(\d{4}))?/';  
    preg_match($pattern, $filename, $matches);

    if (preg_match($pattern, $filename, $matches)) {
        $result = [ 
            'firstName'      => $matches[1],
            'lastName'       => $matches[2],
            'bankName'       => $matches[3],
            'month'          => $matches[4],
            'year'           => $matches[5],
            'accountNumbers' => [$matches[6]]
        ];

        // Check if additional account numbers are present and add them to the result
        if (!empty($matches[7])) {
            $result['accountNumbers'][] = $matches[7];
        }
        if (!empty($matches[8])) {
            $result['accountNumbers'][] = $matches[8];
        }

        return $result;
    }

    return false;
}