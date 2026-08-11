<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // Contato
            ['contact_phone', '', 'contact', 'Telefone principal'],
            ['contact_email', 'comercial@tmacimport.com.br', 'contact', 'E-mail comercial'],
            ['whatsapp_number', '5511999999999', 'contact', 'WhatsApp (E.164)'],
            ['whatsapp_default_message', 'Olá, gostaria de mais informações sobre os produtos da TMAC.', 'contact', 'Mensagem padrão WhatsApp'],
            ['address', '', 'contact', 'Endereço'],
            ['feedback_email', 'ouvidoria@tmacimport.com.br', 'contact', 'E-mail da ouvidoria (sugestões/reclamações). Vazio → usa o comercial.'],
            ['distributor_form_url', 'https://tsgmrsomo1jj.sg.larksuite.com/share/base/form/shrlgNTTXRvcR6JaDOqBRJnrXDd', 'contact', 'URL do formulário "Seja distribuidor" (botão do menu)'],

            // Redes
            ['social_instagram', '', 'social', 'Instagram URL'],
            ['social_facebook', '', 'social', 'Facebook URL'],
            ['social_linkedin', '', 'social', 'LinkedIn URL'],
            ['social_youtube', '', 'social', 'YouTube URL'],

            // Marketing
            ['gtm_id', '', 'marketing', 'Google Tag Manager ID'],
            ['ga4_id', '', 'marketing', 'GA4 Measurement ID'],
            ['rdstation_public_token', '', 'marketing', 'RD Station Public Token'],
            ['rdstation_enabled', '0', 'marketing', 'RD Station habilitado (0/1)'],

            // Catálogo
            ['catalog_pdf_url', '', 'content', 'URL do catálogo PDF'],
        ];

        foreach ($items as [$key, $value, $group, $label]) {
            Setting::updateOrCreate(['key' => $key], compact('value', 'group', 'label'));
        }
    }
}
