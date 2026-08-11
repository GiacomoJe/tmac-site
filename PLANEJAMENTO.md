# Planejamento Técnico — Novo Site TMAC Import

> Documento de planejamento e arquitetura do novo website comercial da TMAC Import.
> Stack: **Laravel 11 + Filament 3 + Livewire 3 + Alpine.js + Tailwind CSS + MySQL 8**.
> Foco absoluto em mobile first, performance, SEO técnico e captação de cotações.

---

## ETAPA 1 — ANÁLISE

### 1.1 Contexto da empresa
A TMAC Import é uma importadora/distribuidora que opera no modelo B2B (e B2B2C através de revendedores), trabalhando com múltiplas **marcas**, **categorias** e **produtos** técnicos, atendendo todo o território nacional por meio de uma rede de **representantes por estado**. O site atual cumpre função institucional, mas não opera como uma ferramenta comercial efetiva.

### 1.2 Pontos fortes do site atual (a preservar)
- Estrutura institucional consolidada (Quem somos, Como comprar, Manuais).
- Presença de catálogo digital, materiais técnicos e canais diretos via WhatsApp.
- Identidade visual e posicionamento de marca já estabelecidos.
- Conteúdo de marcas e produtos já mapeado.

### 1.3 Pontos fracos identificados (a corrigir)
- **Conversão fraca**: ausência de fluxo guiado de cotação. O cliente precisa "descobrir" como pedir orçamento.
- **Busca de produtos ineficiente**: sem busca técnica por SKU, marca, característica.
- **Mobile não é prioridade**: layout pensado para desktop e adaptado para mobile (mobile-secondary).
- **CTAs dispersos**: sem hierarquia clara entre "ver produto", "solicitar cotação", "falar com representante".
- **Carregamento pesado**: muitas imagens não otimizadas, sem lazy-loading, sem Core Web Vitals.
- **SEO técnico carente**: meta tags hardcoded, sem sitemap dinâmico, sem schema.org de produto.
- **Sem integração de marketing**: ausência de RD Station, eventos GTM, conversões mapeadas.
- **Gestão administrativa limitada**: dependência de programador para mudanças básicas.
- **Representantes pouco visíveis**: cliente perde tempo descobrindo quem atende seu estado.

### 1.4 Problemas de UX detectados
- Excesso de cliques entre "interesse" e "cotação".
- Falta de carrinho de cotação (cliente não consegue agrupar produtos de interesse).
- Falta de identificação automática por UF para mostrar representante regional.
- Falta de feedback após envio de formulário.
- Falta de filtros por categoria/marca em listagens.
- Falta de página de produto pensada para decisão de compra B2B.

### 1.5 Oportunidades comerciais
1. **Carrinho de cotação multi-produto**: cliente seleciona vários produtos e envia 1 solicitação única → aumento de ticket médio por lead.
2. **Roteamento automático por UF**: solicitação cai direto no representante regional → reduz tempo de resposta.
3. **Botão flutuante de WhatsApp com contexto de produto**: clique já leva mensagem pré-formatada com SKU.
4. **Página de marca**: hub SEO para captar buscas como "produto X marca Y".
5. **Busca técnica**: por SKU, nome, característica → atende cliente B2B que sabe o que quer.
6. **Lead magnet com catálogo PDF**: troca download por e-mail/telefone.

### 1.6 Diretrizes mobile first
- Touch targets ≥ 44×44 px.
- Botão de cotação flutuante sempre visível no mobile.
- Menu hamburguer com busca no topo.
- Imagens em formato `webp` com `srcset` por viewport.
- Sem hover-dependent interactions (tudo funciona com tap).
- Primeira renderização (LCP) deve priorizar título do produto + imagem principal + CTA.

---

## ETAPA 2 — ARQUITETURA TÉCNICA

