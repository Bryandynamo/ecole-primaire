<?php
// Fonction stricte de cotation officielle
if (!function_exists('getAppreciation')) {
    function getAppreciation($valeur, $total_max) {
        if ($total_max == 20) {
            if ($valeur <= 9) return 'C';
            if ($valeur <= 14) return 'B';
            if ($valeur <= 17) return 'A';
            return 'A+';
        }
        if ($total_max == 30) {
            if ($valeur <= 14) return 'C';
            if ($valeur <= 20) return 'B';
            if ($valeur <= 26) return 'A';
            return 'A+';
        }
        if ($total_max == 40) {
            if ($valeur <= 19) return 'C';
            if ($valeur <= 29) return 'B';
            if ($valeur <= 35) return 'A';
            return 'A+';
        }
        return '';
    }
}
