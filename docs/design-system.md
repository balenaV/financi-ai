# Design system — financi.ai

## 1. Escopo, origem e precedência

Este documento registra a fundação visual extraída do projeto de referência
`financi-ai-design-system` e a forma recomendada de aplicá-la ao projeto Laravel
`financi-ai`.

As fontes de referência têm a seguinte precedência:

1. `artifacts/mockup-sandbox/src/components/mockups/financi-ai/_shared/_tokens.css`
   é a fonte canônica dos tokens da marca.
2. `FoundationsLight.tsx` e `FoundationsDark.tsx` demonstram os tokens e estados
   aprovados.
3. `ComponentsLight.tsx` e `ComponentsDark.tsx` especificam os componentes.
4. `FinancialLight.tsx`, `FinancialDark.tsx`, `LandingLight.tsx` e
   `LandingDark.tsx` especificam composição e responsividade.
5. Os componentes em `src/components/ui/*.tsx` são primitives shadcn/Radix do
   sandbox. Eles servem como referência de comportamento e acessibilidade, mas
   não devem ser copiados literalmente para Blade.
6. `artifacts/mockup-sandbox/src/index.css` contém valores padrão do template
   shadcn. Em qualquer divergência, `_shared/_tokens.css` prevalece.

O projeto de referência não contém imagens, ícones vetoriais próprios ou fontes
binárias. Os mockups usam ícones de `lucide-react` e carregam Instrument Sans do
Google Fonts. No projeto real, deve-se preservar Font Awesome e a cópia local de
Instrument Sans já instalados.

**Fontes:** `_shared/_tokens.css`; `src/index.css`; `components.json`;
`artifacts/mockup-sandbox/package.json`; imports dos arquivos `*Light.tsx` e
`*Dark.tsx`.

## 2. Princípios visuais

- **Trusted & Clean:** superfícies limpas, contraste claro e hierarquia discreta.
- **Creme como identidade:** o fundo claro é creme, enquanto cards permanecem
  brancos.
- **Preto para estrutura:** tipografia, contornos, navegação e hierarquia.
- **Verde para marca e destaque:** ações principais, foco e o sufixo `.ai`.
- **Turquesa para saúde financeira:** receita, sucesso e evolução positiva.
- **Azul e violeta com uso funcional:** gráficos, informação e recursos de IA
  dentro do produto, nunca como base da landing.
- **Vermelho para perda/erro:** despesas, falhas e ações destrutivas.
- **Laranja para atenção financeira:** dívida, vencimento e alerta.
- **Neutros quentes:** texto secundário, bordas e profundidade sobre o creme.
- Estados nunca devem depender somente da cor; devem incluir texto, ícone ou
  outro indicador perceptível.

**Fontes:** `_shared/_tokens.css`; `FoundationsLight.tsx`;
`FoundationsDark.tsx`; `FinancialLight.tsx`; `FinancialDark.tsx`. A direção
preto/creme/verde é uma adaptação de marca aprovada para o financi.ai em
2026-07-28; não foi extraída literalmente do Replit.

## 3. Paleta completa

### 3.1 Base e superfícies

| Token | Light | Dark | Uso |
|---|---|---|---|
| `background` / `bg-base` | `#FFFDF2` | `#080A0D` | Fundo da aplicação |
| `background-alt` / `bg-alt` | `#F8F6EB` | `#0D1117` | Seções e áreas alternadas |
| `surface` / `bg-card` | `#FFFFFF` | `#11151A` | Cards, sidebar e painéis |
| `surface-elevated` | `#FFFFFF` | `#171C22` | Dropdowns e painéis elevados |
| `overlay-surface` / `bg-overlay` | `#FFFFFF` | `#1E2530` | Popovers e modais |
| `input-surface` / `bg-input` | `#FFFFFF` | `#11151A` | Inputs, selects e textareas |

**Fonte:** `_shared/_tokens.css`, blocos `:root` e `.dark`.

### 3.2 Texto

