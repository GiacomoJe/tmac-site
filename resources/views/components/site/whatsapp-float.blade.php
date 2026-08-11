@php
    $num = \App\Models\Setting::get('whatsapp_number');
    $msg = urlencode(\App\Models\Setting::get('whatsapp_default_message') ?? '');
@endphp
@if($num)
    <a href="https://wa.me/{{ $num }}?text={{ $msg }}"
       target="_blank" rel="noopener"
       onclick="window.dataLayer&&dataLayer.push({event:'whatsapp_click',source:'float'})"
       aria-label="WhatsApp"
       class="md:hidden fixed bottom-5 right-5 z-30 bg-whatsapp hover:bg-whatsapp/90 text-white w-13 h-13 p-3.5 rounded-full shadow-lg flex items-center justify-center transition">
        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M19.05 4.91A10 10 0 0 0 4.07 18.2L3 22l3.9-1.02a10 10 0 0 0 12.15-16.07ZM12 19.5a7.5 7.5 0 0 1-3.81-1.04l-.27-.16-2.07.54.55-2.02-.18-.28A7.5 7.5 0 1 1 12 19.5Zm4.1-5.6c-.22-.11-1.32-.65-1.53-.72-.2-.07-.35-.11-.5.11-.14.22-.56.71-.7.86-.13.14-.25.16-.47.05a6.1 6.1 0 0 1-1.8-1.1 6.74 6.74 0 0 1-1.25-1.54c-.13-.22-.01-.34.1-.45.1-.1.22-.27.33-.4.11-.14.15-.24.22-.4.07-.15.04-.28-.02-.4-.05-.11-.5-1.2-.68-1.64-.18-.43-.36-.37-.5-.38h-.42a.8.8 0 0 0-.58.27 2.45 2.45 0 0 0-.78 1.85c0 1.1.8 2.15.91 2.3.11.14 1.56 2.38 3.78 3.34.53.23.94.36 1.26.46.53.16 1.02.14 1.4.09.43-.07 1.32-.54 1.5-1.06.19-.52.19-.97.13-1.06-.06-.1-.2-.15-.43-.26Z"/></svg>
    </a>
@endif
