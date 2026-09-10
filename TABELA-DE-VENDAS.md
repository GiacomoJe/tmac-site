# ÁREA DO CLIENTE — TABELA DE VENDAS

Módulo novo dentro do site: uma área **protegida por login**, só para clientes
cadastrados, onde eles consultam a tabela de preços por estado (com promoções
e status de estoque) e montam o próprio pedido — substitui o app avulso do
Google Apps Script, agora integrado ao site e com os pedidos registrados no
painel.

O cadastro automático de clientes (o cliente criar a própria conta sozinho)
**fica para depois**, como combinado. Por enquanto o time comercial cria cada
acesso manualmente pelo painel.

---

## 1. Instalar as dependências que faltam

A geração dos `.xlsx` de download (tabela em branco / pedido) usa a
PhpSpreadsheet. A LEITURA do `.xlsx` publicado (a parte pesada, com o
catálogo inteiro) usa o OpenSpout — uma biblioteca diferente, feita
especificamente pra ler arquivos grandes sem estourar memória (veja a seção
"Se der erro ao publicar uma tabela grande" mais abaixo). Rode as duas:

```bash
composer require phpoffice/phpspreadsheet openspout/openspout
```

## 2. Rodar as migrations novas

```bash
php artisan migrate
```

Isso cria: `clientes`, `sales_price_lists`, `sales_states`,
`sales_catalog_items`, `sales_orders`, `sales_order_items`.

## 3. Limpar cache de config (se necessário)

Como foi criado o arquivo `config/auth.php` (guard novo `cliente`), se o app
já rodou antes e tem config em cache:

```bash
php artisan config:clear
```

## 4. Recompilar o CSS

Foram adicionadas duas classes novas em `resources/css/app.css`
(`.btn-chip` / `.btn-chip--on`) usadas na Área do Cliente:

```bash
npm run build
# ou, em desenvolvimento: npm run dev
```

---

## Como fica o fluxo

### Publicar a tabela (você, semanalmente)

1. Entre no painel: `/admin`
2. Menu **Tabela de Vendas → Publicar tabela**
3. Envie o `.xlsx` (mesmo formato de sempre — não mudou nada na planilha)
4. Clique em **Publicar tabela**

O arquivo é lido, todo o cálculo de preço por estado, promoção (NET / SP /
EXT), exceção de câmara de ar (sempre pelo preço da aba **BA**) e status de
estoque é recalculado — igual ao app antigo — e fica gravado no banco. A
versão anterior não é apagada, fica guardada como histórico.

> **Não renomeie as abas nem mova as colunas** da planilha. A leitura segue
> exatamente a mesma estrutura documentada no app antigo (`Config`,
> `Tabela - Modelo`, `Tabela - Estoque`, `BA`, uma aba por estado, e as 6
> abas de promoção). Se o nome do período de promoção mudar (hoje é
> "AGOSTO"), ajuste em
> `app/Services/SalesTable/PriceListImporter.php`, constante `PROMO`.

### Se der erro ao publicar uma tabela grande (planilha real, ~30 abas)

A primeira versão desta tela lia o `.xlsx` com a PhpSpreadsheet e enviava o
arquivo pelo componente de upload do Livewire (Filament `FileUpload`). Os dois
pontos foram trocados depois de testes com uma planilha real (~8&nbsp;MB,
~28 tabelas/estado, mais de 100 mil linhas de catálogo):

1. **Leitura**: agora usa o **OpenSpout**, que lê o arquivo célula por célula
   de forma sequencial (sem montar o arquivo inteiro em memória), em vez da
   PhpSpreadsheet — que mesmo lendo "uma aba de cada vez" ainda reabria o
   workbook inteiro (shared strings, estilos etc.) a cada aba. Isso derrubou o
   uso de memória de forma bem drástica; não deve mais ser necessário um
   `memory_limit` muito alto (256M já é suficiente na prática — o código ainda
   sobe automaticamente pra esse valor se o `php.ini` estiver mais baixo).
2. **Upload**: o formulário de "Publicar tabela" agora é um `<form>` comum
   (não é mais um componente Livewire). Um arquivo grande no componente
   `FileUpload` do Livewire é enviado em **duas** requisições (uma só pra
   gravar o arquivo temporário, antes mesmo de clicar em "Publicar"), e é
   nessa etapa extra que apareciam falhas silenciosas. Um formulário comum
   manda o arquivo numa única requisição, do jeito mais padrão possível.

Se **mesmo assim** continuar dando erro 500 ao publicar uma planilha grande,
o problema provavelmente não está mais no PHP/Laravel (que agora usa bem
menos memória e loga qualquer erro em
`storage/logs/laravel.log`) — provavelmente é o **Apache/XAMPP rejeitando a
requisição antes dela chegar no Laravel**. Vale conferir, nesta ordem:

- **`storage/logs/laravel.log` não tem NENHUMA linha nova** no horário exato
  da tentativa? Isso é um sinal forte de que o PHP nem chegou a rodar o
  código da importação — o erro está uma camada abaixo.
- Abra `C:\xampp\apache\logs\error.log` (não é o log do Laravel, é o log do
  próprio Apache) e veja se tem algo registrado bem no horário da tentativa.
- Confirme que o Apache foi **reiniciado** depois de editar o `php.ini` — é
  comum editar o arquivo certo mas esquecer de reiniciar o Apache pelo painel
  do XAMPP, e aí o processo continua rodando com o limite antigo.
- Acesse `http://localhost:8001` com um arquivo temporário de `phpinfo()` (ou
  rode `php -i | findstr "memory_limit\|Loaded Configuration"` no terminal do
  XAMPP) pra confirmar que o `php.ini` que o Apache está realmente usando é o
  mesmo que você editou — em algumas instalações existe mais de um `php.ini`
  no PATH.

### Login de teste da Área do Cliente

Não existe autocadastro (combinado desde o início), então num ambiente novo
não tem nenhum jeito de entrar em `/area-cliente/entrar` até alguém criar uma
conta. Pra já ter uma conta de teste pronta, adicionei ao `DatabaseSeeder`
(mesmo padrão do admin `admin@tmacimport.com.br` que você já usa):

- **E-mail:** `cliente@tmacimport.com.br`
- **Senha:** `tmac@cliente`

Se você já rodou `php artisan migrate` antes (e não quer rodar `db:seed`
de novo, que executaria todos os seeders da lista), crie só essa conta agora
com:

```bash
php artisan tinker
```

e cole (uma linha só, aperte Enter):

```php
App\Models\Cliente::firstOrCreate(['email' => 'cliente@tmacimport.com.br'], ['name' => 'Cliente Teste', 'company' => 'Cliente Teste Ltda', 'uf' => 'SP', 'tabela_padrao' => 'SP', 'password' => Hash::make('tmac@cliente'), 'is_active' => true]);
```

Em produção, troque a senha dessa conta (ou desative/apague) e cadastre os
clientes reais pelo painel — próximo tópico.

### Cadastrar um cliente (acesso à Área do Cliente)

1. Painel → **Tabela de Vendas → Clientes (acessos)** → **Novo**
2. Preencha nome, e-mail (é o login), senha, empresa/CNPJ e, se quiser, a
   **tabela padrão** (estado) — é a que abre pré-selecionada para ele.
3. Avise o cliente do e-mail/senha por fora (WhatsApp, telefone etc.) — não
   existe "esqueci minha senha" ainda, é o próximo passo natural quando
   entrarmos no autocadastro.

### O cliente usa a área

- Login em **`/area-cliente/entrar`** (tem um link "Área do Cliente" no menu
  do site, desktop e mobile).
- Escolhe a tabela/estado, busca produtos, define quantidade e desconto por
  item (itens em promoção têm preço final e não aceitam desconto — igual
  antes), acompanha o total em tempo real.
- **Baixar tabela**: gera um `.xlsx` em branco da tabela escolhida, com
  fórmulas prontas (igual ao app antigo).
- **Salvar pedido**: grava o pedido no banco (aparece em **Tabela de Vendas →
  Pedidos** no painel) *e* baixa o `.xlsx` do pedido, com os dados do
  cliente e os itens.

---

## Arquivos principais

| O quê | Onde |
|---|---|
| Regra de importação/cálculo da planilha (leitura via OpenSpout) | `app/Services/SalesTable/PriceListImporter.php` |
| Geração dos `.xlsx` (tabela em branco / pedido, via PhpSpreadsheet) | `app/Services/SalesTable/SalesTableExporter.php` |
| Tela do cliente (busca + pedido) | `app/Livewire/Cliente/TabelaVendas.php` + `resources/views/livewire/cliente/` |
| Login do cliente | `app/Http/Controllers/Cliente/AuthController.php` |
| Guard de autenticação novo (`cliente`) | `config/auth.php` |
| Painel: tela de publicar tabela | `app/Filament/Pages/PublicarTabelaVendas.php` |
| Painel: recebe o upload (form comum, não Livewire) | `app/Http/Controllers/Admin/PublicarTabelaVendasController.php` |
| Painel: cadastro de clientes | `app/Filament/Resources/ClienteResource.php` |
| Painel: pedidos recebidos | `app/Filament/Resources/SalesOrderResource.php` |

## O que fica para depois (combinado)

- Autocadastro do cliente (formulário público de "criar minha conta").
- Recuperação de senha ("esqueci minha senha") para clientes.
- Direcionar pedido para representante por UF (a tabela `sales_orders` já
  guarda a UF/tabela — dá pra plugar no `QuoteRouter` existente quando
  quiser).