### 2.1 Stack final
| Camada | Tecnologia | Justificativa |
|---|---|---|
| Backend | Laravel 11 | Framework PHP maduro, ecossistema rico, produtividade alta. |
| Admin | Filament 3 | Painel pronto, CRUDs em minutos, autenticação inclusa. |
| Frontend dinâmico | Livewire 3 + Alpine.js | SSR-first (excelente SEO), zero build complexo, sintonia perfeita com Blade. |
| Estilo | Tailwind CSS 3 | Utilitário, leve, mobile-first nativo. |
| Banco | MySQL 8 | Confiável, fácil de hospedar, bem suportado pelo Laravel. |
| Busca | Laravel Scout + Database driver (MVP) → Meilisearch (escala) | Começamos simples; quando passar de ~500 produtos, migramos para Meilisearch sem trocar API. |
| Cache | File (MVP) → Redis (produção) | Cache de views, rotas, queries e fragmentos. |
| Fila | Database (MVP) → Redis (produção) | Para envio de e-mails de cotação e webhooks RD Station. |
| Imagens | Spatie Media Library + Intervention Image | Variantes responsivas, WebP, lazy-loading nativo. |
| SEO | Spatie Sitemap + meta dinâmico por entidade | Sitemap automático, schema.org JSON-LD. |

### 2.2 Estrutura de pastas (relevantes)
```
app/
├── Filament/
│   └── Resources/            # CRUDs do painel admin
├── Http/
│   ├── Controllers/Site/     # Controllers públicos
│   └── Middleware/
├── Livewire/
│   ├── Site/
│   │   ├── QuoteCart.php     # Carrinho de cotação
│   │   ├── ProductSearch.php # Busca instantânea
│   │   └── QuoteForm.php     # Formulário de cotação
│   └── Components/
├── Models/
│   ├── Product.php
│   ├── Category.php
│   ├── Brand.php
│   ├── Representative.php
│   ├── State.php
│   ├── QuoteRequest.php
│   ├── QuoteRequestItem.php
│   ├── Banner.php
│   └── Page.php
├── Services/
│   ├── QuoteRouter.php       # Roteia cotação para representante por UF
│   ├── RdStation.php         # Integração RD Station
│   └── SeoMeta.php           # Geração de meta tags
├── Traits/
│   ├── HasSeo.php
│   └── HasSlug.php
└── Observers/
    └── QuoteRequestObserver.php

resources/
├── views/
│   ├── layouts/
│   │   └── site.blade.php    # Layout base mobile-first
│   ├── components/           # Blade Components reutilizáveis
│   │   ├── header.blade.php
│   │   ├── footer.blade.php
│   │   ├── product-card.blade.php
│   │   ├── cta-quote.blade.php
│   │   └── whatsapp-float.blade.php
│   ├── site/
│   │   ├── home.blade.php
│   │   ├── product.blade.php
│   │   ├── category.blade.php
│   │   ├── brand.blade.php
│   │   ├── quote/
│   │   └── pages/
│   └── livewire/site/
├── css/app.css
└── js/app.js

database/
├── migrations/
├── seeders/
└── factories/

routes/
├── web.php
└── filament.php
```

### 2.3 Estratégia de componentes
- **Blade Components** para tudo que é estático/visual (cards, headers, footers, botões).
- **Livewire** apenas onde há estado/interatividade real: carrinho de cotação, busca, formulários multi-step.
- **Alpine.js** para microinterações (menu, abrir/fechar, dropdown, modal) — sem viagem ao servidor.
- Princípio: **render no servidor por padrão**, JS só onde traz valor.

### 2.4 Estratégia SEO
- URLs amigáveis com slug por entidade: `/produto/{slug}`, `/categoria/{slug}`, `/marca/{slug}`.
- Meta `title`, `description` e `og:*` por entidade (campos editáveis no Filament).
- **JSON-LD** schema.org `Product` em cada página de produto (com `brand`, `category`, `sku`).
- Sitemap.xml gerado dinamicamente com `spatie/laravel-sitemap`.
- Robots.txt no público.
- `canonical` em todas as páginas.
- H1 único por página, hierarquia semântica H1>H2>H3.
- Slug imutável após indexação (regra de negócio); se alterar, gera redirect 301.

