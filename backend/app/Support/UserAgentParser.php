<?php

namespace App\Support;

class UserAgentParser
{
    public static function parse(?string $userAgent): array
    {
        $userAgent ??= '';

        return [
            'browser' => self::browser($userAgent),
            'os' => self::os($userAgent),
            'device' => self::device($userAgent),
        ];
    }

    public static function browser(string $userAgent): string
    {
        $patterns = [
            'Edg' => 'Microsoft Edge',
            'Chrome' => 'Chrome',
            'Safari' => 'Safari',
            'Firefox' => 'Firefox',
            'Opera' => 'Opera',
            'MSIE' => 'Internet Explorer',
            'Trident' => 'Internet Explorer',
        ];

        foreach ($patterns as $key => $name) {
            if (str_contains($userAgent, $key)) {
                return $name;
            }
        }

        return 'Unknown';
    }

    public static function os(string $userAgent): string
    {
        $patterns = [
            'Windows NT 10.0' => 'Windows 10/11',
            'Windows NT 6.3' => 'Windows 8.1',
            'Windows NT 6.2' => 'Windows 8',
            'Windows NT 6.1' => 'Windows 7',
            'Macintosh' => 'macOS',
            'Mac OS X' => 'macOS',
            'Linux' => 'Linux',
            'Android' => 'Android',
            'iPhone' => 'iOS',
            'iPad' => 'iOS',
        ];

        foreach ($patterns as $key => $name) {
            if (str_contains($userAgent, $key)) {
                return $name;
            }
        }

        return 'Unknown';
    }

    public static function device(string $userAgent): string
    {
        if (str_contains($userAgent, 'Mobile')) {
            return 'Mobile';
        }

        if (str_contains($userAgent, 'Tablet') || str_contains($userAgent, 'iPad')) {
            return 'Tablet';
        }

        return 'Desktop';
    }
}
