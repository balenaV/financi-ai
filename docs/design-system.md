# Design system — financiaí

## 1. Escopo, origem e precedência

Este documento registra a fundação visual definida no projeto **"Landing page e
design system"** do claude.ai/design (arquivo `Design System.dc.html`,
projeto `b69c2c4f-b293-42d5-9c72-ca76abc53ab7`) e a forma recomendada de
aplicá-la ao projeto Laravel financiai.

Esta versão **substitui integralmente** a fundação anterior, que era extraída
de um mockup React/shadcn (`artifacts/mockup-sandbox`) hoje removido do
repositório. Paleta, tipografia, mascote e padrão de interação de botão
mudaram por completo entre as duas versões — não é uma atualização
incremental da anterior.

O projeto claude.ai/design contém três arquivos relacionados; apenas o
primeiro foi lido para este documento:

1. `Design System.dc.html` — tokens, tipografia, componentes, bíblia do
   mascote Capí e assets (fonte deste documento).
2. `Landing Page.dc.html` — composição da landing page. Ainda não portado
   para este documento; ler antes de implementar a landing.
3. `Capi Poses.dc.html` — biblioteca de poses do Capí, briefings e prompt
   base de IA para gerar novas poses. Ainda não portado; ler antes de
   encomendar novas ilustrações do personagem.

**Fonte:** `DesignSync` (`get_project`, `list_files`, `get_file`) sobre o
projeto acima, em 2026-08-03.

### 1.1 Status de implementação

O código atual (`resources/css/app.css`) ainda implementa a fundação
**anterior**: Instrument Sans, `--font-mono: 'JetBrains Mono'`, tokens
`--navy`/`--navy-light` e escala `primary-*` em verde-azulado. Nenhuma classe
Tailwind, componente Blade ou asset deste documento foi implementado ainda.
Tratar este documento como especificação-alvo, não como o estado atual do
CSS — confirmar sempre em `resources/css/app.css` antes de assumir que um
token já existe no código.

### 1.2 Nome e mascote

- **Nome (confirmado pelo usuário em 2026-08-03):** a marca é
  **`financiaí`** — com acento no "í" de "aí" como parte da assinatura, é a
  grafia vigente do wordmark visual. `README.md` e `CLAUDE.md` ainda usam
  `financi.ai`, e `docs/financiai-rebranding-context.md` usa `financiai`
  sem acento — essas fontes estão desatualizadas e não devem ser copiadas;
  `financiaí` é quem manda. `financiai` (sem acento) pode seguir como nome
  de pacote/domínio técnico, mas o wordmark e a copy voltada ao usuário
  devem usar `financiaí`.
- **Mascote (confirmado pelo usuário em 2026-08-03):** o mascote da marca é
  **Capí**, derivado de uma capivara (ver seção 10), com anatomia, paleta e
  regras de pose próprias. `docs/financiai-rebranding-context.md` ainda
  descreve uma onça-pintada estilizada como símbolo — essa parte do
  documento está superada e não deve ser usada; não copiar o conceito de
  onça-pintada para código, assets ou outros docs.

## 2. Princípios visuais

- **Editorial e quente:** fundo creme (`#FFF6E6`), nunca branco puro como
  base; cards em branco puro para contraste.
- **Preto para estrutura:** títulos, contornos, bordas fortes.
- **Verde para marca e ação:** CTAs, foco, positivos, o "aí" do wordmark.
  Verde nunca representa valor negativo ou erro.
- **Caramelo e marrom pertencem ao Capí**, não a estados semânticos do
  produto — não reutilizar essas cores em badges/alerts.
- **Botões primários e de destaque usam "hard shadow"** (ver 7.2): contorno
  e sombra sólida deslocada, sem blur, como assinatura de interação. Cards,
  links e controles secundários nunca usam esse efeito.
- Layout mobile-first, grids `auto-fit`, tipografia em `clamp()`, sem
  scroll horizontal.

**Fonte:** `Design System.dc.html`, cabeçalho e seção "Cores da marca".

## 3. Paleta

### 3.1 Cores da marca

| Nome | Hex | Uso |
|---|---|---|
| Creme | `#FFF6E6` | Fundo editorial principal |
| Verde floresta | `#137A4A` | CTAs e elementos principais |
| Verde folha | `#38C172` | Progresso, positivos, destaques |
| Caramelo | `#D07A2A` | Pelagem do Capí e ilustração — não usar em UI de produto |
| Marrom escuro | `#6A4A20` | Focinho e sombras da ilustração do Capí |
| Preto/carvão | `#111111` | Títulos, contornos, estrutura |

