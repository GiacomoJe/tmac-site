# TMAC Import — Novo Site

Plataforma comercial da TMAC Import desenvolvida em **Laravel 11 + Filament 3 + Livewire 3 + Tailwind CSS**, com foco em **mobile first**, performance, SEO técnico e captação de cotações.

## Pré-requisitos

- PHP 8.2+
- Composer 2.x
- MySQL 8+
- Node.js 20+ e NPM
- Extensões PHP: mbstring, openssl, pdo_mysql, fileinfo, gd, intl

## Instalação local

```bash
# 1) Clonar repositório (ou copiar para diretório do servidor)
cd "Site TMAC"

# 2) Instalar dependências PHP
composer install

# 3) Configurar ambiente
cp .env.example .env
php artisan key:generate

# 4) Configurar banco de dados em .env (DB_DATABASE, DB_USERNAME, DB_PASSWORD)

# 5) Rodar migrations + seeders
php artisan migrate --seed

# 6) Storage link
php artisan storage:link

# 7) Instalar dependências JS e compilar assets
npm install
npm run dev

# 8) Subir o servidor
php artisan serve
```

Acesso ao painel admin: `http://localhost:8000/admin`

Usuário inicial criado pelo seeder:
- E-mail: `admin@tmacimport.com.br`
- Senha: `tmac@admin` (alterar no primeiro acesso)

## Estrutura de diretórios (resumo)

```
app/
├── Filament/Resources/      # CRUDs do painel admin
├── Http/Controllers/Site/   # Controllers públicos
├── Livewire/Site/           # Componentes Livewire (cotação, busca)
├── Models/                  # Models Eloquent
├── Services/                # Regras de negócio (QuoteRouter, RdStation, SeoMeta)
└── Traits/                  # HasSlug, HasSeo

resources/
├── views/
│   ├── layouts/site.blade.php
│   ├── components/          # Componentes Blade
│   ├── site/                # Páginas públicas
│   └── livewire/site/
└── css/app.css

database/
├── migrations/
├── seeders/
└── factories/
```

## Módulos principais

- **Produtos**: CRUD com SKU, marca, categorias, imagens, características técnicas, SEO, status.
- **Categorias / Marcas**: CRUD com slug, imagem, ordenação, status.
- **Cotações**: registro de solicitações com itens, roteamento por UF para representante regional.
- **Representantes**: cadastro com estados atendidos.
- **Banners e Páginas institucionais**: gerenciados via Filament.
- **Settings**: chave-valor para configurações globais (WhatsApp, tokens, etc).

## Integrações

- **GTM / Google Ads**: container configurável via `.env`.
- **RD Station**: token configurável via `.env`; eventos enviados via queue.
- **WhatsApp**: número e mensagem padrão configuráveis; deep-link em CTAs.
- **Sitemap**: gerado dinamicamente em `/sitemap.xml`.

## Comandos úteis

```bash
# Limpar cache
php artisan optimize:clear

# Regenerar sitemap
php artisan sitemap:generate

# Criar admin via tinker
php artisan tinker
>>> \App\Models\User::create(['name'=>'Admin','email'=>'x@y.z','password'=>bcrypt('senha')])
```

## Próximos passos pós-MVP

- Migrar busca para Meilisearch (Laravel Scout).
- Implementar fila Redis para envio assíncrono de e-mails e webhooks.
- Migrar storage para S3/R2 com CDN.
- Área do cliente para acompanhamento de cotações.

Consulte **PLANEJAMENTO.md** para o documento completo de arquitetura e roadmap.
