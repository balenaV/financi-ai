# Design system — financiai

## 1. Escopo, origem e precedência

Este documento registra a fundação visual do financiai e como ela está
implementada em `resources/css/app.css`. Ele substitui uma versão anterior
baseada num mockup shadcn/Replit (`financi-ai-design-system`) — essa base foi
descontinuada e não deve mais ser usada como referência.

A fonte de referência atual é o projeto de design em Claude
(`Landing page e design system`, arquivos `Design System.dc.html`,
`Dashboard.dc.html`, `Importar Extrato.dc.html`, `Landing Page.dc.html`,
`Login.dc.html`, `Capi Poses.dc.html`). Esse projeto é HTML com estilos
inline, num formato próprio daquele ambiente — não deve ser copiado
literalmente; ele é lido como especificação visual e reescrito nos padrões
Blade/Tailwind do projeto.

Existe também `docs/financiai-rebranding-context.md`, um brief de
posicionamento de marca anterior ao projeto de design atual (ainda com um
símbolo de onça-pintada e Instrument Sans). O conceito visual evoluiu desde
então — mascote Capí (uma capivara), paleta e tipografia atuais — e o projeto
de design em Claude é a referência que prevalece em qualquer divergência.
O brief de rebranding continua útil como contexto de tom de voz e
posicionamento de marca, não como especificação de tokens.

**Regras que não podem quebrar:**

- Caramelo e marrom pertencem exclusivamente ao mascote Capí — nunca a
  estados, badges ou componentes de UI. Por isso `--mascot-caramelo` e
  `--mascot-marrom` existem como tokens separados, deliberadamente fora do
  bloco `@theme inline` que gera utilitários Tailwind (`bg-*`, `text-*`).
  Não crie um utilitário Tailwind para essas cores.
- Verde nunca representa valor negativo, erro ou estado de atenção.

## 2. Princípios visuais

- Fundo creme como identidade (`#FFF6E6`), cards brancos.
- Carvão quase preto para estrutura: tipografia, contornos, bordas fortes.
- Dois verdes de marca: verde floresta (`#137A4A`) como ação/base; verde
  folha (`#38C172`) como hover/destaque/positivo. Não existe uma cor
  "success" separada — sucesso reusa o verde de marca.
- Bordas quase retas, sem sombra suave em lugar nenhum da interface. A única
  sombra é a sombra dura (`5px 5px 0`), reservada à ação primária.
- Neutros quentes (a família `slate-*` do Tailwind foi sobrescrita para uma
  escala cinza-quente, coerente com o fundo creme — nunca cinza-azulado).
- Estados nunca devem depender só da cor; incluir texto, ícone ou outro
  indicador perceptível.

## 3. Paleta

### 3.1 Marca

| Cor | Hex | Uso |
|---|---|---|
| Creme | `#FFF6E6` | Fundo editorial principal |
| Verde floresta | `#137A4A` | Ação principal (estado de repouso, tema claro) |
| Verde folha | `#38C172` | Hover/destaque/positivo (estado de repouso, tema escuro) |
| Caramelo | `#D07A2A` | Exclusivo do mascote Capí |
| Marrom escuro | `#6A4A20` | Exclusivo do mascote Capí (focinho/sombra) |
| Carvão | `#111111` | Títulos, contornos, estrutura |

### 3.2 Tokens de superfície e texto

| Token | Light | Dark | Uso |
|---|---|---|---|
| `--bg-base` | `#FFF6E6` | `#111310` | Fundo da aplicação |
| `--bg-alt` | `#FBF4E4` | `#212520` | Seções alternadas |
| `--bg-card` / `--bg-input` | `#FFFFFF` | `#1A1D18` | Cards, inputs |
| `--text-primary` | `#111111` | `#FFF6E6` | Títulos, corpo principal |
| `--text-secondary` | `#5B5A54` | `#A9A79C` | Descrições, labels |
| `--text-tertiary` | `#8A8880` | `#7D7B70` | Metadados, placeholders (= Neutro funcional) |
| `--border-subtle` | `rgb(17 17 17 / .10)` | `rgb(255 246 230 / .10)` | Cards e divisores |
| `--border-default` | `rgb(17 17 17 / .14)` | `rgb(255 246 230 / .16)` | Inputs e controles |
| `--border-strong` | `#111111` | `#FFF6E6` | Ênfase, hover de botão secundário |
| `--hard` | `#111111` | `#FFF6E6` | Cor da sombra dura e da borda 1.5px dos botões |

