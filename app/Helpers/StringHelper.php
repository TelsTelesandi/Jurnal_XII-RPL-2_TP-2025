<?php

namespace App\Helpers;

class StringHelper
{
    protected static array $badWords = [
        // Indonesia
        'anjing','anjg','anjirr','anjir','anjay','anjayyy','bangsat','bajingan','kontol','memek','peler','toket','ngentot','ngewe','pepek','jembut','jancuk','kentut','tolol','goblok','idiot','kampret','asu','brengsek','fuckboy','fuckgirl',
        // English
        'fuck','fucker','fucking','motherfucker','mf','shit','bullshit','bitch','asshole','dick','dickhead','pussy','cunt','bastard','slut','whore','prick','jerk','moron','retard','stupid','idiot','damn','screw','screwed'
    ];

    protected static ?array $compiledPatterns = null;

    protected static function compilePatterns(): array
    {
        if (self::$compiledPatterns !== null) return self::$compiledPatterns;

        $words = array_unique(array_filter(array_map('trim', self::$badWords)));
        // Allow extra words via config
        try {
            $extra = config('app.profanity_extra');
            if (is_array($extra)) {
                foreach ($extra as $w) {
                    $w = trim((string)$w);
                    if ($w !== '') $words[] = $w;
                }
                $words = array_unique($words);
            }
        } catch (\Throwable $e) {
            // ignore
        }

        $patterns = [];
        foreach ($words as $w) {
            $escaped = preg_quote($w, '~');
            // Support spaces or hyphens in phrases by matching any whitespace
            $escaped = str_replace(['\\ ', '\\-'], ['\\s+', '(?:\\s|-)'], $escaped);
            $patterns[] = "~\\b{$escaped}\\b~iu";
        }

        return self::$compiledPatterns = $patterns;
    }

    protected static function maskMatch(string $text): string
    {
        $out = '';
        $len = mb_strlen($text);
        for ($i = 0; $i < $len; $i++) {
            $ch = mb_substr($text, $i, 1);
            $out .= preg_match('~[\p{L}\p{N}]~u', $ch) ? '*' : $ch;
        }
        return $out;
    }

    public static function censorProfanity(?string $text): ?string
    {
        if ($text === null || $text === '') return $text;
        $patterns = self::compilePatterns();
        $replacer = function(array $m) { return self::maskMatch($m[0]); };
        foreach ($patterns as $pat) {
            $text = preg_replace_callback($pat, $replacer, $text);
        }
        return $text;
    }

    public static function containsProfanity(?string $text): bool
    {
        if ($text === null || $text === '') return false;
        foreach (self::compilePatterns() as $pat) {
            if (preg_match($pat, $text)) return true;
        }
        return false;
    }
    public static function maskName($name)
    {
        if (!$name)
            return 'Anonim';
        $length = mb_strlen($name);

        // Jika nama terlalu pendek, tampilkan saja "*"
        if ($length <= 2) {
            return mb_substr($name, 0, 1) . str_repeat('*', $length - 1);
        }

        // Ambil huruf pertama & terakhir, tengah disensor
        $first = mb_substr($name, 0, 1);
        $last = mb_substr($name, -1, 1);
        $stars = str_repeat('*', $length - 2);

        return $first . $stars . $last;
    }

    public static function maskEmail($email)
    {
        if (!$email)
            return '-';
        [$user, $domain] = explode('@', $email);

        $maskedUser = substr($user, 0, 1) . str_repeat('*', max(strlen($user) - 2, 1)) . substr($user, -1);

        return $maskedUser . '@' . $domain;
    }
}