| Token | Light | Dark | Uso |
|---|---|---|---|
| `text-primary` | `#090A0B` | `#FFFDF2` | Títulos, valores e corpo principal |
| `text-secondary` | `#4B4A45` | `#C9C6B9` | Descrições, labels auxiliares |
| `text-tertiary` | `#78766F` | `#969388` | Metadados e placeholders |
| `text-inverse` | `#FFFDF2` | `#090A0B` | Texto em superfície invertida |
| `text-on-primary` | `#06150D` | `#06150D` | Texto sobre verde |
| `text-on-success` | `#FFFFFF` | `#FFFFFF` | Texto sobre turquesa sólido |

**Fonte:** `_shared/_tokens.css`, tokens `--text-*`.

> Adaptação de acessibilidade: a referência declara `#64748B` para
> `text-tertiary` no dark. A aplicação usa `#94A3B8`, pois o valor original não
> alcança WCAG AA em textos pequenos sobre `#080A0D` e `#11151A`. Pelo mesmo
> motivo, classes de texto primary `600/700` usam `primary-400` no dark, e
> danger/red `600/700` usam `#FF7373`. A geometria e a intenção dos tokens da
> referência permanecem preservadas.

### 3.3 Bordas

| Token | Light | Dark | Uso |
|---|---|---|---|
| `border-subtle` | `#DEDACF` | `#292B28` | Cards e divisores discretos |
| `border-default` | `#BDB8AD` | `#444740` | Inputs e controles |
| `border-strong` | `#090A0B` | `#FFFDF2` | Separação editorial e ênfase |

Larguras previstas: `1px` para borda fina e `1.5px` para borda média.

**Fonte:** `_shared/_tokens.css`, tokens `--border-*` e
`--border-width-*`.

### 3.4 Verde de marca — primary

| Escala | Valor |
|---|---|
| `primary-50` | `#EEFBF4` |
| `primary-100` | `#D7F7E5` |
| `primary-200` | `#AFEECB` |
| `primary-400` | `#4BD496` |
| `primary-500` | `#22BF77` |
| `primary-600` | `#15995D` |
| `primary-700` | `#117A4C` |
| `primary-800` | `#105F3E` |
| `primary-900` | `#0C4E34` |

No tema dark, os fundos suaves são substituídos por:

- `primary-50`: `#102B20`;
- `primary-100`: `#22BF7726`;
- `primary-200`: `#22BF7747`.

**Fonte:** a estrutura da escala e os estados vêm de `_shared/_tokens.css`,
`FoundationsLight.tsx` e `FoundationsDark.tsx`. Os valores verdes são uma
adaptação de marca do financi.ai, registrada em `resources/css/app.css`.

### 3.5 Navy — secondary

| Token | Valor |
|---|---|
| `secondary` / `navy` | `#102A43` |
| `secondary-light` / `navy-light` | `#1E3A5F` |

Navy deve ser usado em gráficos e informação contextual,
não como substituto indiscriminado de `text-primary`.

**Fonte:** `_shared/_tokens.css`, tokens `--navy` e `--navy-light`.

### 3.6 Turquesa — success

| Escala | Valor |
|---|---|
| `success-50` | `#ECFDF5` |
| `success-100` | `#D1FAE5` |
| `success-400` | `#2DD4BF` |
| `success-500` | `#14B89A` |
| `success-600` | `#0D9488` |
| `success-700` | `#0F766E` |

No dark, `success-50` torna-se `#0D2B26` e `success-100`, `#134E4A30`.

**Fonte:** `_shared/_tokens.css`, escala `--teal-*`.

### 3.7 Violeta — IA e visualização de dados

| Escala | Valor |
|---|---|
| `ai-500` | `#8B5CF6` |
| `ai-600` | `#7C3AED` |
| `ai-700` | `#6D28D9` |
| `ai-900` | `#3B0764` |

O violeta não é mais uma cor institucional. Ele pode aparecer em gráficos,
badges e recursos de IA dentro do produto, sem competir com o verde de marca.

