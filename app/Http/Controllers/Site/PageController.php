<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\SeoMeta;

class PageController extends Controller
{
    public function show(Page $page, SeoMeta $seo)
    {
        abort_unless($page->is_active, 404);

        $seo->set(
            $page->meta_title,
            $page->meta_description,
            canonical: route('site.page', $page->slug),
        );

        // Se houver template específico para o slug, usa; senão cai no genérico.
        $custom = "site.pages.{$page->slug}";
        $view = view()->exists($custom) ? $custom : 'site.page';

        return view($view, compact('page'));
    }
}
