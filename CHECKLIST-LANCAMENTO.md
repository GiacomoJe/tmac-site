# Checklist de lançamento — Site TMAC Import

Guia completo do que precisa ser trocado, cadastrado e configurado antes de colocar o site no ar.

Ordem sugerida:
1. **Preparar assets** (imagens/logos com a arte)
2. **Configurar `.env` de produção** (banco, e-mail, tokens)
3. **Rodar migrations + seeders base** (estados, motos, categorias)
4. **Cadastrar conteúdo no admin** (banners, produtos, representantes, etc.)
5. **Trocar imagens de fallback no código** (Unsplash → arquivos próprios)
6. **Publicar e testar**

---

## PARTE 1 — DADOS FICTÍCIOS QUE PRECISAM SAIR

### 1.1 Usuário admin padrão
- **Arquivo:** `database/seeders/DatabaseSeeder.php`
- **Fictício:** `admin@tmacimport.com.br` / senha `tmac@admin`
- **Ação:** Depois de rodar o seeder, entre em `/admin` e:
  - Crie um usuário próprio com seu e-mail
  - **Delete o admin padrão** ou troque a senha imediatamente

### 1.2 Produtos de demonstração
- **Arquivo:** `database/seeders/ProductsSeeder.php`
- **Fictício:** Gera ~50 produtos aleatórios com SKUs tipo `CB-0001`, `MAG-0022` etc.
- **Ação:** **NÃO rode o `ProductsSeeder`** em produção. Cadastre seus produtos reais via `/admin → Catálogo → Produtos`.

### 1.3 Marcas externas de demonstração
- **Arquivo:** `database/seeders/BrandsSeeder.php`
- **Verificar:** Se há marcas demo. Substitua pela lista real de fornecedores (Cobreq, NGK, DID, Bosch, etc.)

### 1.4 Redes sociais fictícias
- **Arquivo:** `database/seeders/SocialLinksSeeder.php`
- **Fictício:** URLs `https://www.instagram.com/tmacimport`, `https://www.facebook.com/tmacimport` etc.
- **Ação:** No `/admin → Conteúdo → Redes sociais`, edite cada uma com as URLs reais das contas TMAC.

### 1.5 Configurações de contato genéricas
- **Arquivo:** `database/seeders/SettingsSeeder.php`
- **Fictício:**
  - `whatsapp_number: 5511999999999`
  - `contact_phone: (vazio)`
  - `contact_email: comercial@tmacimport.com.br`
  - `feedback_email: ouvidoria@tmacimport.com.br`
  - `address: (vazio)`
- **Ação:** Em `/admin → Configurações`, preencha os valores reais.

### 1.6 Depoimentos fake na Home
- **Arquivo:** `resources/views/site/home.blade.php` (seção 8 "Social proof")
- **Fictício:** João Silva / Carlos Santos / Roberto Lima
- **Ação:** Trocar os 3 depoimentos por reais OU remover essa seção (me avise).

### 1.7 Estados com valor mínimo genérico
- **Arquivo:** `database/seeders/StatesSeeder.php`
- **Fictício:** Valores mínimos por região (SP=2000, BA=2500, AM=3500)
- **Ação:** Em `/admin → Cotação → Estados`, revise/ajuste o valor mínimo real de cada UF.

---

## PARTE 2 — IMAGENS FICTÍCIAS (URLs do Unsplash)

Todas as fotos do site atualmente vêm do Unsplash como placeholder. Precisam ser substituídas por fotos reais da TMAC (workshop, CD, equipe, peças).

Consulte o arquivo `BRIEFING-IMAGENS.md` para as **dimensões exatas** de cada foto.

### 2.1 Home
| Local | Arquivo | Foto Unsplash atual |
|---|---|---|
| Hero fallback (quando não há banner) | `resources/views/site/home.blade.php` linha ~72 | `photo-1568772585407-9361f9bf3a87` (motor) |
| Banda "Centro de distribuição" | mesmo arquivo, seção 2C | `photo-1605559424843-9e4c228bf1c2` (oficina) |
| Bloco social-proof | mesmo arquivo, seção 8 | `photo-1632823469850-2f77dd9c7f93` (mecânico) |
| CTA final vermelha | mesmo arquivo, seção 9 | `photo-1547549700-bf12d5e5ce8a` (moto) |

### 2.2 Quem somos
- **Hero (topo)**: cadastrável no admin → `/admin → Conteúdo → Páginas → Quem somos → Imagem de capa`
- **Fotos internas (image-breakers, representantes, produtos, CTA final):** hardcoded no `resources/views/site/pages/quem-somos.blade.php` — para trocar, editar as URLs Unsplash no arquivo (procurar por `images.unsplash.com`)

