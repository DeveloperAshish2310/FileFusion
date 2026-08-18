<?php

if (!function_exists('getInitials')) {
    function getInitials($fullName, $limit = null)
    {
        $words = explode(' ', trim($fullName));
        if ($limit) {
            $words = array_slice($words, 0, $limit);
        }
        return strtoupper(implode('', array_map(fn($w) => $w[0], $words)));
    }
}


if (!function_exists('magicstring')) {
    function magicstring($arr)
    {
        echo "<pre>";
        print_r($arr);
        echo "</pre>";
    }
}
if (!function_exists('BytetoSize')) {
    function BytetoSize($bytes, $precision = 2, $decimalSeparator = '.', $thousandsSeparator = ',')
    {
        if (!is_numeric($bytes) || $bytes < 0) return '0 B';

        $units = [
            'TB' => pow(1024, 4),
            'GB' => pow(1024, 3),
            'MB' => pow(1024, 2),
            'KB' => 1024,
            'B'  => 1,
        ];

        foreach ($units as $unit => $value) {
            if ($bytes >= $value) {
                $result = $bytes / $value;
                return number_format($result, $precision, $decimalSeparator, $thousandsSeparator) . ' ' . $unit;
            }
        }

        return '0 B';
    }
}

if (!function_exists('getFileIcon')) {
    function getFileIcon($extension)
    {
        $icons = [
            // Documents
            'pdf' => ['icon' => 'file-text', 'color' => 'text-red-500 bg-red-50'],
            'doc' => ['icon' => 'file-text', 'color' => 'text-blue-500 bg-blue-50'],
            'docx' => ['icon' => 'file-text', 'color' => 'text-blue-500 bg-blue-50'],
            'txt' => ['icon' => 'file-text', 'color' => 'text-slate-500 bg-slate-50'],

            // Images
            'jpg' => ['icon' => 'image', 'color' => 'text-purple-500 bg-purple-50'],
            'jpeg' => ['icon' => 'image', 'color' => 'text-purple-500 bg-purple-50'],
            'png' => ['icon' => 'image', 'color' => 'text-purple-500 bg-purple-50'],
            'gif' => ['icon' => 'image', 'color' => 'text-purple-500 bg-purple-50'],
            'svg' => ['icon' => 'image', 'color' => 'text-purple-500 bg-purple-50'],
            'webp' => ['icon' => 'image', 'color' => 'text-purple-500 bg-purple-50'],

            // Videos
            'mp4' => ['icon' => 'video', 'color' => 'text-pink-500 bg-pink-50'],
            'avi' => ['icon' => 'video', 'color' => 'text-pink-500 bg-pink-50'],
            'mov' => ['icon' => 'video', 'color' => 'text-pink-500 bg-pink-50'],
            'mkv' => ['icon' => 'video', 'color' => 'text-pink-500 bg-pink-50'],

            // Audio
            'mp3' => ['icon' => 'music', 'color' => 'text-green-500 bg-green-50'],
            'wav' => ['icon' => 'music', 'color' => 'text-green-500 bg-green-50'],
            'ogg' => ['icon' => 'music', 'color' => 'text-green-500 bg-green-50'],

            // Archives
            'zip' => ['icon' => 'archive', 'color' => 'text-yellow-500 bg-yellow-50'],
            'rar' => ['icon' => 'archive', 'color' => 'text-yellow-500 bg-yellow-50'],
            '7z' => ['icon' => 'archive', 'color' => 'text-yellow-500 bg-yellow-50'],

            // Code
            'html' => ['icon' => 'code', 'color' => 'text-orange-500 bg-orange-50'],
            'css' => ['icon' => 'code', 'color' => 'text-orange-500 bg-orange-50'],
            'js' => ['icon' => 'code', 'color' => 'text-orange-500 bg-orange-50'],
            'php' => ['icon' => 'code', 'color' => 'text-indigo-500 bg-indigo-50'],
            'py' => ['icon' => 'code', 'color' => 'text-indigo-500 bg-indigo-50'],
        ];

        $ext = strtolower($extension);
        return $icons[$ext] ?? ['icon' => 'file', 'color' => 'text-slate-500 bg-slate-50'];
    }
}

if (!function_exists('timeAgo')) {
    function timeAgo($datetime)
    {
        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;

        if ($diff < 60) {
            return 'Just now';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 172800) {
            return 'Yesterday';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } else {
            return date('M d, Y', $timestamp);
        }
    }
}


