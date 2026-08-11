# Paleta oficial e identidade visual — TMAC

Documento de referência com as paletas oficiais de cores, logos e tipografia extraídas do MIV (Manual de Identidade Visual) da TMAC e das marcas próprias.

---

## 1. TMAC (marca principal)

### Cores oficiais

| Cor | HEX | RGB | CMYK | Pantone | Uso no site |
|---|---|---|---|---|---|
| Azul TMAC | `#004F9F` | 0, 79, 159 | 100, 70, 0, 0 | 2145 C | **Signal DEFAULT** — CTAs secundários, links, destaques informativos |
| Azul escuro | `#003063` | 0, 48, 99 | 100, 70, 0, 50 | 2147 C | **Signal Dark** — hover, botões pressed |
| Azul mais escuro | `#001437` | 0, 20, 55 | 100, 70, 0, 80 | 2768 C | **Signal Darker** — profundidade, sombras |
| Vermelho TMAC | `#ED1C24` | 237, 28, 36 | 0, 100, 100, 0 | 2347 C | **Accent DEFAULT** — CTAs primários, alertas, destaques |
| Vermelho escuro | `#8B0304` | 139, 3, 4 | 0, 100, 100, 50 | 1815 C | **Accent Dark** — hover em CTAs vermelhos |

### Logos disponíveis

Todos em `storage/app/public/images/`:
- `logo-tmac.svg` / `logo-tmac.png` — logo colorido (vermelho + preto) para fundo claro (usado no header)
- `logo-tmac-white.svg` / `logo-tmac-white.png` — logo branco para fundo escuro (usado no footer)
- `logo-tmac-black.svg` — logo preto puro para impressão/mono

### Tipografia oficial TMAC

- **Display / títulos:** Nexa (variações Book / Bold / ExtraBold / Black)
- **Alternativa display:** League Gothic (Regular / CondensedRegular)

Ambas estão em `Materiais/TMAC/TIPOGRAFIA/`. **Nexa não está no Google Fonts** — precisa ser hospedada localmente ou substituída por alternativa (recomendado: manter Archivo como fallback, que já usamos).

---

## 2. Universo TMAC — Marcas próprias

### CORAMI (Linha de Motor)

| Cor | HEX | Pantone | Uso |
|---|---|---|---|
| Laranja Corami | `#DA5F06` | 2028 C | Principal / cards / destaques |
| Cinza escuro | `#414042` | 419 C | Texto secundário |
| Cinza claro | `#D1D3D4` | 427 C | Bordas / fundos |

**Logos:** `storage/app/public/images/brands/corami/logo.svg` (laranja), `logo-white.svg`

### LBJ (Linha Premium)

⚠️ Nome correto é **LBJ** (não "L3J" como estava antes).

| Cor | HEX | Pantone | Uso |
|---|---|---|---|
| Verde claro | `#41B97B` | 2414 C | Detalhes |
| Verde médio | `#259A62` | 2242 C | Elementos secundários |
| **Verde principal** | `#00814F` | 2422 C | **Cor da marca no site** |
| Verde escuro | `#0F784F` | 335 C | Hover / sombras |

**Logos:** `storage/app/public/images/brands/lbj/logo.svg` (vermelho), `logo-white.svg`

### ATROX (Linha de Acessórios)

| Cor | HEX | Pantone | Uso |
|---|---|---|---|
| Vermelho Atrox | `#BD192B` | 2035 C | Principal |
| Preto | `#000000` | 20-0194 TPM | Secundária |

**Logos:** `storage/app/public/images/brands/atrox/logo.svg` (vermelho), `logo-white.svg`

### MOTOLED (Linha de Iluminação)

| Cor | HEX | Pantone | Uso |
|---|---|---|---|
| **Azul Motoled** | `#009ED0` | 298 C | **Cor principal** |
| Branco | `#FFFFFF` | P 179-1 C | Fundos |
| Preto | `#000000` | 20-0194 TPM | Texto |

**Logos:** `storage/app/public/images/brands/motoled/logo.svg` (azul), `logo-white.svg`

---

## 3. Tipografias por marca

| Marca | Display | Corpo |
|---|---|---|
| TMAC | Nexa (Black/ExtraBold) + League Gothic | Nexa Book |
| Atrox | Gobold (Bold/Regular/Light/Thin) | Sora (variable) |
| Corami | *(não fornecido — usar padrão TMAC)* | *(não fornecido)* |
| LBJ | *(não fornecido)* | *(não fornecido)* |
| Motoled | *(não fornecido)* | *(não fornecido)* |

**Recomendação atual do site:**
- Display: **Archivo Black** (Google/Bunny Fonts) — visualmente similar à Nexa Black
- Texto: **IBM Plex Sans**
- Técnico/mono: **IBM Plex Mono**

Se quiser migrar 100% pra Nexa, é preciso hospedar as fontes localmente (`.otf` já estão no projeto em `Materiais/TMAC/TIPOGRAFIA/NEXA/`).

---

## 4. Aplicação prática no site

### O que já está aplicado

- ✅ Tailwind config atualizado com **vermelho `#ED1C24`** e **azul `#004F9F`** oficiais
- ✅ Logo TMAC oficial (SVG + PNG, colorido + branco) em `storage/images/`
- ✅ Header usando `logo-tmac.svg` com fallback PNG
- ✅ Cores das 4 marcas Universo TMAC corrigidas com os hex oficiais no `TmacBrandsSeeder`
- ✅ Slug LBJ (era `l3j`) corrigido no seeder + migration de correção automática

### O que ainda pode ser feito

- 🟡 Logos das 4 marcas próprias já estão em `storage/images/brands/{marca}/` — cadastrar via admin ou vincular no seeder (já feito no `logo_path`)
- 🟡 Tipografia Nexa (opcional): hospedar as `.otf` como `@font-face` local
- 🟡 Ampliar sistema de cores com as variações escuras oficiais (`accent.dark`, `signal.dark`, `signal.darker`)

### Como aplicar mudanças

Depois de alterar cores no `tailwind.config.js`:
```bash
npm run build
```

Depois de alterar o seeder das marcas:
```bash
php artisan db:seed --class=TmacBrandsSeeder
```

---

## 5. Referências

- `Materiais/TMAC/MIV BÁSICO TMAC.pdf` — Manual de Identidade Visual oficial
- `Materiais/TMAC/CORES/PALETA DE CORES TMAC.pdf` — paleta detalhada
- `Materiais/{ATROX,CORAMI,LBJ,MOTOLED}/Paleta de Cores/` — paletas das linhas próprias
- `Materiais/{marca}/LOGOS/` — logos em EPS + PNG + SVG (branco, preto, colorido)