### 2.3 Contato
- **Hero (topo)**: cadastrável no admin → `/admin → Conteúdo → Páginas → Contato → Imagem de capa`
- Sem outras fotos hardcoded

### 2.4 Ouvidoria
- **Hero (topo)**: cadastrável no admin → `/admin → Conteúdo → Páginas → Ouvidoria → Imagem de capa`
- Sem outras fotos hardcoded

### 2.5 Universo TMAC
- **Listagem:** `resources/views/site/tmac-brands.blade.php` — 1 foto hero
- **Detalhe:** `resources/views/site/tmac-brand.blade.php` — 1 foto hero (por linha)
- **Ação:** No `/admin → Conteúdo → Universo TMAC`, cadastre a foto real de cada linha (Corami, LBJ, Atrox, Motoled). O sistema usa a foto cadastrada quando disponível; se não tiver, cai no Unsplash como fallback.

### 2.6 Logo TMAC
- **Arquivo atual:** `storage/images/logo-tmac.png`
- **Ação:** Substituir pelo logo definitivo. Especs:
  - Altura renderizada: 40px
  - Recomendado: PNG transparente 240×80 @3x OU SVG
  - Variantes: colorido (fundo claro) + branco (fundo escuro do footer)

### 2.7 Favicon
- **Não existe ainda no projeto.**
- **Ação:** A arte deve entregar:
  - `favicon.ico` (16×16 + 32×32)
  - `favicon-192.png` (PWA)
  - `favicon-512.png` (PWA)
  - `apple-touch-icon.png` (180×180)

### 2.8 Open Graph (compartilhamento em redes sociais)
- **Não existe ainda no projeto.**
- **Ação:** Uma imagem 1200×630px em `storage/images/og-image.jpg` referenciada no `SeoMeta` como default.

---

## PARTE 3 — CADASTROS QUE VOCÊ PRECISA FAZER NO ADMIN

Aqui é a lista completa do que precisa entrar no painel `/admin` antes de publicar.

### 3.1 Configurações (`/admin → Configurações`)

| Chave | O que preencher |
|---|---|
| `contact_phone` | Telefone principal do comercial (ex: `(11) 3333-4444`) |
| `contact_email` | E-mail comercial (ex: `comercial@tmacimport.com.br`) |
| `whatsapp_number` | WhatsApp em E.164 sem `+` (ex: `5511999999999`) |
| `whatsapp_default_message` | Mensagem inicial ao clicar no WhatsApp |
| `address` | Endereço da sede |
| `feedback_email` | E-mail que recebe manifestações da ouvidoria |
| `gtm_id` | ID do Google Tag Manager (`GTM-XXXXXX`) |
| `ga4_id` | ID do GA4 (`G-XXXXXXX`) |
| `rdstation_public_token` | Token público do RD Station (quando integrar) |
| `rdstation_enabled` | `1` para ativar envio de leads pro RD, `0` desativado |
| `catalog_pdf_url` | URL do PDF do catálogo (se tiver) |

### 3.2 Estados (`/admin → Cotação → Estados`)

- Todos os 26 estados + DF já vêm cadastrados pelo seeder
- **Revisar o valor mínimo de cotação** de cada estado (`min_quote_value`)
- Se algum estado NÃO deve receber cotações, desativar

### 3.3 Redes sociais (`/admin → Conteúdo → Redes sociais`)

- Editar as 5 demos (Instagram, Facebook, YouTube, TikTok, LinkedIn)
- Trocar URLs pelas reais
- Adicionar outras plataformas se necessário (WhatsApp, Telegram, Spotify, Threads, etc.)
- Ordenar por importância (drag-and-drop)
- Desativar as que não estão em uso

### 3.4 Marcas distribuídas (`/admin → Catálogo → Marcas`)

- Cadastrar todas as marcas de parceiros/fornecedores (Cobreq, NGK, Bosch, DID, Vedamotors, etc.)
- Fazer upload do logo de cada marca (PNG transparente ~300×100)
- Ordenar por relevância

### 3.5 Universo TMAC (`/admin → Conteúdo → Universo TMAC`)

- Já vêm 4 linhas cadastradas: **Corami**, **LBJ**, **Atrox**, **Motoled**
- Para cada linha:
  - Confirmar/ajustar tagline e descrição
  - **Fazer upload da foto de capa** (1200×1600px @3:4)
  - **Fazer upload do logo** (400×120px PNG transparente)
  - Confirmar cor sólida da marca (color picker)
  - Marcar "destacar na home" (todas já vêm marcadas)

### 3.6 Categorias e subcategorias (`/admin → Catálogo → Categorias`)