**Fonte:** `Design System.dc.html`, seção "Cores" (`tokensLight`/`tokensDark`).

### 3.3 Verde de marca — `primary`/`brand`/`success`/`accent`

| Token | Valor | Origem |
|---|---|---|
| `--brand-400` | `#38C172` | Exato — verde folha |
| `--brand-600` | `#137A4A` | Exato — verde floresta |
| `--brand-700` | `#0F6B40` | Exato — hover do CTA do header no próprio `Design System.dc.html` |
| `--brand-50/100/200/500/800/900` | interpolados | Degradê construído a partir dos dois tons acima; não vêm literalmente da referência |

Em tema escuro, `--brand-400` (folha) passa a ser o verde dominante e
`--brand-600` (floresta) o de apoio — o botão primário troca de base
(ver §7.1). Os utilitários Tailwind `success-*` e `accent-*` apontam para
esta mesma escala — não existe mais uma cor turquesa separada para
"sucesso"; o badge "Pago" do próprio design de referência já usa o verde de
marca, não um verde diferente.

### 3.4 Cores funcionais

| Semântica | Token base | Hex | Origem |
|---|---|---|---|
| Info | `--blue-600` | `#2F6FED` | Exato |
| Planejado | `--violet-500` | `#7A5AF8` | Exato |
| Atenção | `--orange-600` | `#E08A1E` | Exato |
| Erro | `--red-700` | `#B3261E` | Exato — mesmo tom do badge "Atrasado" na referência |
| Neutro | `--text-tertiary` / `--neutral-500` | `#8A8880` | Exato |

Os utilitários Tailwind `danger-*`/`warning-*` e os padrões nativos
`red-*`/`amber-*` foram unificados (mesma escala) para que qualquer uso
direto de `text-red-700` ou `bg-amber-50` já existente no código renderize a
cor certa sem precisar trocar a classe.

> **Nota de implementação:** o mockup estático usa um tom de caramelo
> (`rgba(208,122,30,…)`) no badge de exemplo "Vence hoje", o que conflita com
> a regra "caramelo é exclusivo do mascote". A implementação usa Atenção
> (`#E08A1E`) para esse estado — badges com tom `warning`/`pending`/`planned`
> em `x-badge` já resolvem para essa cor, não para o caramelo.

### 3.5 Roxo — Planejado / IA

| Escala | Valor |
|---|---|
| `--violet-500` | `#7A5AF8` (exato) |
| `--violet-600` | `#6841E0` |
| `--violet-700` | `#5A34C9` |
| `--violet-900` | `#2E1A6B` |

Usado em gráficos, estados "planejado" e elementos de IA — nunca compete com
o verde de marca.

### 3.6 Secondary / navy

`--navy` (`#102A43`) e `--navy-light` (`#1E3A5F`) não têm equivalente na
referência nova; foram mantidos sem alteração (uso em gráficos/apoio
institucional) até que surja uma decisão explícita para essa família.

### 3.7 Neutros (`slate-*`)

A escala padrão do Tailwind (`slate-50`…`slate-950`, cinza-azulada) foi
sobrescrita para uma escala cinza-quente, ancorada no Neutro funcional
(`#8A8880`), coerente com o fundo creme. Qualquer uso existente de
`text-slate-500`, `bg-slate-100` etc. já renderiza com a nova escala sem
precisar trocar a classe.

## 4. Tipografia

- Família: **Figtree** (pesos 400–900), self-hosted via `@fontsource/figtree`
  — não carregar via Google Fonts CDN, mesma convenção que já existia para
  Instrument Sans.
