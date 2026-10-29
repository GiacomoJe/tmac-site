# Guia de deploy — TMAC Import

Como levar o site do seu computador para o servidor, **mantendo tudo que já foi cadastrado**
(111 produtos, 13 categorias, 228 modelos de moto, 469 compatibilidades, marcas do Universo TMAC).

> Para a checagem de conteúdo antes de publicar (textos, imagens, configurações),
> veja `CHECKLIST-LANCAMENTO.md`. Este guia trata só do processo técnico.

---

## Visão geral

São 3 coisas que precisam chegar no servidor:

| O quê | Como vai | Observação |
|---|---|---|
| **Código** (PHP, views, CSS) | Git | `git push` → `git pull` no servidor |
| **Banco de dados** (produtos, categorias, motos…) | Dump SQL | Exporta local, importa no servidor |
| **Uploads** (fotos de produto, logos cadastrados) | Cópia de pasta | `storage/app/public` via FTP/SCP |

As imagens de design (banners, logos fixos) já estão em `public/images` e viajam junto com o código.

---

## Tabelas — o que sincronizar

Se você usa uma ferramenta de sincronização em vez do dump completo, use esta
classificação. **A direção importa:** o grupo A vai do seu PC → servidor; o grupo C
vai do servidor → seu PC (ou nem vem).

### A. Catálogo e conteúdo — sincronizar (PC → servidor)

Tudo que você cadastra/importa localmente e o site só lê:

| Tabela | Conteúdo |
|---|---|
| `brands` | Marcas dos fabricantes |
| `tmac_brands` | Linhas do Universo TMAC (cor, logo, textos) |
| `categories` | Categorias e subcategorias (`parent_id`) |
| `products` | Produtos (SKU, descrição, `quote_price`, `tmac_brand_id`) |
| `category_product` | **Pivot** produto ↔ categoria |
| `product_images` | Galeria de imagens |
| `motorcycle_makes` | Montadoras (Honda, Yamaha…) |
| `motorcycle_models` | Modelos de moto |
| `motorcycle_fitments` | **Pivot** produto ↔ moto (as 469 compatibilidades) |
| `states` | UFs e `min_quote_value` |
| `representatives` | Representantes / vendedores |
| `representative_state` | **Pivot** representante ↔ UF |
| `resellers` | Lojas do mapa (com lat/lng) |
| `banners` | Banners da home |
| `pages` | Heros e textos editáveis das páginas |
| `settings` | Configurações gerais (e-mails, ouvidoria, WhatsApp) |
| `social_links` | Redes sociais |
| `testimonials` | Depoimentos |
| `redirects` | Redirects do site antigo |

> **Ordem obrigatória** por causa das foreign keys: `brands`, `tmac_brands`,
> `motorcycle_makes`, `states`, `users` **antes** de `products`, `categories`,
> `motorcycle_models`, `representatives`. E os pivots (`category_product`,
> `motorcycle_fitments`, `representative_state`) sempre **por último**.
>
> Se a ferramenta não respeita ordem, desative as checagens durante a carga:
> ```sql
> SET FOREIGN_KEY_CHECKS = 0;
> -- ... carga ...
> SET FOREIGN_KEY_CHECKS = 1;
> ```

### B. Tabela de Vendas — depende de onde você publica

| Tabela | Conteúdo |
|---|---|
| `sales_price_lists` | Cada publicação da planilha .xlsx |
| `sales_states` | Códigos de tabela de preço (SP, RS, NET…) |
| `sales_catalog_items` | Snapshot preço × tabela (a maior tabela do banco) |

Se a planilha é publicada **pelo admin direto em produção**, não sincronize estas —
você sobrescreveria a versão ativa. Se você publica localmente e só espelha, inclua
no grupo A, sempre as três juntas (`sales_catalog_items` referencia
`sales_price_lists`).

### C. Dados dos clientes — NUNCA sobrescrever

Nascem em produção. Sincronizar na direção errada apaga vendas reais:

