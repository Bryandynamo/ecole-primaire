<?php
if (!function_exists('cote')) {
    function cote($note)
    {
        if (!is_numeric($note)) return '';
        if ($note >= 18) return 'A+';
        if ($note >= 16) return 'A';
        if ($note >= 14) return 'B+';
        if ($note >= 12) return 'B';
        if ($note >= 10) return 'C';
        return 'D';
    }
}