**Fonte:** `_shared/_tokens.css`; badges em `LandingLight.tsx`,
`LandingDark.tsx`, `FoundationsLight.tsx`, `FoundationsDark.tsx`,
`ComponentsLight.tsx` e `ComponentsDark.tsx`.

### 3.8 Danger, warning e pending

| Família | 50 | 100 | 400/500 | 600 | 700 |
|---|---|---|---|---|---|
| Danger/red | `#FEF2F2` | `#FEE2E2` | `#EF4444` | `#DC2626` | `#B91C1C` |
| Warning/orange | `#FFF7ED` | `#FFEDD5` | `#F97316` | `#EA580C` | `#C2410C` |
| Pending/yellow | `#FEFCE8` | — | `#FACC15` | `#EAB308` | — |

No dark:

- danger `50/100`: `#2D1212` / `#7F1D1D40`;
- warning `50/100`: `#2D1A0A` / `#7C2D1240`.

**Fonte:** `_shared/_tokens.css`; badges e alerts em `ComponentsLight.tsx` e
`ComponentsDark.tsx`.

### 3.9 Neutros

| Escala | Valor |
|---|---|
| `neutral-50` | `#F8FAFC` |
| `neutral-100` | `#F1F5F9` |
| `neutral-200` | `#E2E8F0` |
| `neutral-300` | `#CBD5E1` |
| `neutral-400` | `#94A3B8` |
| `neutral-500` | `#64748B` |
| `neutral-600` | `#475569` |
| `neutral-700` | `#334155` |
| `neutral-800` | `#1E293B` |
| `neutral-900` | `#0F172A` |

O dark redefine `neutral-100..500` para `#1E293B`, `#28313B`, `#334155`,
`#475569` e `#64748B`.

**Fonte:** `_shared/_tokens.css`, escala `--slate-*`.

## 4. Cores semânticas

| Semântica | Token base | Função |
|---|---|---|
| `primary` | `primary-600` | Ação principal, link, item ativo |
| `primary-hover` | `primary-700` | Hover de ação principal |
| `primary-active` | `primary-800` | Pressed/active |
| `secondary` | `navy` | Apoio institucional |
| `success` | `success-600` | Receita, pago, concluído, positivo |
| `warning` | `warning-600` | Dívida, vencimento e atenção |
| `danger` | `danger-600` | Despesa, erro, exclusão |
| `info` | `blue-600` | Mensagem informativa |
| `background` | `bg-base` | Fundo global |
| `surface` | `bg-card` | Superfície padrão |
| `border` | `border-subtle` | Borda padrão de superfície |
| `focus` | `primary-600` | Foco de teclado |

**Fonte:** combinação dos tokens em `_shared/_tokens.css` com os exemplos de
estado de `FoundationsLight.tsx`, `FoundationsDark.tsx`,
`ComponentsLight.tsx` e `ComponentsDark.tsx`.

## 5. Tipografia

### 5.1 Famílias

- Sans: `'Instrument Sans', 'Inter', system-ui, sans-serif`.
- Mono: `'JetBrains Mono', 'Fira Code', monospace`.
- Valores financeiros devem usar números tabulares.

Instrument Sans está definida no Google Fonts na referência. Na aplicação real
ela deve continuar sendo servida localmente por `@fontsource/instrument-sans`.

**Fonte:** `_shared/_tokens.css`; helpers `.font-brand-sans` e
`.font-brand-mono` em `_group.css`.

### 5.2 Escala

| Token | Tamanho |
|---|---|
| `text-xs` | `11px` |
| `text-sm` | `13px` |
| `text-base` | `15px` |
| `text-md` | `16px` |
| `text-lg` | `18px` |
| `text-xl` | `20px` |
| `text-2xl` | `24px` |
| `text-3xl` | `30px` |
| `text-4xl` | `36px` |
| `text-5xl` | `48px` |

Pesos: regular `400`, medium `500`, semibold `600`, bold `700`.

Para a landing page, títulos responsivos podem chegar a `4.5rem` no breakpoint
`md`, conforme `text-5xl md:text-7xl`. Isso é uma exceção editorial, não um novo
token global.

