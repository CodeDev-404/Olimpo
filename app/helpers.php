<?php

if (!function_exists('t')) {
    function t(?string $value): ?string
    {
        if ($value === null || $value === '') return $value;
        return mb_convert_case(mb_strtolower($value), MB_CASE_TITLE, 'UTF-8');
    }
}