### 2.5 Estratégia de performance
- **Tailwind purge** ativo → CSS final < 30 KB.
- **Lazy-loading** nativo em todas as imagens (`loading="lazy"`).
- **WebP** com fallback JPEG via `<picture>`.
- **Eager loading** com `with()` para evitar N+1.
- **Cache de query** em listagens públicas (categorias, marcas, banners) com TTL de 1h.
- **Cache de view** para fragmentos estáticos da home.
- **HTTP caching** via headers `Cache-Control` para assets imutáveis (com hash no nome).
- **Preload** de fontes críticas e LCP image.
- **Defer** de scripts não críticos.
- Target Core Web Vitals: LCP < 2.5s, FID < 100ms, CLS < 0.1.

### 2.6 Estratégia de imagens
- Upload original via Filament; geração automática de 3 variantes (thumb 200, medium 600, large 1200).
- Conversão automática para WebP.
- Armazenamento em `storage/app/public/products/` com link simbólico.
- Em produção, migrar para S3/R2 com CDN (Cloudflare).
- `srcset` e `sizes` em todas as imagens de produto.

### 2.7 Estratégia de cache
| Item | Driver MVP | Driver produção | TTL |
|---|---|---|---|
| Config | File | File | persistente |
| Routes | File | File | persistente |
| Views | File | File | persistente |
| Listagem de produtos destaque | File | Redis | 60 min |
| Listagem de categorias | File | Redis | 6h |
| Banners da home | File | Redis | 30 min |
| Representante por UF | File | Redis | 24h |

Invalidações: via Model Observers (ao salvar produto, limpa cache de produto e listagens relacionadas).

### 2.8 Estratégia de busca
- **Fase 1 (MVP)**: busca via Eloquent com `LIKE` em `name`, `sku`, `description`. Index FULLTEXT em campos textuais. Suficiente para até ~1000 produtos.
- **Fase 2 (escala)**: Laravel Scout + Meilisearch. Busca instantânea, tolerante a erros, ranking por relevância. Migração transparente — basta trocar driver.
- Busca é exposta no header em **todas as páginas** + componente Livewire de busca instantânea com debounce de 300ms.

---

## ETAPA 3 — MODELAGEM DO BANCO

### 3.1 Diagrama de relacionamentos (resumo)
```
Brand 1───* Product *───* Category (pivot product_category)
                │
                └──* QuoteRequestItem *───1 QuoteRequest
                                              │
                                              └───1 State
                                                    │
                                                    └───* Representative (pivot representative_state)
```

### 3.2 Tabelas (schema lógico)

#### `users`
Padrão Laravel + `role` (enum: admin, comercial). Acesso ao Filament.

#### `categories`
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| name | string(150) | |
| slug | string(180) unique | gerado de name |
| description | text nullable | |
| image_path | string nullable | |
| sort_order | int default 0 | ordem de exibição |
| is_active | bool default true | |
| seo_title | string(180) nullable | |
| seo_description | string(255) nullable | |
| created_at, updated_at | timestamps | |

#### `brands`
| Campo | Tipo |
|---|---|
| id, name, slug, description, logo_path | |
| website_url | string nullable |
| is_active, sort_order | |
| seo_title, seo_description | |
| timestamps | |

#### `products`
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| brand_id | FK brands | |
| sku | string(80) unique | |
| name | string(200) | |
| slug | string(220) unique | |
| short_description | string(500) nullable | |
| description | longText nullable | HTML do CKEditor |
| specifications | json nullable | pares chave/valor de características técnicas |
| main_image | string nullable | imagem destaque |
| is_featured | bool default false | |
| is_active | bool default true | |
| sort_order | int default 0 | |
| seo_title, seo_description | | |
| timestamps, softDeletes | |

#### `product_category` (pivot)
| product_id, category_id | FKs |
| sort_order | int |