| Tabela | Conteúdo |
|---|---|
| `clientes` | Logins dos lojistas (senha com hash) |
| `quote_requests` | Cotações recebidas |
| `quote_request_items` | Itens das cotações |
| `leads` | Leads dos formulários (RD Station) |
| `sales_orders` | Pedidos da Tabela de Vendas |
| `sales_order_items` | Itens dos pedidos |

### D. Fora da sincronização

| Tabela | Por quê |
|---|---|
| `users` | Senhas de admin diferem entre ambientes |
| `sessions` | Sessões ativas — sincronizar derruba quem está logado |
| `password_reset_tokens` | Tokens temporários |
| `migrations` | Controlada pelo `php artisan migrate` |

> **Importante:** sincronizar linha de banco **não traz a imagem**. Os campos
> `products.main_image`, `product_images.path`, `tmac_brands.logo_path` e
> `banners.image_path` guardam só o caminho. Se o valor não começa com `images/`,
> o arquivo está em `storage/app/public` e precisa ser copiado separadamente.

---

## PARTE 1 — Preparar no seu computador

### 1.1 Exportar o banco

Abra o PowerShell na raiz do projeto:

```powershell
cd "C:\Users\Jeferson\Documents\Claude\Projects\Site TMAC"

C:\xampp\mysql\bin\mysqldump.exe -u root --default-character-set=utf8mb4 `
  --single-transaction --routines --events `
  tmac_site > tmac_site.sql
```

Confirme que o arquivo foi gerado e não está vazio:

```powershell
Get-Item tmac_site.sql | Select-Object Name, @{N='MB';E={[math]::Round($_.Length/1MB,2)}}
```

> **Atenção:** esse arquivo contém seus dados. Não faça commit dele.
> Adicione ao `.gitignore`:
> ```
> /tmac_site.sql
> *.sql
> ```

### 1.2 Compactar os uploads

```powershell
Compress-Archive -Path "storage\app\public\*" -DestinationPath "storage-uploads.zip" -Force
```

Isso empacota fotos de produto, logos das marcas e banners cadastrados pelo admin.

### 1.3 Enviar o código

```powershell
git add .
git commit -m "Catalogo completo: produtos, categorias e compatibilidade de motos"
git push origin master
```

---

## PARTE 2 — Preparar o servidor

### 2.1 Requisitos

Confira antes (detalhes completos no `CHECKLIST-LANCAMENTO.md`):

- PHP 8.2+ com extensões: `mbstring xml curl zip bcmath gd intl pdo_mysql openssl imagick`
- MySQL 8.0+ ou MariaDB 10.6+
- Composer 2.5+
- Node.js 20 (só para o build dos assets)
- **OPcache ativado** — faz muita diferença na velocidade

### 2.2 Criar o banco

```sql
CREATE DATABASE tmac_site
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE USER 'tmac'@'localhost' IDENTIFIED BY 'SUA_SENHA_FORTE';
GRANT ALL PRIVILEGES ON tmac_site.* TO 'tmac'@'localhost';
FLUSH PRIVILEGES;
```

### 2.3 Clonar o projeto

```bash
cd /var/www
git clone SEU_REPOSITORIO tmac
cd tmac
```

---

## PARTE 3 — Instalar

### 3.1 Dependências

```bash
composer install --no-dev --optimize-autoloader
```

### 3.2 Configurar o `.env`

```bash
cp .env.example .env
nano .env
```

Valores de produção:

```env
APP_NAME="TMAC Import"
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE=America/Sao_Paulo
APP_URL=https://tmacimport.com.br

APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=en

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tmac_site
DB_USERNAME=tmac
DB_PASSWORD=SUA_SENHA_FORTE

SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=file

# sync = envia e-mail na hora. O projeto não tem tabela `jobs`,
# então NÃO use "database" aqui sem criar a tabela primeiro.
QUEUE_CONNECTION=sync

# E-mail — obrigatório para cotações e leads funcionarem
MAIL_MAILER=smtp
MAIL_HOST=smtp.seuprovedor.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@tmacimport.com.br"
MAIL_FROM_NAME="${APP_NAME}"

QUOTE_COMMERCIAL_EMAIL=comercial@tmacimport.com.br
QUOTE_DEFAULT_MINIMUM=2000

# Preencher quando tiver
RDSTATION_PUBLIC_TOKEN=
RDSTATION_ENABLED=false
GTM_CONTAINER_ID=

WHATSAPP_NUMBER=5511999999999
```