### 3.2 Tokens de superfície

| Token | Light | Dark | Uso |
|---|---|---|---|
| `--bg` | `#FFF6E6` | `#111310` | Fundo da aplicação |
| `--surface` | `#FFFFFF` | `#1A1D18` | Cards e painéis |
| `--surface-2` | `#FBF4E4` | `#212520` | Superfícies alternadas, hover de link/nav |
| `--fg` | `#111111` | `#FFF6E6` | Texto principal |
| `--muted` | `#5B5A54` | `#A9A79C` | Texto secundário |
| `--accent` | `#137A4A` | `#38C172` | Ação principal (inverte peso entre temas) |
| `--accent-2` | `#38C172` | `#137A4A` | Destaque secundário (inverte peso entre temas) |

`--accent`/`--accent-2` trocam de valor entre claro e escuro — no escuro o
verde mais claro (`#38C172`) vira a cor de ação primária, para manter
contraste sobre `#111310`.

Além desses, a interação de botão usa dois tokens lógicos citados na fonte
mas sem hex explícito: `var(--hard)` (cor da hard shadow — `#111111` no
claro, `#FFF6E6` no escuro) e `var(--inv-bg)`/`var(--inv-fg)` (par de
inversão do botão secundário — preto/branco no claro, creme/escuro no
escuro). Definir esses tokens explicitamente ao implementar.

### 3.3 Cores funcionais

| Nome | Hex | Uso |
|---|---|---|
| Info | `#2F6FED` | Mensagem informativa |
| Planejado | `#7A5AF8` | Estados de planejamento/IA |
| Atenção | `#E08A1E` | Vencimento, alerta |
| Erro | `#B3261E` | Despesa, erro, exclusão |
| Neutro | `#8A8880` | Estado neutro/desabilitado |

Não há, na fonte lida, uma escala 50/100/600/700 por cor funcional como na
fundação anterior — apenas um valor sólido por cor. Badges de status
(seção 8.3) usam esse valor sólido em opacidade reduzida como fundo (ex.:
`rgba(179,38,30,0.10)`), não uma escala de tokens separada.

## 4. Tipografia

Família única: **Figtree** (pesos 400/500/600/700/800/900), carregada via
Google Fonts na fonte de referência (`fonts.googleapis.com`), fallback
`system-ui, sans-serif`. A aplicação real já hospeda fontes localmente
(`@fontsource/instrument-sans`); ao migrar, preferir um pacote
`@fontsource/figtree` local em vez de depender do Google Fonts em produção.

**Sem fonte monoespaçada para números financeiros.** A fundação anterior
especificava JetBrains Mono para valores monetários; a fonte atual usa
Figtree normal com `font-variant-numeric: tabular-nums` (ver escala
"Número" abaixo). `ui-monospace, monospace` só aparece para hex codes e
nomes de tokens dentro da própria página de documentação, não como token de
produto.

| Estilo | Tamanho/altura | Peso | Tracking | Observação |
|---|---|---|---|---|
| Display | `62px`/`1.03` (responsivo `clamp(32px, 4.6vw, 54px)`) | 800 | `-0.04em` | Hero |
| H2 | `42px`/`1.1` (responsivo `clamp(26px, 3.4vw, 40px)`) | 800 | `-0.035em` | Seções |
| H3 | `21px`/`1.25` | 750 | `-0.025em` | Subtítulos |
| Body L | `17px`/`1.6` | 400 | — | Texto de destaque, cor `--muted` |
| Body | `15px`/`1.55` | 400 | — | Texto padrão, cor `--muted` |
| Caption | `13px`/`1.4` | 600 | `0.06em`, uppercase | Rótulos |
| Número | `34px` | 800 | `-0.03em` | `font-variant-numeric: tabular-nums` |

**Fonte:** `Design System.dc.html`, seção "Tipografia".

## 5. Espaçamento

Escala base `4px`:

| Token | Valor |
|---|---|
| `space-1` | `4px` |
| `space-2` | `8px` |
| `space-3` | `12px` |
| `space-4` | `16px` |
| `space-6` | `24px` |
| `space-8` | `32px` |
| `space-12` | `48px` |
| `space-18` | `72px` |