#### `product_images`
| id, product_id, path, alt, sort_order | |

#### `states`
| id, name, uf (char 2 unique), region | seeder com 27 UFs |

#### `representatives`
| id, name, phone, whatsapp, email, photo_path, bio (text), is_active | |

#### `representative_state` (pivot)
| representative_id, state_id, is_primary | flag para representante principal de uma UF |

#### `quote_requests`
| Campo | Tipo | Notas |
|---|---|---|
| id, code (UUID/short hash p/ identificar publicamente) | |
| customer_name, company, email, phone | |
| state_id | FK |
| city | string(120) |
| message | text nullable |
| status | enum: pending, in_progress, won, lost | default pending |
| assigned_representative_id | FK nullable |
| source | string nullable | utm_source, etc. |
| utm_payload | json nullable | utm_* completo |
| ip, user_agent | nullable |
| timestamps |

#### `quote_request_items`
| id, quote_request_id (FK), product_id (FK), quantity, notes | |

#### `banners`
| id, title, subtitle, image_mobile, image_desktop, link_url, position (enum: home_top, home_mid), sort_order, starts_at, ends_at, is_active | |

#### `pages`
| id, slug unique, title, content (longText), seo_title, seo_description, is_active | |
Slugs reservados: `quem-somos`, `como-comprar`, `logistica`, `qualidade`, `contato`.

#### `settings` (key-value)
| key (string unique), value (text), group | tabela única para configurações globais editáveis (telefone, e-mail, WhatsApp, IDs do GTM, token RD Station etc.). |

#### `redirects`
| id, from_path, to_path, http_code (301/302), is_active | suporte a 301 quando slugs mudarem. |

### 3.3 Índices recomendados
- `products(slug)`, `products(sku)`, `products(is_active, is_featured)`.
- FULLTEXT em `products(name, short_description, description)` na fase 1.
- `categories(slug)`, `brands(slug)`, `pages(slug)`.
- `quote_requests(status, created_at)` e `quote_requests(state_id)`.

---

## ETAPA 4 — UX, CONVERSÃO E MOBILE FIRST

### 4.1 Estrutura ideal da home
Ordem de elementos (mobile, top → bottom):
1. **Header sticky**: logo + ícone busca + ícone menu + ícone carrinho de cotação (badge com contador).
2. **Hero banner** (1 ou 2 slides, sem autoplay agressivo): título curto + CTA "Solicitar cotação".
3. **Busca destacada**: campo grande "Procure por produto ou SKU…".
4. **Categorias em destaque** (grid 2 colunas no mobile, 4 desktop): ícone/imagem + nome.
5. **Marcas em destaque**: carrossel horizontal logos.
6. **Produtos em destaque**: cards com imagem + nome + SKU + botão "Adicionar à cotação".
7. **Bloco "Encontre seu representante"**: select de UF → mostra representante regional.
8. **Bloco institucional**: 3 cards (Logística, Qualidade, Suporte).
9. **CTA grande**: "Solicite sua cotação agora".
10. **Footer**: contato, redes, links úteis, catálogo PDF.

Botão flutuante de WhatsApp + botão flutuante "Cotação (N)" sempre visíveis no mobile.

### 4.2 Fluxo de cotação (3 passos)
**Passo 1 — Coleta de interesse (durante navegação)**
- Em cada card de produto: botão `+ Cotação`.
- Em cada página de produto: botão grande `Adicionar à cotação` + campo de quantidade.
- Contador no header atualiza via Livewire.

**Passo 2 — Revisão do carrinho de cotação**
- Lista de produtos selecionados (foto, nome, SKU, quantidade editável, botão remover).
- Caixa de "observações gerais".
- Botão `Continuar para dados de contato`.