**Fonte:** `_shared/_tokens.css`; demonstração em `FoundationsLight.tsx` e
`FoundationsDark.tsx`; hero em `LandingLight.tsx` e `LandingDark.tsx`.

## 6. Espaçamento

A unidade base é `4px`.

| Token | Valor |
|---|---|
| `space-1` | `4px` |
| `space-2` | `8px` |
| `space-3` | `12px` |
| `space-4` | `16px` |
| `space-5` | `20px` |
| `space-6` | `24px` |
| `space-8` | `32px` |
| `space-10` | `40px` |
| `space-12` | `48px` |
| `space-16` | `64px` |
| `space-20` | `80px` |
| `space-24` | `96px` |

**Fonte:** `_shared/_tokens.css`; visualização em `FoundationsLight.tsx` e
`FoundationsDark.tsx`.

## 7. Raios, bordas e sombras

### 7.1 Raios

| Token | Valor | Aplicação sugerida |
|---|---|---|
| `radius-sm` | `6px` | Itens internos compactos |
| `radius-md` | `10px` | Botões e inputs |
| `radius-lg` | `16px` | Cards e painéis |
| `radius-xl` | `20px` | Cards destacados |
| `radius-2xl` | `28px` | Pricing e hero cards |
| `radius-full` | `9999px` | Avatar, badge e toggle |

### 7.2 Sombras light

| Token | Valor |
|---|---|
| `shadow-xs` | `0 1px 2px rgba(9,10,11,.05)` |
| `shadow-sm` | `0 1px 3px rgba(9,10,11,.08), 0 1px 2px -1px rgba(9,10,11,.06)` |
| `shadow-md` | `0 4px 6px -1px rgba(9,10,11,.07), 0 2px 4px -2px rgba(9,10,11,.05)` |
| `shadow-lg` | `0 10px 15px -3px rgba(9,10,11,.08), 0 4px 6px -4px rgba(9,10,11,.05)` |
| `shadow-xl` | `0 20px 25px -5px rgba(9,10,11,.08), 0 8px 10px -6px rgba(9,10,11,.04)` |
| `shadow-card` | `0 1px 3px rgba(9,10,11,.06), 0 1px 2px -1px rgba(9,10,11,.04)` |
| `shadow-focus` | `0 0 0 3px rgba(37,99,235,.20)` |

No dark, as mesmas geometrias usam preto com opacidade de `.30` a `.55`; o
foco usa `rgba(37,99,235,.35)`.

**Fonte:** `_shared/_tokens.css`; demonstração em `FoundationsLight.tsx` e
`FoundationsDark.tsx`.

## 8. Motion

| Token | Valor |
|---|---|
| `duration-fast` | `100ms` |
| `duration-base` | `150ms` |
| `duration-slow` | `250ms` |
| `duration-enter` | `300ms` |
| `ease-default` | `cubic-bezier(0.4, 0, 0.2, 1)` |
| `ease-spring` | `cubic-bezier(0.34, 1.56, 0.64, 1)` |
| `ease-out` | `cubic-bezier(0, 0, 0.2, 1)` |

Respeitar `prefers-reduced-motion: reduce`.

**Fonte:** `_shared/_tokens.css`; transições de navegação em
`ComponentsLight.tsx` e `ComponentsDark.tsx`.

## 9. Dimensões padrão

| Componente | Dimensão |
|---|---|
| Navbar/header | `64px` |
| Sidebar expandida de referência | `240px` |
| Sidebar recolhida de referência | `64px` |
| Botão pequeno | altura mínima `32px` |
| Botão padrão | altura mínima `36px` |
| Botão grande | altura mínima `40px` |
| Botão somente ícone | `36 × 36px` |
| Input/select padrão | `36px` na primitive; `40px` recomendado para o app financeiro |
| Ícone em controle | `16px`; navegação normalmente `20px` |
| Avatar padrão | `40 × 40px` |
| Ícone de empty state | contêiner `64 × 64px`, ícone `32px` |
| Card | padding padrão `24px` |
| Card financeiro | padding entre `16px` e `24px` |
| Modal | largura máxima `512px` (`max-w-lg`) |
| Dashboard | largura máxima `1400px` |