A fonte não pula direto de `space-4` a `space-6` por acaso nem define
`space-5`/`space-10`/`space-14`/`space-16` — a escala documentada tem esses
saltos. Não inventar tokens intermediários sem necessidade real.

## 6. Raios e sombras

### 6.1 Raios

| Valor | Uso |
|---|---|
| `8px` | Itens compactos |
| `10–12px` | Botões e inputs |
| `14–16px` | Cards |
| `999px` | Badges, chips — pílulas reservadas a esses casos |

### 6.2 Sombras

Apenas dois níveis documentados:

- **Sem sombra** — estado padrão da maioria das superfícies.
- **`shadow-sm`** — `0 1px 2px rgba(17,17,17,0.06)`, usada em cards padrão e
  no header flutuante.

Sombras mais pesadas com blur não existem neste sistema — profundidade vem
da hard shadow (seção 7.2), que é sólida e sem blur, não de uma escala
`shadow-md/lg/xl` como na fundação anterior.

## 7. Estados interativos

### 7.1 Geral

- **Hover em card:** borda sobe para `rgba(17,17,17,0.22)` e o card desloca
  `-2px` no eixo Y. Nunca ganha sombra pesada.
- **Foco:** borda verde (`--accent`) + anel `rgba(56,193,114,0.18)` de
  `4px`, `outline: none` substituído pelo anel.
- **Link/nav:** cor `--muted` → `--fg`, fundo `--surface-2`, raio `10px`.
- **Desabilitado:** fundo `rgba(17,17,17,0.06)`, texto
  `rgba(17,17,17,0.35)`, sem borda.

### 7.2 Hard shadow — assinatura de interação dos botões

Este é o padrão de interação mais distintivo da fonte e não existia na
fundação anterior:

- **Com hard shadow** (botão primário, botão de destaque, cards de plano):
  contorno e sombra sólida em `var(--hard)` — `#111111` no claro,
  `#FFF6E6` no escuro. No hover: `transform: translate(-3px, -3px)` +
  `box-shadow: 5px 5px 0 var(--hard)`, transição `140ms`. Botões dentro do
  header flutuante usam deslocamento menor (`2–3px`) em vez de `5px`, para
  caber no espaço reduzido.
- **Sem hard shadow** (links de navegação, toggle de tema, ghost, botão
  pequeno, FAQ, chips): apenas mudança de fundo/borda, sem deslocamento.
- **Botão secundário/branco:** não usa hard shadow — inverte fundo/texto
  por completo em `180ms` via `var(--inv-bg)`/`var(--inv-fg)`.

Ao portar para Blade/Tailwind, implementar a hard shadow como utilitário
próprio (ex. `.btn-hard`) — não é uma sombra Tailwind padrão (`shadow-md`
etc.), é `box-shadow` sólida sem blur combinada com `translate`.

**Fonte:** `Design System.dc.html`, seção "Componentes" → "Hover e foco".

## 8. Especificação de componentes

Apenas os componentes abaixo foram especificados na fonte lida. Tabelas,
modais, tabs, tooltips, selects e skeletons **não têm especificação nesta
versão** — a fundação anterior tinha valores para eles, mas eram baseados na
paleta/tipografia antigas e não devem ser reaproveitados sem revisão; tratar
como pendente até que `Landing Page.dc.html` ou uma futura página de
componentes cubra esses casos.

### 8.1 Botões

| Variante | Fundo | Texto | Borda | Hover |
|---|---|---|---|---|
| Primário | `#137A4A` | `#FFF6E6` | `1.5px solid #111111` | bg `#38C172`, texto `#0B3B26`, hard shadow |
| Secundário | `#FFFFFF` | `#111111` | `1.5px solid #111111` | inversão total (bg `#111111`, texto `#FFFFFF`), `180ms` |
| Ghost | transparente | `#137A4A` | nenhuma | bg `rgba(56,193,114,0.12)` |
| Destaque | `#38C172` | `#0B3B26` | `1.5px solid #111111` | hard shadow, sem trocar bg |
| Desabilitado | `rgba(17,17,17,0.06)` | `rgba(17,17,17,0.35)` | nenhuma | — |
| Pequeno | `#137A4A` | `#FFF6E6` | nenhuma | — |
| Ícone (toggle de tema) | transparente | `--muted` | `1px solid rgba(17,17,17,0.12)` | bg `--surface-2`, texto `--fg` |