**Passo 3 — Formulário e envio**
- Campos: nome, empresa, e-mail, telefone (com máscara), UF (select), cidade, mensagem opcional.
- LGPD checkbox: "Concordo com a política de privacidade".
- Botão `Enviar solicitação`.
- Ao enviar:
  - Salva no banco (`quote_requests` + `quote_request_items`).
  - Identifica representante pela UF via `QuoteRouter`.
  - Dispara e-mail para comercial e representante (queue).
  - Dispara evento RD Station (queue) com utm payload.
  - Dispara evento GTM `quote_submit`.
  - Mostra tela de obrigado com:
    - Código do pedido para acompanhamento.
    - Nome e WhatsApp direto do representante regional.
    - CTA "Fale agora pelo WhatsApp" com mensagem pré-formatada.

### 4.3 Estrutura da página de produto (mobile, top → bottom)
1. Breadcrumb (categoria > produto).
2. **Imagem principal** (LCP optimizado, com zoom).
3. Galeria (thumbs horizontais).
4. **H1**: nome do produto.
5. SKU + marca (link para página da marca).
6. Descrição curta (2-3 linhas).
7. **CTA principal**: `Adicionar à cotação` (sticky no mobile ao rolar).
8. **CTA secundário**: `WhatsApp com este produto` (link `wa.me` com mensagem pré-formatada).
9. Tabs/Accordions:
   - Descrição completa.
   - Características técnicas (tabela das specs).
   - Manuais/downloads (se houver).
10. **Produtos relacionados** (mesma categoria ou marca).
11. **Bloco "Fale com nosso representante"**.

### 4.4 CTAs — hierarquia e posicionamento
| Prioridade | CTA | Onde |
|---|---|---|
| Primária | Solicitar / Adicionar à cotação | Card, página produto, header (carrinho), float mobile |
| Secundária | WhatsApp com contexto | Página produto, sticky footer mobile, página rep. |
| Terciária | Ver catálogo / Manuais | Footer, página marca |
| Conteúdo | Quem somos / Como comprar | Menu principal, footer |

Regra: **uma única CTA primária por bloco**, alto contraste, ≥ 44px.

### 4.5 Estratégias de conversão
- **Microcopy de confiança**: "Resposta em até X horas úteis", "Atendimento por representante regional".
- **Form com poucos campos** (8 no máximo no fluxo).
- **Auto-fill UF por IP** (best effort) para reduzir fricção.
- **Exit-intent leve no desktop**: oferece download do catálogo PDF.
- **Prova social**: número de marcas, anos de mercado, estados atendidos (na home).
- **Mensagens de erro claras** em formulários (campo a campo).
- **Feedback de loading** ao enviar (botão muda para "Enviando…" desabilitado).

### 4.6 Mobile first — princípios operacionais
- Design system com escala tipográfica fluida (`clamp()`).
- Containers com `padding-inline` consistente (16px mobile, 24-32px desktop).
- Imagens com `aspect-ratio` definido para zero CLS.
- Tabelas se tornam "cards" no mobile (cada linha vira um card).
- Menus laterais via Alpine.js, sem reload.
- Testar em 360×640 (Android baixo), 390×844 (iPhone), 768×1024 (tablet), 1280+ (desktop).

---

## ETAPA 5 — ROADMAP

### 5.1 MVP (Sprint 1 + 2) — entregar em ~3-4 semanas
**Sprint 1 — Fundação (1 a 1,5 semana)**
- [ ] Setup Laravel + Filament + Tailwind + Livewire.
- [ ] Migrations e models de: Brand, Category, Product, ProductImage, State, Representative, QuoteRequest, QuoteRequestItem, Banner, Page, Setting.
- [ ] Seeder de States (27 UFs).
- [ ] Filament Resources: Brand, Category, Product, Representative, Banner, Page, QuoteRequest (read-only com filtros), Setting.
- [ ] Autenticação Filament + role admin.