A aplicação real já utiliza controles com altura mínima de `40px`. A adaptação
deve preservar essa altura por acessibilidade e toque, mesmo que a primitive do
sandbox use `36px`.

**Fontes:** `ComponentsLight.tsx`, `ComponentsDark.tsx`;
`src/components/ui/button.tsx`, `input.tsx`, `select.tsx`, `card.tsx` e
`dialog.tsx`; `FinancialLight.tsx` e `FinancialDark.tsx`.

## 10. Breakpoints e responsividade

O projeto de referência não declara breakpoints customizados; usa os breakpoints
padrão do Tailwind:

| Prefixo | Largura mínima |
|---|---|
| `sm` | `640px` |
| `md` | `768px` |
| `lg` | `1024px` |
| `xl` | `1280px` |
| `2xl` | `1536px` |

Padrões observados:

- mobile-first, uma coluna por padrão;
- CTA empilhada no mobile e horizontal em `sm`;
- navegação completa e sidebar de mockup visíveis em `md`;
- KPI: 1 coluna, 2 em `md`, 4 em `xl`;
- dashboard principal: uma coluna, grade de 12 colunas em `xl`, com conteúdo
  `8/12` e painel lateral `4/12`;
- cards: `repeat(auto-fit, minmax(280px, 1fr))` quando a grade é fluida;
- header financeiro empilha no mobile e alinha horizontalmente em `md`;
- padding de página: `16px`, `24px` em `md`, `32px` em `lg`;
- landing usa containers centralizados e padding lateral de `24px`.

**Fontes:** classes responsivas em `LandingLight.tsx`, `LandingDark.tsx`,
`FinancialLight.tsx`, `FinancialDark.tsx`, `ComponentsLight.tsx` e
`ComponentsDark.tsx`; Tailwind 4 em `artifacts/mockup-sandbox/package.json`.

## 11. Estados interativos

### 11.1 Geral

- `hover`: alteração de cor ou elevação sutil, `150ms`.
- `focus-visible`: anel verde de `3px`, sem remover o outline sem substituição.
- `active`: `primary-800` em ações primárias e redução da elevação.
- `disabled`: cursor `not-allowed`, sem eventos e opacidade entre `.50` e `.60`.
- `loading`: controle desabilitado, spinner de `16px` e preservação da largura.
- `selected`: fundo suave da cor semântica e texto de maior contraste.

### 11.2 Botão primário

- default `#22BF77`;
- hover `#4BD496`;
- active `#15995D`;
- texto `#06150D`;
- borda preta/creme conforme o tema;
- disabled com superfície neutra, texto terciário e opacidade `.60`.

**Fontes:** estados em `FoundationsLight.tsx` e `FoundationsDark.tsx`;
primitives em `src/components/ui/button.tsx`; exemplos em
`ComponentsLight.tsx` e `ComponentsDark.tsx`.

## 12. Especificação dos componentes

### 12.1 Botões

Variantes:

- **primary:** verde sólido, texto quase preto e contorno de alto contraste;
- **secondary/outline:** superfície transparente ou card, borda default;
- **ghost:** sem borda visível, hover em `background-alt`;
- **danger:** vermelho sólido;
- **link:** texto primary com underline no hover;
- **AI:** violeta somente quando necessário dentro do produto.

Tamanhos: small `32px`, default `40px` no app real, large ao menos `40px`,
icon-only quadrado. Usar `radius-md`, peso `500/600`, gap `8px`.

**Fontes:** `FoundationsLight.tsx`, `FoundationsDark.tsx`,
`ComponentsLight.tsx`, `ComponentsDark.tsx` e `src/components/ui/button.tsx`.

### 12.2 Inputs e textareas