### 3.3 Gerar a chave

```bash
php artisan key:generate
```

---

## PARTE 4 — Importar os dados

Três cenários. **Escolha o seu.**

| Situação | Vá para |
|---|---|
| O servidor **já tem o site rodando** e você só quer levar os produtos novos e a compatibilidade com motos | **4.0** — um comando |
| Servidor **novo**, do zero, e você quer o banco idêntico ao local | **4.1 – 4.4** — dump |
| Servidor **novo**, do zero, e prefere refazer os imports lá | **4.5** — sequência completa |

---

### 4.0 Só os produtos novos + vínculos com motos

Este é o caso mais comum no dia a dia: o site já está no ar e você acabou de preparar
uma planilha nova. Não precisa de dump, nem de seeder, nem de imagem.

```bash
cd /var/www/tmac
git pull origin master          # traz a planilha junto com o código

# Confira o que vai acontecer, sem gravar nada
php artisan fitments:import --dry-run --create-missing --create-products

# Se os números fizerem sentido, rode de verdade
php artisan fitments:import --create-missing --create-products
```

Esse comando faz tudo o que você precisa:

- **cadastra os produtos** cujo SKU ainda não existe (`--create-products`), já com
  nome, descrição reduzida, códigos originais e categoria inferida pelo grupo;
- **cadastra os modelos de moto** que faltarem (`--create-missing`);
- **cria os vínculos** produto ↔ moto com as faixas de ano.

É **seguro repetir**: vínculos que já existem são contados como "Já existiam" e não
duplicam. Produtos que já existem não são sobrescritos.

> **Pré-requisito:** as 13 categorias oficiais precisam existir no servidor — o comando
> procura por nome. Se o site já está no ar, elas estão lá.

**Para cada planilha nova no futuro**, o ciclo é só isso: coloca o `.xlsx` na raiz,
commita, `git pull` no servidor, roda o comando. Se o arquivo tiver outro nome, passe
como argumento:

```bash
php artisan fitments:import "NOVA PLANILHA.xlsx" --create-missing --create-products
```

**Duas coisas que ficam pendentes** nos produtos criados assim:

| Campo | Situação | Onde resolver |
|---|---|---|
| `quote_price` | Entra zerado — não conta no mínimo por estado | Admin → Produtos |
| `main_image` | Sem foto — o card mostra o placeholder | Admin → Produtos |

---

### 4.1 Enviar os arquivos

Use FileZilla, WinSCP ou SCP para enviar `tmac_site.sql` e `storage-uploads.zip`:

```powershell
# Exemplo com SCP (PowerShell)
scp tmac_site.sql usuario@seuservidor:/var/www/tmac/
scp storage-uploads.zip usuario@seuservidor:/var/www/tmac/
```

### 4.2 Importar o banco

```bash
cd /var/www/tmac
mysql -u tmac -p tmac_site < tmac_site.sql
```

Confira se veio tudo:

```bash
mysql -u tmac -p tmac_site -e "
SELECT 'produtos' t, COUNT(*) n FROM products
UNION SELECT 'categorias', COUNT(*) FROM categories
UNION SELECT 'modelos moto', COUNT(*) FROM motorcycle_models
UNION SELECT 'compatibilidades', COUNT(*) FROM motorcycle_fitments
UNION SELECT 'marcas TMAC', COUNT(*) FROM tmac_brands
UNION SELECT 'estados', COUNT(*) FROM states;"
```

Esperado: ~111 produtos · 13 categorias · 228 modelos · 469 compatibilidades · 5 marcas TMAC · 27 estados.

### 4.3 Restaurar os uploads

```bash
unzip -o storage-uploads.zip -d storage/app/public/
rm storage-uploads.zip tmac_site.sql     # não deixe no servidor
```

