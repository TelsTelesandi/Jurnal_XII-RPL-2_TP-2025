<?php

namespace App\Helpers;

class StringHelper
{
    protected static array $badWords = [
        // Indonesia
        'anjing','anjg','anjirr','anjir','anjay','anjayyy','bangsat','bajingan','badjingan','kontol','kntl','k0nt0l','k0ntol','kont0l','memek','mmk','m3m3k','peler','plr','toket','ngentot','ngewe','pepek','jembut','jancuk','jncuk','kentut','tolol','goblok','gblk','bodo','bego','idiot','kampret','asu','brengsek','babi','monyet','kunyuk','tai','taik','telek','lonte','lont3','perek','sundel','jablay','pelacur','lacur','pantek','puki','pukimak','kimak','setan','iblis','dajjal','keparat','ngehe','fuckboy','fuckgirl',
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
            $regex = '';
            $length = mb_strlen($w);

            // GANTI: Menggunakan perulangan & percabangan untuk filter cerdas (Leetspeak & Singkatan Vokal)
            for ($i = 0; $i < $length; $i++) {
                $char = mb_strtolower(mb_substr($w, $i, 1));

                if ($char === 'a') {
                    $regex .= '[aA4@]+';
                } elseif ($char === 'i') {
                    $regex .= '[iI1!]+';
                } elseif ($char === 'u') {
                    $regex .= '[uUvV]+';
                } elseif ($char === 'e') {
                    $regex .= '[eE3]+';
                } elseif ($char === 'o') {
                    $regex .= '[oO0]+';
                } elseif ($char === 's') {
                    $regex .= '[sS5$]+';
                } elseif ($char === 'g') {
                    $regex .= '[gG9]+';
                } elseif ($char === 'b') {
                    $regex .= '[bB8]+';
                } elseif ($char === 't') {
                    $regex .= '[tT7]+';
                } elseif ($char === ' ') {
                    $regex .= '[\s\-\_]+';
                } else {
                    $regex .= '[' . preg_quote($char, '~') . strtoupper($char) . ']+';
                }
            }

            // Boundary
            $patterns[] = "~\\b{$regex}\\b~iu";
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

        // Pastikan HTML tag tidak ikut tersensor (misalnya <s> jangan berubah jadi <*>)
        return preg_replace_callback(
            '/(>|^)([^<]+)/u',
            function ($matches) use ($patterns, $replacer) {
                $content = $matches[2];
                foreach ($patterns as $pat) {
                    $content = preg_replace_callback($pat, $replacer, $content);
                }
                return $matches[1] . $content;
            },
            $text
        );
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