- fundo `input-surface`;
- borda `border-default`;
- texto `text-primary`;
- placeholder `text-tertiary`;
- `radius-md`;
- padding horizontal `12–14px`;
- altura mínima `40px` no app real;
- foco em primary com `shadow-focus`;
- erro com danger e mensagem textual;
- disabled com fundo alternativo, texto terciário e cursor `not-allowed`.

Textarea segue as mesmas regras, com altura definida pelo conteúdo e resize
vertical quando adequado.

**Fontes:** seção Form Fields em `ComponentsLight.tsx` e
`ComponentsDark.tsx`; `src/components/ui/input.tsx`.

### 12.3 Selects

O trigger segue o input. O menu usa `overlay-surface`, borda sutil, `shadow-md`,
scroll vertical e item ativo com fundo suave. Indicadores visuais ficam à
direita. No projeto Blade, preservar `<select>` nativo nesta fase.

**Fontes:** seção Form Fields em `ComponentsLight.tsx` e
`ComponentsDark.tsx`; `src/components/ui/select.tsx`.

### 12.4 Cards

- fundo `surface`;
- borda `border-subtle`;
- `radius-lg` (`16px`);
- `shadow-card`;
- padding padrão `24px`;
- header com título semibold e descrição secondary;
- ações no footer;
- hover opcional com `shadow-md` e deslocamento máximo de `-1px`.

Cards financeiros devem usar cor semântica no valor/ícone, não colorir toda a
superfície.

**Fontes:** seção Cards em `ComponentsLight.tsx` e `ComponentsDark.tsx`;
`src/components/ui/card.tsx`; KPI cards em `FinancialLight.tsx` e
`FinancialDark.tsx`.

### 12.5 Tabelas

- wrapper com overflow horizontal;
- texto `13–15px`;
- header com peso medium/semibold e texto secondary;
- células com padding aproximado de `16px`;
- separadores `border-subtle`;
- linha hover em `background-alt`;
- valores monetários alinhados à direita e com números tabulares;
- paginação separada por borda superior.

**Fontes:** seção Tables em `ComponentsLight.tsx` e `ComponentsDark.tsx`;
`src/components/ui/table.tsx`.

### 12.6 Badges

- formato pill ou `radius-md`;
- tamanho padrão: `12px`, padding `4px 10px`;
- default neutro;
- primary verde; info azul;
- success turquesa;
- danger vermelho;
- warning laranja;
- pending amarelo;
- AI em gradiente azul-violeta.

Fundos devem usar tons `50/100`; texto deve usar tons `600/700`.

**Fontes:** badges em `FoundationsLight.tsx`, `FoundationsDark.tsx`,
`ComponentsLight.tsx` e `ComponentsDark.tsx`; primitive
`src/components/ui/badge.tsx`.

### 12.7 Modais

- overlay preto entre `.50` e `.80`, com blur discreto;
- superfície elevada;
- largura máxima `512px`;
- padding `24px`;
- título `18px/600`;
- descrição secondary;
- ações empilhadas no mobile e alinhadas à direita em `sm`;
- fechar por botão, overlay e `Escape`;
- foco inicial e restituição de foco obrigatórios.

**Fontes:** seção Modals em `ComponentsLight.tsx` e `ComponentsDark.tsx`;
`src/components/ui/dialog.tsx`.

### 12.8 Navegação e sidebar

- navbar com `64px`, superfície e borda inferior;
- item ativo em primary, com underline na navbar;
- sidebar com `240px` expandida e `64px` recolhida na referência;
- item da sidebar com padding `12px`, `radius-md`, ícone `20px`;
- ativo com `primary-50`, texto `primary-600` e peso semibold;
- inativo com texto secondary e hover em superfície alternativa;
- recolhida mostra somente ícones com tooltip;
- no mobile, usar drawer ou navegação inferior.

A largura atual da aplicação real (`256px/112px`) pode ser preservada nesta
fundação para evitar deslocamento estrutural; a dimensão de referência deve ser
adotada somente na futura migração de layout.

**Fontes:** seção Navigation em `ComponentsLight.tsx` e
`ComponentsDark.tsx`; mockup em `LandingLight.tsx` e `LandingDark.tsx`.