### 4.4 Rodar migrations pendentes

```bash
php artisan migrate --force
```

> Se o dump já estava atualizado, ele dirá "Nothing to migrate" — é o esperado.

---

### 4.5 Opção B — rodar os mesmos imports no servidor

Se você preferir este caminho, **pule os passos 4.1 a 4.4** (não precisa de dump nem do
zip de uploads) e rode a sequência abaixo. É exatamente o que foi feito no ambiente local.

As planilhas já viajam no Git (`produtos-tmac.xlsx`, `categorias-tmac.xlsx`,
`TESTE DE DESCRIÇÃO DE PRODUTOS TMAC.xlsx`), então estão na raiz do projeto no servidor.

```bash
cd /var/www/tmac

# 1. Estrutura do banco
php artisan migrate --force

# 2. Dados de fundação: 27 UFs, settings, páginas, motos,
#    redes sociais, marcas do Universo TMAC e as 13 categorias
php artisan db:seed --force

# 3. Produtos do export WooCommerce (só os dados, sem imagens)
php artisan products:import

# 4. Imagens dos produtos — DEMORADO (download um a um)
php artisan products:import --images-only

# 5. Imagens das categorias
php artisan categories:import-images

# 6. Descrições + compatibilidade com motos
php artisan fitments:import --create-missing --create-products

# 7. Remover as lojas fictícias do mapa
php artisan resellers:clear-demo
```

**Sobre o passo 4 (imagens):** baixa de `tmacimport.com.br` uma por uma e pode levar
bastante tempo. Rode em `screen`/`tmux` para a sessão não cair, ou em lotes:

```bash
php artisan products:import --images-only --offset=0    --limit=500
php artisan products:import --images-only --offset=500  --limit=500
# ... e assim por diante
```

> O comando é seguro para repetir — ele pula o que já baixou. Se cair no meio,
> rode de novo a partir do `--offset` onde parou.

**Antes de rodar, confira os números locais** para comparar depois:

```powershell
# No seu PC
php artisan tinker --execute="
echo 'produtos: '.App\Models\Product::count().PHP_EOL;
echo 'categorias: '.App\Models\Category::count().PHP_EOL;
echo 'modelos: '.App\Models\MotorcycleModel::count().PHP_EOL;
echo 'compat: '.DB::table('motorcycle_fitments')->count().PHP_EOL;
"
```

Rode o mesmo no servidor ao terminar. Os números devem bater.

**Dois ajustes manuais que sobram**, independente da opção escolhida:

1. **Imagens de design** (banners da home, heros, logos das marcas) — as que você
   enviou por upload estão em `storage/app/public` e **não** são recriadas por nenhum
   comando. Faça o zip do passo 1.2 e descompacte como no 4.3, ou reenvie pelo admin.
2. **`quote_price` dos produtos vindos da planilha de descrições** — entram com valor
   zerado e não contam no mínimo por estado. Preencha no admin.

---

## PARTE 5 — Finalizar

### 5.1 Link do storage

```bash
php artisan storage:link
```

### 5.2 Compilar os assets

```bash
npm ci
npm run build
```

> Se o servidor não tiver Node, rode `npm run build` no seu computador e envie a pasta `public/build` por FTP.

### 5.3 Permissões

```bash
chown -R www-data:www-data /var/www/tmac
chmod -R 755 /var/www/tmac
chmod -R 775 storage bootstrap/cache
```