- Números financeiros continuam com `font-variant-numeric: tabular-nums`
  quando fizer sentido (ver `<x-money>`), mono ainda é `JetBrains Mono`.

| Papel na referência | Tamanho/peso/tracking | Token do projeto mais próximo |
|---|---|---|
| Display | 54–62px / 800 / `-0.04em` | `text-5xl` (`--text-5xl: 3.375rem`, `tracking-tightest`) |
| H2 | 40–42px / 800 / `-0.035em` | `text-4xl` (`--text-4xl: 2.625rem`, `tracking-tighter`) |
| H3 | 21px / 750 / `-0.025em` | `text-xl` (`--text-xl: 1.3125rem`, peso 750 embutido no token, `tracking-tight` padrão do Tailwind já é `-0.025em`) |
| Body L | 17px / 400 / 1.6 | `text-lg` (`--text-lg: 1.0625rem`) |
| Body | 15px / 400 / 1.55 | `text-base` (`--text-base: 0.9375rem`, já batia exatamente antes desta revisão) |
| Caption | 13px / 600 / uppercase / `0.06em` | `text-sm` + `font-semibold uppercase tracking-wide` |
| Número | 34px / 800 / tabular | `text-3xl`/`text-4xl` + `tabular-nums` conforme o contexto |

`tracking-tighter` (`-0.035em`, sobrescrito) e `tracking-tightest`
(`-0.04em`, novo) foram adicionados ao `@theme` para cobrir H2/Display sem
precisar de classes arbitrárias em cada view.

## 5. Espaçamento

Sem alteração — a unidade base do projeto (`--spacing: 0.25rem` = 4px) já
batia exatamente com a escala 4px da referência (`space-1`…`space-18`).

## 6. Raios e sombras

| Token | Valor | Observação |
|---|---|---|
| `--radius-sm` | `8px` | era 6px |
| `--radius-md` | `11px` | era 10px; botões e inputs |
| `--radius-lg` | `16px` | inalterado; cards |
| `--radius-xl` / `--radius-2xl` | `20px` / `28px` | inalterados, sem contradição com a referência |
| pílula (badges, toggle) | `rounded-full` nativo do Tailwind | já cobre os "999px" da referência |

**Sombras — mudança de filosofia, não só de valor:**

- `--shadow-card` agora é `none`. Cards (`.surface`) nunca têm sombra —
  apenas borda. Isso vale para `financial-card`, `empty-state`,
  `page-header` e qualquer outro consumidor de `.surface`.
- `--shadow-hard: 5px 5px 0 0 var(--hard)` é a **única** sombra que aparece
  em qualquer lugar da interface, e só em hover de ação primária
  (`.btn-primary`, `.btn-destaque`) — nunca em cards, nunca em repouso.
- `--shadow-xs/sm/md/lg/xl` (soft, com blur) continuam definidos e em uso
  pelo shell existente (dropdowns, `floating-tab`, `app-action-cluster`,
  `auth-social-button`, cartão de login). Retirá-los é trabalho de
  composição de página (sidebar, login), não de tokens — fica para quando
  essas telas forem reconstruídas.

## 7. Componentes

### 7.1 Botões (`resources/views/components/button.blade.php`)

Variantes e classes CSS correspondentes em `app.css`:

| `variant` | Classe | Repouso | Hover |
|---|---|---|---|
| `primary` (padrão) | `.btn-primary` | fundo floresta, texto creme | fundo folha, texto `--text-on-brand-alt` (`#0B3B26`), sombra dura, `translate(-3px,-3px)` |
| `secondary` | `.btn-secondary` | fundo card, borda 1.5px `--hard`, texto primário | **inversão total**: fundo/texto trocam, sem sombra |
| `ghost` | `.btn-ghost` | transparente, texto verde | fundo `rgb(56 193 114 / .12)`, sem sombra |
| `destaque` / `highlight` | `.btn-destaque` | fundo folha (já "ativado") | sombra dura + `translate(-3px,-3px)`, igual ao primário |
| `danger` | `.btn-danger` | fundo vermelho (Erro) | escurece, sem sombra |