Padding padrão `13px 24px`, `font-size 15px`, `font-weight 700` (800 no
destaque); botão pequeno usa `9px 16px`, `13.5px`; ícone é `36×36px`. Raio
`12px` (botões padrão) ou `10px` (pequeno/ícone).

### 8.2 Campos

- Label: `13.5px`/`600`.
- Input: `padding 12px 14px`, `border 1.5px solid rgba(17,17,17,0.14)`,
  `radius 10px`, fundo `--surface`; foco: borda `--accent` + anel
  `0 0 0 4px rgba(56,193,114,0.18)`.
- Campo de valor monetário: wrapper com prefixo `R$` inline dentro da
  mesma borda; input interno sem borda própria.
- Erro: label e borda em `#B3261E`, texto de ajuda `12.5px` na mesma cor.

### 8.3 Cards e badges de status

- Card de saldo: rótulo `12.5px`/`600`/`--muted`; valor `26px`/`800`/
  `-0.03em`; variação `13px`/`600` em `#137A4A` (positiva).
- Badges de status — pílula (`radius 999px`, `padding 6px 12px`,
  `12.5px`/`700`):

  | Status | Texto | Fundo |
  |---|---|---|
  | Pago | `#137A4A` | `rgba(56,193,114,0.14)` |
  | Vence hoje | `#8A5A12` | `rgba(208,122,42,0.14)` |
  | Atrasado | `#B3261E` | `rgba(179,38,30,0.10)` |
  | Em breve | `--muted` | `rgba(17,17,17,0.06)` |

- Empty state: borda tracejada `1px rgba(17,17,17,0.18)`, `radius 14px`,
  ilustração do Capí (pose sentado) à esquerda, título `15px`/`700`,
  descrição `13.5px`/`--muted`/`1.45`.

### 8.4 Header flutuante

Barra fixa com "cápsula": `radius 14px`, padding `8px 8px 8px 14px`,
`backdrop-filter: blur(16px)`.

| | Light | Dark |
|---|---|---|
| Fundo | `rgba(255,246,230,0.85)` | `rgba(26,29,24,0.85)` |
| Borda | `1px solid rgba(17,17,17,0.10)` | `1px solid rgba(255,246,230,0.14)` |
| Sombra | `0 1px 2px rgba(17,17,17,0.06)` | `0 1px 2px rgba(0,0,0,0.35)` |

Contém logo (Capí `30px` + wordmark `19px`/`800`/`-0.035em`) à esquerda,
link "Entrar" (`14px`/`700`) e pílula "Começar grátis" (`padding 10px 16px`,
`radius 11px`, fundo `--accent`, texto creme) à direita.

## 9. Wordmark e logo

- Wordmark: `financi` + `aí` (acento obrigatório no "í"), peso `800`,
  tracking `-0.04em`. O "aí" usa `--accent` (`#137A4A` claro /
  `#38C172` escuro); o resto do texto usa `--fg`.
- **Nunca** usar ponto entre "financi" e "aí", nunca tipografia manuscrita,
  nunca capitalizar como "Financiaí" (sempre minúsculo).
- Símbolo (rosto do Capí) em tamanhos `16px`, `24px`, `32px`, `64px`.
- Proibido: distorcer/inclinar/aplicar contorno; trocar a cor do "aí" por
  algo fora da paleta; usar sobre fundo de baixo contraste; área de proteção
  menor que a altura do "f".
- Variante monocromática sobre fundo `#137A4A` sólido (wordmark inteiro em
  creme) existe para uso sobre a cor de marca.

**Fonte:** `Design System.dc.html`, seção "Logo e wordmark".

## 10. Capí — bíblia do personagem

Mascote da marca (nome derivado de "capivara" — não confundir com o
conceito de onça-pintada da seção 1.2). Todas as poses derivam do mesmo
modelo:

| Elemento | Regra |
|---|---|
| Cabeça | ~40% da altura total, arredondada, levemente oval |
| Olhos | Pequenos, pretos, acima do focinho |
| Focinho | Marrom `#6A4A20`, largo e central |
| Orelhas | Pequenas, arredondadas, topo lateral da cabeça |
| Braços/pernas | Curtos, mãos simples, espessura uniforme |
| Contorno | Preto uniforme, sem variação de peso entre poses |
| Moletom | Verde floresta, com o wordmark no peito |
| Wordmark no moletom | "financi" em creme `#FFF6E6` + "aí" em verde `#8FF0B4`, sempre com acento |
| Coturno | Preto fosco, curto, cadarço simples, sem insígnias |

