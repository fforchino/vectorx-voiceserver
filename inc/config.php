<?php
define("RESEMBLE_AI_API_TOKEN", "YOUR_API_TOKEN_HERE");
define("FAKEYOU_USERNAME", "YOUR_EMAIL_HERE");
define("FAKEYOU_PASSWORD", "YOUR_PASSWORD_HERE");

function guidv4($data)
{
    assert(strlen($data) == 16);

    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}
?>