Em tema escuro, `.btn-primary` inverte a base: repouso em folha
(`--brand-400`) com texto escuro, hover em floresta (`--brand-600`) com
texto creme — mesmo padrão do CTA do header escuro na referência.

Não existe ainda uma variante "pequena" (padding/fonte reduzidos, mostrada
na referência) — não foi criada por falta de um caso de uso real no código
atual. Adicionar quando uma tela precisar.

### 7.2 Cards (`.surface`, `financial-card`, `empty-state`, `page-header`)

Sem sombra (ver §6). Hover com elevação (borda mais forte + `translateY(-2px)`)
não foi aplicado globalmente a `.surface` — muitos usos são contêineres
estáticos, não clicáveis, e um hover de "card clicável" aplicado a todos
sugeriria interatividade que não existe. Se uma tela específica tiver cards
clicáveis (ex.: cards de agente), aplicar a elevação ali, não em `.surface`.

`empty-state.blade.php` agora usa a ilustração do Capí sentado
(`public/images/mascot/capi-sentado.png`, copiada de `assets/capi/` do
projeto de design) no lugar do glifo genérico "+", com borda tracejada,
layout empilhado em telas estreitas e lado-a-lado a partir de `sm:`. A API
do componente (`title`, `message`, `action`) não mudou.

### 7.3 Badges (`badge.blade.php`)

Sem alteração de API — os tons (`success`, `danger`, `warning`, `primary`,
default) já resolvem para a paleta nova automaticamente via os tokens
atualizados.

## 8. Assets

- `public/images/mascot/capi-sentado.png` — único asset do Capí trazido até
  agora (usado no empty state). Poses adicionais (`capi-rosto`,
  `capi-apontando`, `capi-comemorando`, `capi-celular`, ilustrações dos
  agentes Dumont/Dinho/Poatan) ficam para quando as telas que as usam forem
  construídas (landing, dashboard, cards de agente).
- O símbolo/wordmark atual da marca (`public/images/brand/*`, componente
  `application-logo.blade.php`, texto "financi.ai") **não foi alterado**
  nesta revisão — é uma decisão de branding (nome exibido, logo com o rosto
  do Capí) fora do escopo de tokens, pendente de confirmação explícita.

## 9. O que ainda não foi migrado (fora do escopo desta revisão)

Esta revisão cobriu tokens e os componentes de fundação (`button`, `badge`,
`empty-state`, `financial-card`, `surface`). Deliberadamente **não**
tocaram:

- shell/layout (`sidebar`, `app-topbar`, `floating-tabs`, `auth-story`
  split-screen do login) — a paleta subjacente já mudou (eles consomem os
  mesmos tokens), mas a composição/estrutura dessas telas é trabalho de
  página, coberto pelos próximos itens (landing, login, dashboard,
  importação);
- o nome/logo exibido da marca (`financi.ai` vs. `financiai` vs.
  `financiaí`), como notado em §8;
- variantes de componente que a referência mostra mas que ainda não têm um
  caso de uso real no código (botão pequeno, cards clicáveis com elevação).

## 10. Riscos e conflitos conhecidos

1. `docs/financiai-rebranding-context.md` descreve um mascote diferente
   (onça-pintada) e Instrument Sans — documento anterior ao projeto de
   design atual, mantido só como contexto histórico de tom/posicionamento.
2. `--teal-*` continua definido, mas só como cor decorativa de gráfico
   (diversidade de série no Chart.js) — não carrega mais nenhum significado
   semântico de "sucesso".
3. `--navy`/`--navy-light` não têm equivalente na referência nova; mantidos
   sem alteração até haver uma decisão explícita.
4. As sombras suaves (`--shadow-xs/sm/md/lg/xl`) continuam em uso no shell —
   inconsistente com a regra "sem sombra suave" da referência até essas
   telas serem reconstruídas (ver §9).
