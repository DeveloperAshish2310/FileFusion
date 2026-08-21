<div>
    <!-- Nothing in life is to be feared, it is only to be understood. Now is the time to understand more, so that we may fear less. - Marie Curie -->

    @php
        $groups = [
            'terminal_files' => ['bat', 'sh','zip'],
            'executable_app_files' => ['exe', 'msi',],
            'program_files' => ['js', 'pyc', 'py', 'php', 'html'],
            'image_files' => ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'],
            'archives_file' => ['zip', 'rar', '7z', 'tar', 'gz'],
            'presentation' => ['ppt', 'pptx'],
            'documents' => ['pdf', 'doc', 'docx', 'txt', 'rtf'],
            'exel_files' => ['xls', 'xlsx', 'csv'],
            'audio_files' => ['mp3', 'wav', 'aac', 'flac'],
            'video_files' => ['mp4', 'mkv', 'mpeg', 'm3u8'],
        ];

        $icons = [
            'terminal_files' => '<svg class="lucide lucide-file-terminal text-slate-950 lucide-file-terminal-icon"fill="none"height="24"stroke="currentColor"stroke-linecap="round"stroke-linejoin="round"stroke-width="2"viewBox="0 0 24 24"width="24"xmlns="http://www.w3.org/2000/svg"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="m8 16 2-2-2-2"/><path d="M12 18h4"/></svg>',

            'executable_app_files' => '<svg class="lucide  lucide-app-window lucide-app-window-icon"fill="none"height="24"stroke="currentColor"stroke-linecap="round"stroke-linejoin="round"stroke-width="2"viewBox="0 0 24 24"width="24"xmlns="http://www.w3.org/2000/svg"><rect height="16"rx="2"width="20"x="2"y="4"/><path d="M10 4v4"/><path d="M2 8h20"/><path d="M6 4v4"/></svg>',
            'program_files' =>
                '<svg class="lucide lucide-code text-gray-600"fill="none"height="24"stroke="currentColor"stroke-linecap="round"stroke-linejoin="round"stroke-width="2"viewBox="0 0 24 24"width="24"xmlns="http://www.w3.org/2000/svg"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>',
            'image_files' =>
                '<svg class="lucide lucide-image text-purple-500"fill="none"height="24"stroke="currentColor"stroke-linecap="round"stroke-linejoin="round"stroke-width="2"viewBox="0 0 24 24"width="24"xmlns="http://www.w3.org/2000/svg"><rect height="18"rx="2"ry="2"width="18"x="3"y="3"></rect><circle cx="9"cy="9"r="2"></circle><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path></svg>',
            'archives_file' =>
                '<svg class="lucide lucide-archive text-yellow-500"fill="none"height="24"stroke="currentColor"stroke-linecap="round"stroke-linejoin="round"stroke-width="2"viewBox="0 0 24 24"width="24"xmlns="http://www.w3.org/2000/svg"><rect height="5"rx="1"width="20"x="2"y="3"></rect><path d="M4 8v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8"></path><path d="M10 12h4"></path></svg>',
            'presentation' =>
                '<svg class="lucide lucide-presentation text-orange-500"fill="none"height="24"stroke="currentColor"stroke-linecap="round"stroke-linejoin="round"stroke-width="2"viewBox="0 0 24 24"width="24"xmlns="http://www.w3.org/2000/svg"><path d="M2 3h20"></path><path d="M21 3v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V3"></path><path d="m7 21 5-5 5 5"></path></svg>',
            'documents' =>
                '<svg class="lucide lucide-file-text text-blue-500"fill="none"height="24"stroke="currentColor"stroke-linecap="round"stroke-linejoin="round"stroke-width="2"viewBox="0 0 24 24"width="24"xmlns="http://www.w3.org/2000/svg"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path><path d="M14 2v4a2 2 0 0 0 2 2h4"></path><path d="M10 9H8"></path><path d="M16 13H8"></path><path d="M16 17H8"></path></svg>',
            'exel_files' =>
                '<svg class="lucide lucide-file-spreadsheet text-green-500"fill="none"height="24"stroke="currentColor"stroke-linecap="round"stroke-linejoin="round"stroke-width="2"viewBox="0 0 24 24"width="24"xmlns="http://www.w3.org/2000/svg"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path><path d="M14 2v4a2 2 0 0 0 2 2h4"></path><path d="M8 13h2"></path><path d="M14 13h2"></path><path d="M8 17h2"></path><path d="M14 17h2"></path></svg>',
            'audio_files' =>
                '<svg class="lucide lucide-audio-lines text-indigo-500"fill="none"height="24"stroke="currentColor"stroke-linecap="round"stroke-linejoin="round"stroke-width="2"viewBox="0 0 24 24"width="24"xmlns="http://www.w3.org/2000/svg"><path d="M2 10v3"></path><path d="M6 6v11"></path><path d="M10 3v18"></path><path d="M14 8v7"></path><path d="M18 5v13"></path><path d="M22 10v3"></path></svg>',
            'video_files' =>
                '<svg class="lucide lucide-video text-pink-500"fill="none"height="24"stroke="currentColor"stroke-linecap="round"stroke-linejoin="round"stroke-width="2"viewBox="0 0 24 24"width="24"xmlns="http://www.w3.org/2000/svg"><path d="m22 8-6 4 6 4V8Z"></path><rect height="12"rx="2"ry="2"width="14"x="2"y="6"></rect></svg>',
            'default' =>
                '<svg class="lucide lucide-file text-red-500"fill="none"height="24"stroke="currentColor"stroke-linecap="round"stroke-linejoin="round"stroke-width="2"viewBox="0 0 24 24"width="24"xmlns="http://www.w3.org/2000/svg"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path><path d="M14 2v4a2 2 0 0 0 2 2h4"></path></svg>',
            'folder' =>
                '<svg class="lucide lucide-folder text-amber-500"fill="none"height="24"stroke="currentColor"stroke-linecap="round"stroke-linejoin="round"stroke-width="2"viewBox="0 0 24 24"width="24"xmlns="http://www.w3.org/2000/svg"><path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"></path></svg>',
        ];

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $foundIcon = False;
        foreach ($groups as $group => $extensions) {
            if (in_array($ext, $extensions)) {
                echo $icons[$group] ?? $icons['default'];
                $foundIcon = True;
                break;
            }
        }

        if (!$foundIcon) {
            echo $icons['default'];
        }
    @endphp

</div>
