<?php

if (!function_exists('t')) {
    function t(?string $value): ?string
    {
        if ($value === null || $value === '') return $value;
        return mb_convert_case(mb_strtolower($value), MB_CASE_TITLE, 'UTF-8');
    }
}

if (!function_exists('unaccent_string')) {
    function unaccent_string(?string $value): ?string
    {
        if ($value === null || $value === '') return $value;
        return strtr($value, [
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N',
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
        ]);
    }
}

if (!function_exists('accent_insensitive_search')) {
    function accent_insensitive_search(array $columns, string $term): \Closure
    {
        $norm = mb_strtolower(unaccent_string($term) ?? '', 'UTF-8');
        $useUnaccent = \DB::connection()->getDriverName() === 'sqlite';

        return function ($qry) use ($columns, $norm, $useUnaccent) {
            foreach ($columns as $column) {
                if ($useUnaccent) {
                    $qry->orWhereRaw("LOWER(unaccent({$column})) LIKE ?", ["%{$norm}%"]);
                } else {
                    $qry->orWhere($column, 'like', "%{$norm}%");
                }
            }
        };
    }
}
