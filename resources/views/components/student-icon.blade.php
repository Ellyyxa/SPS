@props(['name'])
<svg {{ $attributes->merge(['class' => 'student-nav-icon', 'width' => '22', 'height' => '22', 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'aria-hidden' => 'true']) }}>
    @if ($name === 'dashboard') <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
    @elseif ($name === 'tasks') <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
    @elseif ($name === 'calendar') <rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
    @elseif ($name === 'notifications') <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/>
    @elseif ($name === 'emotion') <circle cx="12" cy="12" r="9"/><path d="M8 14s1.5 2 4 2 4-2 4-2M9 9h.01M15 9h.01"/>
    @elseif ($name === 'penguin') <path d="M12 3c-4 0-7 3.7-7 8.2V20l3-2 4 3 4-3 3 2v-8.8C19 6.7 16 3 12 3Z"/><path d="M9 10h.01M15 10h.01M10 14l2 1 2-1"/>
    @elseif ($name === 'profile') <circle cx="12" cy="8" r="4"/><path d="M4 21c.7-4 3.2-6 8-6s7.3 2 8 6"/>
    @elseif ($name === 'logout') <path d="M10 17l5-5-5-5M15 12H3"/><path d="M21 3v18h-8"/>
    @endif
</svg>