**Sprint 2 — Site público + cotação (2 a 2,5 semanas)**
- [ ] Layout base mobile-first (header sticky, footer, float WhatsApp).
- [ ] Home com banners, categorias, marcas, produtos em destaque, busca por UF de representante.
- [ ] Página de categoria (listagem com filtros: marca).
- [ ] Página de marca (listagem com filtros: categoria).
- [ ] Página de produto.
- [ ] Livewire QuoteCart (estado em session, contador no header).
- [ ] Página de cotação com formulário e envio.
- [ ] E-mail de notificação para comercial e representante.
- [ ] Páginas institucionais via tabela `pages`.
- [ ] Sitemap + robots + meta dinâmicos.
- [ ] Integração básica GTM.

### 5.2 Sprint 3 — Marketing e otimização (~1 semana)
- [ ] Integração RD Station (criação de lead + eventos).
- [ ] Eventos GTM: view_item, add_to_quote, quote_submit, whatsapp_click.
- [ ] Otimização de imagens (Spatie Media Library + WebP).
- [ ] Cache de listagens públicas.
- [ ] Schema.org JSON-LD em produtos.
- [ ] Lighthouse audit + ajustes para Core Web Vitals.

### 5.3 Sprint 4 — Refino e busca avançada (~1 semana)
- [ ] Busca instantânea no header (Livewire + debounce).
- [ ] Filtros laterais em listagens (mobile via drawer).
- [ ] Página de representantes com busca por UF.
- [ ] Painel admin: dashboard com cotações por status/UF/produto.
- [ ] Exportação de cotações em CSV.
- [ ] 301 redirects do site antigo para o novo.

### 5.4 Backlog (pós-MVP)
- Catálogo PDF dinâmico gerado por filtros.
- Área do cliente (acompanhamento de cotações).
- Migração de busca para Meilisearch.
- Integração WhatsApp Business API.
- Multi-idioma (es/en) — caso seja exportador.
- A/B test de variações de CTA (via GTM).

### 5.5 Ordem ideal de desenvolvimento
1. **Modelagem** (migrations + models + relacionamentos).
2. **Admin Filament** (você consegue cadastrar dados antes do front estar pronto).
3. **Seed inicial** (representantes, estados, marcas, 10-20 produtos reais).
4. **Layout base + home estática**.
5. **Página de produto + página de categoria + página de marca**.
6. **Carrinho de cotação + formulário + e-mails**.
7. **Páginas institucionais + footer + SEO técnico**.
8. **Integrações de marketing (GTM, RD Station)**.
9. **Otimização e Lighthouse**.
10. **Deploy + 301 do site antigo**.

### 5.6 Decisões técnicas justificadas
- **Livewire em vez de Vue/Inertia**: o site é majoritariamente conteúdo + formulários; Livewire entrega SSR puro (SEO sem esforço), build mais simples, integração direta com Filament. Vue só se justificaria se houvesse SPA real.
- **Filament em vez de Nova/Backpack**: gratuito, ativo, melhor DX para Laravel 11.
- **Spatie Media Library em vez de upload manual**: variantes responsivas, WebP automático, organização por collection.
- **Cache em File no MVP**: zero dependência extra; troca-se por Redis em produção em 1 linha de `.env`.
- **Search em LIKE/FULLTEXT no MVP**: Meilisearch acrescenta um serviço a operar; só compensa quando o volume justifica.
- **Roteamento de cotação por UF via Service (`QuoteRouter`)**: mantém regra de negócio isolada do controller; fácil de testar e estender (ex: rateio entre múltiplos representantes).

---

## Critérios de Aceite (do briefing) — Cobertura

| Critério | Coberto por |
|---|---|
| Funcionar perfeitamente no celular | Mobile first em todo o design system |
| Encontrar produto e cotar com facilidade | Busca + cards com CTA + fluxo 3 passos |
| Admin cadastra sem programador | Filament Resources para todas as entidades |
| Cotações registradas no painel | Tabela `quote_requests` + Filament Resource |
| SEO, GTM, RD Station | Spatie Sitemap + JSON-LD + Service RdStation |
| Escalável para futuras integrações | Services isolados, Observers, Queue, Scout-ready |