### 12.9 Cabeçalhos

- altura global `64px`;
- título de página `24px/700`;
- descrição `13–15px`, secondary;
- ações em grupo com gap `12px`;
- empilhar conteúdo no mobile e alinhar horizontalmente a partir de `md`;
- superfícies sticky podem usar transparência com backdrop blur.

**Fontes:** headers em `FinancialLight.tsx`, `FinancialDark.tsx`,
`LandingLight.tsx`, `LandingDark.tsx`, `ComponentsLight.tsx` e
`ComponentsDark.tsx`.

### 12.10 Alerts

Variantes info, success, warning e danger. Cada alert combina ícone, título,
descrição, fundo suave, borda e texto da mesma família semântica.

**Fontes:** seção Alerts em `ComponentsLight.tsx` e `ComponentsDark.tsx`.

### 12.11 Tabs

Lista de tabs compacta, trigger com estado ativo perceptível, conteúdo em
surface com `radius-lg` e margin-top `16px`. Deve permitir scroll horizontal no
mobile.

**Fontes:** seção Tabs em `ComponentsLight.tsx` e `ComponentsDark.tsx`;
`src/components/ui/tabs.tsx`.

### 12.12 Tooltips

Usar em controles somente ícone e sidebar recolhida. Não substituir labels
essenciais. Deve aparecer por hover e foco.

**Fontes:** seção Tooltips em `ComponentsLight.tsx` e
`ComponentsDark.tsx`; `src/components/ui/tooltip.tsx`.

### 12.13 Skeletons e estados vazios

Skeletons cobrem card, linha de tabela e perfil sem alterar a geometria final.
Empty state centraliza ícone em círculo `64px`, título, descrição e CTA
opcional.

**Fontes:** seções Skeletons e Empty States em `ComponentsLight.tsx` e
`ComponentsDark.tsx`; primitives `src/components/ui/skeleton.tsx` e
`src/components/ui/empty.tsx`.

## 13. Componentes financeiros encontrados

- KPI cards de saldo, receitas, despesas e investimentos;
- gráfico de linha/área de evolução;
- gráfico de barras de receitas versus despesas;
- gráfico de pizza por categoria;
- lista de transações recentes;
- acompanhamento de orçamentos;
- widget de cartão e limite;
- card de insight de IA;
- progresso de metas;
- composição de carteira de investimentos.

O layout financeiro usa KPIs responsivos, uma área principal de `8/12` e uma
coluna lateral de `4/12` em telas `xl`.

**Fontes:** `FinancialLight.tsx` e `FinancialDark.tsx`.

## 14. Estrutura da landing page de referência

1. navbar sticky;
2. hero com badge, título, descrição e dois CTAs;
3. mockup do dashboard;
4. seção de recursos;
5. indicadores numéricos;
6. pricing;
7. CTA final;
8. footer.

**Fontes:** `LandingLight.tsx` e `LandingDark.tsx`.

## 15. Assets e equivalências no projeto real

| Referência | Projeto real |
|---|---|
| Instrument Sans via Google Fonts | `@fontsource/instrument-sans` local |
| Lucide React | Font Awesome |
| Recharts | Chart.js |
| Logo construído com `Command` e texto | wordmark tipográfico Blade e símbolo vetorial em `public/images/brand/financi-ai-symbol.svg` |
| React + Radix state | Blade + JavaScript/jQuery existente |
| shadcn components | componentes Blade em `resources/views/components` |

Não copiar os ícones Lucide, o logo provisório do mockup, React state, Radix ou
Recharts.

**Fontes da referência:** `LandingLight.tsx`, `LandingDark.tsx`,
`FinancialLight.tsx`, `FinancialDark.tsx`, `components.json` e
`artifacts/mockup-sandbox/package.json`.

## 16. Mapa de migração

### Portáveis diretamente como especificação

- `_shared/_tokens.css`: valores, não o arquivo literalmente;
- tipografia e escala;
- cores semânticas;
- raios, sombras, motion e espaçamento;
- composição visual de cards, badges, tabelas e formulários;
- grids e breakpoints Tailwind;
- padrões da landing e dashboard.