### 10.1 Paleta da pelagem

| Nome | Hex |
|---|---|
| Pelagem | `#E8912B` |
| Sombra | `#C97A1E` |
| Focinho | `#8A5A2B` |
| Moletom | `#1E9B47` |
| Wordmark creme | `#FFF6E6` |
| Wordmark verde (blusa) | `#8FF0B4` |
| Contorno | `#111111` |

Nota: os hex de pelagem/focinho aqui (`#E8912B`/`#8A5A2B`) diferem
levemente dos hex "Caramelo"/"Marrom escuro" da paleta de marca (seção
3.1, `#D07A2A`/`#6A4A20`) — a fonte usa os dois pares em contextos
diferentes (marca geral vs. render do personagem) sem reconciliar
explicitamente. Confirmar com o usuário qual par é autoritativo antes de
codificar como token único, em vez de escolher um lado silenciosamente.

### 10.2 Poses e critério de aceite

Mapeamento uso → asset, conforme a fonte:

| Uso | Arquivo |
|---|---|
| Logo | `capi-rosto.png` (657×639) |
| Hero | `capi-celular.png` (948×804) |
| CTA | `capi-comemorando.png` (831×881) |
| Agentes | `capi-apontando.png` (595×855) |
| Empty state | `capi-sentado.png` (1008×781) |

`capi-parado.png` (566×867) é o "modelo oficial" de referência (vista
frontal, moletom verde-floresta + coturnos), citado na seção de anatomia
mas sem um uso de produto atribuído na tabela de aceite.

O projeto também tem, sem dimensões nem uso documentados nesta leitura:
`capi-caminhando.png`, `capi-cofrinho.png`, `capi-costas.png`,
`capi-pensando.png`, `capi-prancheta.png`. A biblioteca completa de poses,
briefings de poses planejadas e o prompt base para gerar novas variações
com IA vivem em `Capi Poses.dc.html` — ler esse arquivo antes de encomendar
ou gerar novas poses do personagem.

## 11. Assets e responsividade

Assets do personagem em `assets/capi/*.png`, PNG transparente recortado.
Substituir por SVG editável antes de produção — a fonte marca isso como
pendência, não como decisão final.

Breakpoints validados na fonte (não os breakpoints padrão do Tailwind
`sm/md/lg/xl/2xl` — são pontos de teste específicos deste projeto):

`360px · 390px · 768px · 1024px · 1280px · 1440px · 1920px`

Padrões observados: grids `auto-fit` e tipografia em `clamp()`, sem
scroll horizontal; header vira cápsula compacta com drawer abaixo de
`940px`.

## 12. Riscos e pendências conhecidos

1. **Sem visão de dashboard/página funcional nesta leitura.** A fonte lida
   é só a página de design system; ela cobre fundação e componentes
   isolados, não composição de tela financeira real (KPIs, gráficos,
   listas de transação). Ler `Landing Page.dc.html` e, se existir, uma
   futura página de dashboard antes de migrar telas inteiras.
2. **Fonte e mono divergem do código atual.** Migrar de Instrument Sans
   para Figtree e remover a dependência de JetBrains Mono para números é
   uma mudança visível em todo o app, não incremental — planejar como
   etapa própria, não misturada com outros ajustes.
3. **Tabelas, modais, tabs, tooltips, selects e skeletons não têm
   especificação nova.** Não copiar os valores da fundação anterior (que
   usava outra paleta) por conveniência; esperar por uma fonte que os
   cubra ou pedir a especificação explicitamente antes de implementar.
4. **Divergência de hex entre paleta de marca e paleta da pelagem do Capí**
   (seção 10.1) — não reconciliar sem confirmação.
5. **Nome e mascote já confirmados pelo usuário** (seção 1.2): `financiaí` e
   Capí (capivara), respectivamente. `README.md`, `CLAUDE.md` e
   `docs/financiai-rebranding-context.md` ainda não foram atualizados para
   refletir isso — a descrição de onça-pintada nesse último está superada.
