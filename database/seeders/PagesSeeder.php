<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PagesSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'slug' => 'quem-somos',
                'title' => 'Quem Somos',
                'content' => '<p>A TMAC Import é uma importadora consolidada no mercado brasileiro...</p>',
                'seo_title' => 'Quem Somos — TMAC Import',
                'seo_description' => 'Conheça a TMAC Import: tradição em importação de produtos de qualidade para todo o Brasil.',
            ],
            [
                'slug' => 'como-comprar',
                'title' => 'Como Comprar',
                'content' => '<p>O processo é simples: selecione os produtos de interesse e solicite uma cotação...</p>',
                'seo_title' => 'Como Comprar — TMAC Import',
                'seo_description' => 'Veja como solicitar uma cotação na TMAC Import passo a passo.',
            ],
            [
                'slug' => 'logistica',
                'title' => 'Logística',
                'content' => '<p>Entregamos para todo o Brasil...</p>',
                'seo_title' => 'Logística — TMAC Import',
                'seo_description' => 'Entrega e logística da TMAC Import para todo o território nacional.',
            ],
            [
                'slug' => 'qualidade',
                'title' => 'Qualidade',
                'content' => '<p>Nosso compromisso com a qualidade...</p>',
                'seo_title' => 'Qualidade — TMAC Import',
                'seo_description' => 'Compromisso com qualidade em todos os produtos da TMAC Import.',
            ],
            [
                'slug' => 'contato',
                'title' => 'Contato',
                'content' => '<p>Entre em contato com nosso time comercial.</p>',
                'seo_title' => 'Contato — TMAC Import',
                'seo_description' => 'Fale com a TMAC Import: telefone, e-mail, WhatsApp e representantes regionais.',
            ],
            [
                'slug' => 'ouvidoria',
                'title' => 'Ouvidoria',
                'content' => null,
                'seo_title' => 'Ouvidoria — TMAC Import',
                'seo_description' => 'Canal de ouvidoria da TMAC Import: envie sugestões, reclamações ou elogios e ajude a melhorar nosso atendimento.',
            ],
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(['slug' => $page['slug']], $page);
        }
    }
}
