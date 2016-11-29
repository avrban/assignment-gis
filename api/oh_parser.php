<?php
/*metoda pre nacitavanie otvaracich hodin podla nazvu institucie a datumu z otvaracie-hodiny.sk*/
function get_openinghours($name, $date)
{
    error_reporting(E_ALL & ~E_NOTICE);
    require_once("parser.php");
    $name_url = str_replace(" ", "+", $name);
    $weekday = date("N", $date);

    $html = file_get_html('https://www.otvaracie-hodiny.sk/engine/search/?selang=&match=all&string=' . $name_url . '');
    if (isset($html)) {
        $element = $html->find('div[class="row search-result-row"]', 0);
        if (isset($element)) {
            $link = $element->find('a', 0);

            if (isset($link)) {
                $href = "https://www.otvaracie-hodiny.sk" . $link->href;
            }
        }
    }

    if (empty($href)) return "";

    $html = file_get_html($href);
    if (isset($html)) {
        $element = $html->find('div[class="product-detail"]', 0);
        if (isset($element)) {
            $tabulka = $element->find('table', 0);
            if (isset($tabulka)) {
                $riadok = $tabulka->find('tr', $weekday - 1);
                if (isset($riadok)) {
                    $hodiny = $riadok->find('td', 1)->plaintext;
                    return $hodiny;
                }

            }
        }
    }

    return "";
}