- Já vêm cadastradas 10 categorias raiz + 50+ subcategorias
- **Revisar** se a árvore está adequada ao seu catálogo
- Adicionar imagem para cada categoria raiz (opcional mas recomendado — 800×800px)
- Ativar/desativar conforme catálogo real
- Ordenar (drag-and-drop)

### 3.7 Motos — marcas e modelos (`/admin → Catálogo → Motos`)

- Já vêm cadastradas as principais marcas (Honda, Yamaha, Suzuki, Kawasaki, BMW, Royal Enfield, Harley-Davidson, Triumph) + modelos populares brasileiros
- **Revisar** se está adequado ao seu catálogo
- Adicionar/ajustar modelos conforme necessário

### 3.8 Representantes (`/admin → Comercial → Representantes`)

- Cadastrar cada representante regional:
  - Nome
  - Telefone
  - **E-mail** (obrigatório para receber cotações encaminhadas)
  - WhatsApp
  - Estados atendidos (múltipla escolha)
  - Ativo/inativo
- Sem representantes cadastrados, o encaminhamento manual de cotações não funciona

### 3.9 Produtos (`/admin → Catálogo → Produtos`)

Este é o **cadastro mais volumoso e demorado**. Para cada produto:

| Campo | Obrigatório? | Notas |
|---|---|---|
| Nome | Sim | Ex: "Cabo de acelerador CG 150 Titan" |
| SKU/Código | Sim | Único |
| Marca | Sim | Selecionar entre as cadastradas |
| Linha Universo TMAC | Não | Se for produto próprio (Corami/LBJ/Atrox/Motoled) |
| Categorias | Sim | Múltipla escolha |
| Preço de referência (`quote_price`) | Sim | USO INTERNO, nunca exibido. Usado pra calcular mínimo |
| Imagem principal | Sim | 1500×1500 fundo branco |
| Descrição curta | Recomendado | Aparece no card |
| Descrição completa | Recomendado | Página de detalhe |
| Especificações técnicas | Opcional | JSON estruturado |
| Status de estoque | Sim | Pronta-entrega / Baixo / Sob cotação |
| Compatibilidade com motos | Recomendado | Marca / modelo / ano |
| SEO title + description | Recomendado | Para Google |
| Destaque | Opcional | Marca para aparecer na home |
| Ordem | Opcional | Prioridade dentro da categoria/marca |

**Volume esperado:** Para o site funcionar bem, o mínimo é ~50 produtos. Ideal 500-1000+ SKUs.

### 3.10 Banners da home (`/admin → Conteúdo → Banners`)

- Cadastrar 3-5 banners rotativos para o hero da home
- Cada banner:
  - Imagem desktop (2400×1300px 16:9) — obrigatória
  - Imagem mobile (1080×1440px 4:5) — obrigatória
  - Título e subtítulo (ou marcar "banner limpo" pra usar arte pronta)
  - Texto do botão + URL
  - Datas de início/fim (opcional, para promoções sazonais)
  - Ativo/inativo, ordem

### 3.11 Páginas institucionais (`/admin → Conteúdo → Páginas`)

- **Quem somos** — conteúdo já tem placeholder editorial (fallback). Você pode:
  - Deixar o layout pronto do arquivo Blade (recomendado)
  - Ou editar o campo `content` HTML pra sobrescrever
- **Como comprar** — precisa criar/ajustar conteúdo
- **Contato** — conteúdo já vem via layout customizado
- **Ouvidoria** — conteúdo já vem via layout customizado
- **Logística** e **Qualidade** — se referenciadas no footer, criar
- Para cada página: revisar SEO title + description

### 3.12 Testar fluxos críticos

- [ ] Adicionar produto ao carrinho → aparece modal pedindo UF
- [ ] Selecionar UF → produto vai pro carrinho
- [ ] Barra de progresso atualiza conforme adiciona itens
- [ ] Ao atingir mínimo, botão "Continuar" libera
- [ ] Enviar cotação → chega e-mail no comercial
- [ ] Admin recebe cotação no painel
- [ ] Admin encaminha cotação pra vendedor → chega e-mail
- [ ] Formulário de contato envia lead → chega e-mail
- [ ] Formulário de ouvidoria envia lead → chega e-mail no endereço correto
- [ ] Newsletter no footer inscreve
- [ ] Todos os links do menu funcionam
- [ ] Filtros de produto por categoria/subcategoria/marca funcionam
- [ ] Página do Universo TMAC → link "Ver produtos" abre com filtro

---

## PARTE 4 — CONFIGURAÇÃO DE PRODUÇÃO (`.env`)

Antes de publicar, edite o arquivo `.env` do servidor:

```env
APP_NAME="TMAC Import"
APP_ENV=production
APP_KEY=(gerado automaticamente com php artisan key:generate)
APP_DEBUG=false
APP_TIMEZONE=America/Sao_Paulo
APP_URL=https://tmacimport.com.br

APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=pt_BR

# Banco de produção
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tmac_site
DB_USERNAME=(usuário do banco)
DB_PASSWORD=(senha forte)

# Sessões e filas em banco
SESSION_DRIVER=database
SESSION_LIFETIME=120
QUEUE_CONNECTION=database
CACHE_STORE=file

# E-mail SMTP (SendGrid, Resend, SES, Brevo, ou o que você usar)
MAIL_MAILER=smtp
MAIL_HOST=(seu smtp)
MAIL_PORT=587
MAIL_USERNAME=(usuário)
MAIL_PASSWORD=(senha)
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@tmacimport.com.br"
MAIL_FROM_NAME="${APP_NAME}"

# Cotação — destinatário das notificações
QUOTE_COMMERCIAL_EMAIL=comercial@tmacimport.com.br
QUOTE_DEFAULT_MINIMUM=2000

# Integrações (preencher quando tiver)
RDSTATION_PUBLIC_TOKEN=
RDSTATION_ENABLED=false
GTM_CONTAINER_ID=
GOOGLE_ADS_CONVERSION_ID=

# WhatsApp institucional (também no admin)
WHATSAPP_NUMBER=5511999999999
WHATSAPP_DEFAULT_MESSAGE="Olá, gostaria de mais informações."
```

---

## PARTE 5 — COMANDOS DE PUBLICAÇÃO

Depois de subir o código no servidor de produção:

```bash
# 1. Instala dependências (sem dev, otimizado)
composer install --no-dev --optimize-autoloader

# 2. Copia .env e gera chave
cp .env.example .env
# ...edita com dados de produção...
php artisan key:generate

# 3. Roda migrations
php artisan migrate --force

# 4. Roda seeders essenciais (NÃO rode ProductsSeeder!)
php artisan db:seed --class=StatesSeeder --force
php artisan db:seed --class=SettingsSeeder --force
php artisan db:seed --class=PagesSeeder --force
php artisan db:seed --class=MotorcyclesSeeder --force
php artisan db:seed --class=CategoriesSeeder --force
php artisan db:seed --class=BrandsSeeder --force
php artisan db:seed --class=SocialLinksSeeder --force
php artisan db:seed --class=TmacBrandsSeeder --force

# 5. Cria usuário admin (via tinker)
php artisan tinker
# Dentro:
User::create(['name' => 'Jeferson', 'email' => 'seu-email@dominio.com', 'password' => Hash::make('senha-forte'), 'role' => 'admin', 'email_verified_at' => now()]);
# Depois DELETE o admin padrão criado pelo DatabaseSeeder

# 6. Link do storage (imagens uploadadas ficam acessíveis via web)
php artisan storage:link

# 7. Build de assets
npm ci && npm run build

# 8. Caches otimizados de produção
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:cache-components

# 9. Permissões
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

---

## PARTE 6 — MANUTENÇÃO CONTÍNUA (pós-lançamento)

Depois do lançamento, no dia a dia:

### Diariamente
- Verificar leads novos em `/admin → Comercial → Leads`
- Verificar cotações novas em `/admin → Comercial → Solicitações de cotação`
- Encaminhar cotações para vendedores

### Semanalmente
- Cadastrar produtos novos que chegaram
- Ajustar status de estoque
- Atualizar banners (se houver campanha)
- Backup do banco (deveria ser automático)

### Mensalmente
- Analisar leads por origem (qual formulário mais converte)
- Revisar valores mínimos de cotação por estado (ajustar conforme margem)
- Adicionar novos representantes se necessário
- Publicar novo conteúdo na página Quem somos (se relevante)

---

## RESUMO EXECUTIVO — Ordem de execução

1. **Passar `BRIEFING-IMAGENS.md` pra arte** — enquanto a arte trabalha, você faz o resto
2. **Configurar `.env` de produção** no servidor
3. **Rodar migrations + seeders base** (SEM ProductsSeeder)
4. **Criar seu usuário admin, deletar o padrão**
5. **Cadastrar Configurações, Estados, Representantes** (fundação comercial)
6. **Cadastrar Marcas distribuídas + Universo TMAC** (com logos + fotos quando prontos)
7. **Cadastrar Categorias/subcategorias** (revisar árvore)
8. **Cadastrar Produtos** (o grosso do trabalho — pode ser feito gradualmente)
9. **Cadastrar Banners da home** (com as fotos definitivas)
10. **Substituir URLs Unsplash por fotos reais** no código
11. **Configurar SMTP, RD Station, GTM, GA4**
12. **Testar todos os fluxos** (checklist da seção 3.12)
13. **Publicar** (apontar DNS + SSL)

Boa sorte no lançamento!