### 5.4 Caches de produção

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan filament:cache-components
```

### 5.5 Apontar o servidor web para `/public`

**Nginx:**

```nginx
server {
    listen 80;
    server_name tmacimport.com.br www.tmacimport.com.br;
    root /var/www/tmac/public;      # ← aponta para public, nunca para a raiz

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* { deny all; }

    client_max_body_size 32M;
}
```

**Apache:** garanta `DocumentRoot /var/www/tmac/public` e `AllowOverride All`.

### 5.6 SSL

```bash
sudo certbot --nginx -d tmacimport.com.br -d www.tmacimport.com.br
```

### 5.7 Cron e fila

```bash
crontab -e
```

```cron
* * * * * cd /var/www/tmac && php artisan schedule:run >> /dev/null 2>&1
```

**Fila:** não é necessária hoje — nada no projeto usa `ShouldQueue`, os e-mails saem
na hora (`QUEUE_CONNECTION=sync`). Só configure o Supervisor abaixo **se** mais tarde
você criar a tabela `jobs` (`php artisan queue:table && php artisan migrate`) e passar
o `QUEUE_CONNECTION` para `database`:

```ini
[program:tmac-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/tmac/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/tmac/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread && sudo supervisorctl update && sudo supervisorctl start tmac-worker:*
```

---

## PARTE 6 — Segurança antes de abrir ao público

### 6.1 Trocar o usuário admin

```bash
php artisan tinker
```

```php
// Cria o seu usuário
App\Models\User::create([
    'name' => 'Jeferson',
    'email' => 'seu-email@dominio.com',
    'password' => Hash::make('SENHA_FORTE_AQUI'),
    'role' => 'admin',
    'email_verified_at' => now(),
]);

// Remove o admin padrão do seeder
App\Models\User::where('email', 'admin@tmacimport.com.br')->delete();
```

### 6.2 Verificar

```bash
# Debug tem que estar desligado
grep APP_DEBUG .env          # → APP_DEBUG=false

# .env não pode estar acessível pela web
curl -I https://tmacimport.com.br/.env     # → 403 ou 404
```

---

## PARTE 7 — Testar

- [ ] Home carrega com os banners
- [ ] `/produtos` lista os 111 produtos
- [ ] Filtro **"Minha moto"** funciona (Honda → Biz 125)
- [ ] Filtro por categoria e Universo TMAC
- [ ] Página de produto mostra "Compatível com"
- [ ] Adicionar ao carrinho → modal de UF aparece
- [ ] Barra de progresso do mínimo atualiza
- [ ] Botão de cotação fica **verde** ao atingir o mínimo
- [ ] Enviar cotação → e-mail chega no comercial
- [ ] Formulário de contato → e-mail chega
- [ ] `/onde-comprar` — mapa carrega e geolocalização funciona
- [ ] `/admin` — login com o novo usuário
- [ ] Admin: encaminhar cotação para vendedor dispara e-mail

---

## Atualizações futuras

Depois do primeiro deploy, publicar mudanças de código fica simples:

```bash
cd /var/www/tmac
php artisan down                 # modo manutenção

git pull origin master
composer install --no-dev --optimize-autoloader
php artisan migrate --force
npm ci && npm run build

php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:cache-components

php artisan up                   # volta ao ar
```

**Dica:** salve isso como `deploy.sh` na raiz do servidor e rode `bash deploy.sh`.

---

## Backup — configure logo no primeiro dia

```bash
# /etc/cron.daily/tmac-backup
#!/bin/bash
DATA=$(date +%Y%m%d)
DESTINO=/var/backups/tmac

mkdir -p $DESTINO
mysqldump -u tmac -pSUA_SENHA tmac_site | gzip > $DESTINO/db-$DATA.sql.gz
tar czf $DESTINO/uploads-$DATA.tar.gz -C /var/www/tmac/storage/app/public .

# Mantém 30 dias
find $DESTINO -name "*.gz" -mtime +30 -delete
```

```bash
chmod +x /etc/cron.daily/tmac-backup
```

---

## Problemas comuns

| Sintoma | Causa provável | Solução |
|---|---|---|
| Tela branca | `APP_DEBUG=false` escondendo erro | Veja `storage/logs/laravel.log` |
| Imagens de produto não aparecem | Falta o symlink | `php artisan storage:link` |
| CSS/JS não carregam | Assets não compilados | `npm run build` |
| "500 Server Error" após deploy | Cache antigo | `php artisan optimize:clear` e recriar |
| Erro de permissão em storage | Dono errado | `chown -R www-data:www-data storage` |
| Admin muito lento | OPcache desligado | Ativar no `php.ini` |
| Alterou `.env` e nada mudou | Config em cache | `php artisan config:clear && php artisan config:cache` |