### Exigem adaptação para Blade

- todos os arquivos `src/components/ui/*.tsx`;
- modais e dropdowns Radix;
- sidebar React;
- tabs, tooltip, select e switch React;
- charts Recharts;
- ícones Lucide;
- handlers `useState` e eventos JSX;
- estilos inline baseados em CSS variables.

### Não migrar

- servidor de preview do Replit;
- plugins `@replit/*`;
- API de exemplo e healthcheck do workspace;
- Drizzle, Orval e bibliotecas do monorepo sem relação com o visual;
- valores placeholder do `src/index.css` quando conflitam com
  `_shared/_tokens.css`.

**Fontes:** estrutura completa do workspace de referência, especialmente
`package.json`, `pnpm-workspace.yaml`, `artifacts/mockup-sandbox/package.json`,
`vite.config.ts`, `src/index.css` e diretórios `src/components`.

## 17. Riscos e conflitos conhecidos

1. A aplicação real usa Tailwind 4 CSS-first; manter duas fontes divergentes de
   tokens entre CSS e `tailwind.config.js` gera inconsistência.
2. A paleta original usava azul como `primary`; as classes `primary-*` foram
   remapeadas para o verde de marca sem eliminar azul, violeta e turquesa dos
   gráficos e estados financeiros.
3. Há overrides dark com hexadecimais fixos; eles devem apontar para tokens.
4. A aplicação real possui componentes Blade novos e componentes de autenticação
   herdados com classes duplicadas; ambos devem convergir gradualmente.
5. Copiar shadcn/Radix exigiria React e quebraria as convenções Blade.
6. Lucide e Font Awesome juntos aumentariam bundle e inconsistência visual.
7. Recharts e Chart.js juntos aumentariam bundle e duplicariam responsabilidades.
8. A sidebar da referência usa larguras diferentes da aplicação; alterar isso
   agora mudaria layout, portanto deve ficar para a fase de páginas.
9. Estilos inline do mockup não são uma estratégia adequada para a aplicação
   real; devem virar tokens/classes globais.
10. As cores fixas do Chart.js precisam consumir os mesmos tokens para responder
    corretamente à troca de tema.

## 18. Plano incremental de implementação

### Etapa 1 — Fundação

- registrar tokens CSS e Tailwind;
- remapear `primary` para verde de marca e manter azul/turquesa como famílias
  funcionais;
- criar aliases semânticos para background, surface, border, success, warning,
  danger e AI;
- preservar Font Awesome, Instrument Sans local e Chart.js;
- validar build.

### Etapa 2 — Componentes globais

- adaptar botões, inputs, selects, textareas, badges, cards, modal, progress,
  page header e empty state;
- preservar APIs Blade atuais;
- validar Feature tests e páginas de autenticação.

### Etapa 3 — Layout global

- migrar header, sidebar e navegação mobile;
- revisar tooltips, foco, drawer e sidebar recolhida;
- validar desktop e mobile.

### Etapa 4 — Landing page

- migrar as oito seções da referência;
- substituir o logo provisório por wordmark tipográfico com `.ai` verde;
- revisar copy e remover promessas de funcionalidades não existentes;
- medir tamanho do bundle, LCP e CLS.

### Etapa 5 — Dashboard

- migrar KPI cards e grade `8/12 + 4/12`;
- adaptar gráficos para Chart.js;
- manter os dados e serviços Laravel existentes.

### Etapa 6 — Páginas funcionais

- migrar uma família por vez: transações, contas, cartões, dívidas,
  planejamento, investimentos, orçamentos, metas e relatórios;
- executar testes após cada família.

### Etapa 7 — Qualidade

- auditoria WCAG AA;
- testes de teclado e reduced motion;
- E2E desktop/mobile;
- análise de bundle e performance;
- remoção de estilos antigos somente quando não houver mais consumidores.

Cada etapa deve produzir um diff pequeno, build aprovado e testes verdes antes
do início da próxima.
