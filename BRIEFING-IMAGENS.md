# Briefing de imagens e logos — Site TMAC Import

Guia completo de todos os formatos, dimensões e usos de imagens necessários para o site TMAC. Use este documento para solicitar peças à equipe de arte/design.

**Regras gerais que se aplicam a todas as imagens:**
- Formato preferencial: **WebP** (melhor compressão) com fallback **JPG** para fotos e **PNG** para logos com transparência
- SVG quando possível para logos (escala infinita, peso mínimo)
- Otimização: passar por TinyPNG, Squoosh ou similar antes de subir
- Densidade: trabalhar em **2x** (dobro das dimensões finais) para nitidez em telas Retina
- Cor: sRGB
- Espaço de cores: garantir contraste suficiente sobre fundos escuros (#1A1B1F) e claros (#F5F2EA)

---

## 1. IDENTIDADE DA MARCA TMAC

### 1.1 Logo principal TMAC (header e footer)
- **Arquivo atual:** `storage/images/logo-tmac.png`
- **Dimensões finais:** altura renderizada de **40px**
- **Arquivo entregue:** 240×80px (3x) em PNG transparente OU SVG
- **Variantes necessárias:**
  - Versão colorida (fundo claro do header)
  - Versão branca/invertida (fundo escuro do footer)
  - Versão monocromática preta (para impressão)
- **Variação no footer:** texto "TMAC Import" usa fonte display Archivo Black 30px

### 1.2 Favicon
- **Dimensões:** 32×32px, 16×16px, 192×192px (PWA), 512×512px (PWA)
- **Formato:** ICO + PNG + SVG
- **Apple touch icon:** 180×180px PNG
- **Manifest:** logo simples sem texto, ícone reconhecível

### 1.3 Open Graph image (compartilhamento em redes sociais)
- **Dimensões:** 1200×630px
- **Formato:** JPG (PNG se tiver transparência necessária)
- **Conteúdo:** logo TMAC + tagline "Atacado de peças para motocicleta"
- **Use:** uma versão por página principal (home, produtos, marcas, etc.) ou uma genérica

---

## 2. UNIVERSO TMAC — Marcas próprias

**4 linhas atuais:** Corami (laranja #E97B0B), LBJ (verde #006B2D), Atrox (vermelho terra #B83434), Motoled (azul #2EA0C8)

### 2.1 Logo de cada linha
- **Dimensões finais renderizadas:** altura de **36px no card da home**, **40px na listagem**
- **Arquivo entregue:** **400×120px** em PNG transparente (idealmente) ou SVG
- **Variantes:**
  - PNG com fundo transparente (logo da marca em cor própria)
  - O sistema já aplica `brightness-0 invert` automaticamente quando o card é de fundo escuro
- **Importante:** logo deve funcionar em fundo colorido (a cor da marca). Versão simplificada, alto contraste, sem efeitos

### 2.2 Foto de capa de cada linha (opcional, mas recomendado)
- **Dimensões:** **1200×1600px** (proporção 3:4 — aspect-[3/4])
- **Formato:** WebP ou JPG, 70-80% qualidade
- **Uso:** fundo do card (entra com `mix-blend-mode: multiply` em opacidade 18%, então fotos com bom contraste e elementos visíveis no centro funcionam melhor)
- **Estilo:** moody/industrial — produto da marca em ambiente, detalhe de peça, motor, ferramenta
- **Total:** 4 fotos (uma por linha)

---

## 3. MARCAS DISTRIBUÍDAS (marcas externas que a TMAC representa)

### 3.1 Logos das marcas parceiras
- **Onde aparece:** Carousel de marcas na home, página `/marcas`, detalhe de produto
- **Dimensões finais renderizadas:** **altura 36px** (carousel)
- **Arquivo entregue:** **300×100px** PNG transparente OU SVG
- **Variantes:**
  - Versão para fundo claro (preto/colorida)
  - Versão para fundo escuro (recebe filtro `brightness-0 invert` automaticamente, então qualquer PNG transparente funciona)
- **Total:** depende de quantas marcas você cadastra (Cobreq, Vedamotors, NGK, etc.)

---

## 4. HERO BANNERS DA HOME (carrossel principal)

### 4.1 Banner do hero principal
**Esse é o mais importante. Aparece em destaque na primeira dobra.**

- **Versão desktop:** **2400×1300px** (proporção 16:9 aprox., min-height 78vh)
- **Versão mobile:** **1080×1440px** (proporção 3:4 vertical, min-height 560-760px)
- **Formato:** WebP + fallback JPG, 70-80% qualidade
- **Safe zone (área onde NÃO pode ter elementos críticos):**
  - 40% da esquerda + 30% inferior ficam cobertos por overlay escuro e por texto/CTA
  - Conteúdo importante deve ficar à direita, parte superior/centro
- **Conteúdo sugerido:**
  - Moto profissional em cenário industrial
  - Mecânico trabalhando em close-up
  - Peças em destaque com profundidade
  - Foto wide de oficina/CD
- **Quantidade:** 3-5 banners para o carousel rotativo

### 4.2 Banda "Centro de Distribuição" (full-bleed entre seções)
- **Dimensões:** **2400×1200px** (proporção 2:1 ou wider)
- **Formato:** WebP/JPG
- **Conteúdo:** CD próprio, estoque, prateleiras com peças, esteira de expedição
- **Importante:** será coberta por overlay escuro 50-60%, então textura/atmosfera importam mais que detalhes finos

---

## 5. PÁGINAS INSTITUCIONAIS

### 5.1 Hero da página "Quem somos"
- **Dimensões:** **2400×1300px** (proporção 16:9 a 21:9)
- **Estilo:** wide, atmosférico, mostra a operação/CD/equipe
- **Min-height renderizado:** 560-760px

### 5.2 Image breaker (entre seções da Quem somos)
**Aparecem 2 imagens full-bleed quebrando o ritmo da página.**

- **Dimensões:** **2400×1100px** (proporção 16:7 aprox.)
- **Estilo:** moody, detalhe de motor / wide de oficina
- **Quantidade:** 2 imagens distintas

### 5.3 Foto "Cobertura nacional" (Quem somos, bloco representantes)
- **Dimensões:** **1400×1050px** (proporção 4:3)
- **Estilo:** moto em estrada / mapa estilizado / motociclista

### 5.4 Foto vertical "Catálogo TMAC" (Quem somos, bloco produtos)
- **Dimensões:** **1200×1500px** (proporção 4:5)
- **Estilo:** peças organizadas, vista superior, detalhe de produto

### 5.5 Hero "Contato"
- **Dimensões:** **2400×1100px** (proporção 16:7 aprox., min-height 360-520px)
- **Estilo:** equipe atendendo, mãos em telefone, atendimento

### 5.6 Hero "Ouvidoria"
- **Dimensões:** **2400×1000px**
- **Estilo:** mais neutro, profissional (gravata, papelaria, mesa de escritório, etc.)

### 5.7 Hero "Universo TMAC" (listagem)
- **Dimensões:** **2400×1200px**
- **Estilo:** abstrato/conceitual mostrando "marcas" — pode ser composição de peças coloridas, vista superior

### 5.8 Hero da página de detalhe de cada linha TMAC (Corami, LBJ, Atrox, Motoled)
- **Dimensões:** **2400×1400px** cada
- **Estilo:** alinhado à identidade visual da linha (cor da marca dominando o ambiente)
- **Quantidade:** 4 (uma por linha)

---

## 6. PRODUTOS (catálogo)

### 6.1 Imagem principal do produto
- **Dimensões mínimas:** **1000×1000px** (quadrado)
- **Dimensões recomendadas:** **1500×1500px**
- **Formato:** WebP/JPG fundo branco OU transparent PNG
- **Estilo:**
  - Fundo branco puro #FFFFFF OU cinza claro #F5F2EA
  - Produto centralizado, com sombra leve
  - Padding interno de 10-15% (não corar até a borda)
- **Renderizado em:** cards 200×200 a 400×400, página de detalhe até 800×800

### 6.2 Galeria adicional de produto
- Mesmas specs: 1500×1500px, fundo branco/transparente
- 2-6 fotos por produto (diferentes ângulos)

---

## 7. CATEGORIAS (já existem, opcional refazer)

### 7.1 Imagem de capa de categoria
- **Dimensões:** **800×800px** (quadrado)
- **Formato:** WebP/JPG ou PNG
- **Onde aparece:** grid de categorias na home (aspect-square)
- **Estilo:** representação simbólica da categoria (Motor → motor, Freios → pastilhas, etc.)
- **Recomendação:** mesmo estilo para todas (silhueta, foto isolada, ícone ilustrado — escolher um)
- **Quantidade:** 10 categorias raiz cadastradas

---

## 8. SUBCATEGORIAS (opcional)

- Subcategorias **não exibem imagem** atualmente nos filtros
- Se quiser exibir em página dedicada futura: 600×600px

---

## 9. REPRESENTANTES (perfil de vendedor)

- **Foto de perfil (opcional, não usada atualmente):** 400×400px quadrada
- **Caso queira adicionar futuramente:** estilo "headshot" profissional, fundo neutro

---

## 10. REDES SOCIAIS (footer + sidebar)

### 10.1 Ícones de redes sociais
- **Já estão no sistema** como SVGs inline no componente `x-site.social-icon`
- **Suporte atual:** Instagram, Facebook, WhatsApp, YouTube, TikTok, LinkedIn, X (Twitter), Telegram, Pinterest, Spotify, Threads, Website
- **Não precisa de arte** — já entregue no código

---

## 11. ÍCONES E ELEMENTOS GRÁFICOS

- **Ícones de interface:** já estão como SVGs inline (Lucide-style). Não precisa de arte.
- **Pictogramas categorias:** opcional, mas o site usa SVGs inline atualmente.

---

## RESUMO EXECUTIVO — Lista de entrega prioritária

Se quiser começar pelo essencial, esta é a ordem de prioridade:

| Prioridade | Item | Qtd | Dimensões |
|---|---|---|---|
| 🔴 Crítico | Logo TMAC (PNG transparente + SVG) | 1 + variantes | 240×80px @3x |
| 🔴 Crítico | Favicon completo | 1 set | 32/16/192/512 |
| 🔴 Crítico | Hero da home (desktop + mobile) | 3-5 banners | 2400×1300 + 1080×1440 |
| 🔴 Crítico | Logos Universo TMAC (Corami, LBJ, Atrox, Motoled) | 4 | 400×120px |
| 🟡 Alto | Imagens dos produtos cadastrados | varia | 1500×1500px fundo branco |
| 🟡 Alto | Hero "Quem somos" | 1 | 2400×1300 |
| 🟡 Alto | Foto de capa de cada linha TMAC | 4 | 1200×1600 (3:4) |
| 🟡 Alto | Logos das marcas parceiras (Cobreq, NGK, etc.) | varia | 300×100 PNG transparente |
| 🟢 Médio | Banda CD (full-bleed) | 1 | 2400×1200 |
| 🟢 Médio | Image breakers Quem somos | 2 | 2400×1100 |
| 🟢 Médio | Categorias capa | 10 | 800×800 |
| 🟢 Médio | Heros institucionais (Contato, Ouvidoria, Universo TMAC) | 3 | 2400×1100 |
| 🟢 Médio | Open Graph image | 1 | 1200×630 |
| 🔵 Baixo | Heros detalhe de linha TMAC | 4 | 2400×1400 |

---

## OBSERVAÇÕES TÉCNICAS PARA A ARTE

1. **Trabalhar em camadas separadas** — assim podemos pedir variantes (cor/preto/branco) facilmente
2. **PNG transparente é melhor que JPG fundo branco** para logos — permite uso em qualquer fundo
3. **Para fotos institucionais**, evitar texto sobreposto na foto — o site adiciona overlays escuros que podem ler dois textos sobrepostos
4. **Cores da identidade:**
   - Vermelho TMAC: `#E11D2A` (accent)
   - Azul TMAC: `#004DFF` (signal)
   - Preto TMAC: `#1A1B1F` (ink)
   - Bege fundo: `#F5F2EA` (bg)
   - Verde WhatsApp: `#25D366`
5. **Tipografia oficial:** Archivo Black (display), IBM Plex Sans (texto), IBM Plex Mono (técnico)
6. **Estilo fotográfico desejado:** industrial, moody, alto contraste, levemente dessaturado, com pontos de luz vermelha/azul ocasionais

---

## ARQUIVOS DE REFERÊNCIA (já no site, podem ser usados como inspiração)

As fotos atuais são placeholders do Unsplash. Quando substituir por fotos reais, mantenha aspect ratio e enquadramento similares para que o layout continue funcionando.
