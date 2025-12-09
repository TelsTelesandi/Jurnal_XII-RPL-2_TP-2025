<?php

use Illuminate\Support\Facades\DB;

if (!function_exists('setting')) {
    function setting($key, $default = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                DB::table('settings')->updateOrInsert(
                    ['key' => $k],
                    ['value' => $v]
                );
            }
            return true;
        }

        $row = DB::table('settings')->where('key', $key)->first();
        return $row->value ?? $default;
    }
}
