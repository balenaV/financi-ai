# Estratégia de branches

O projeto mantém dois ambientes permanentes:

- `dev`: desenvolvimento e homologação (staging). Recebe apenas squash-merge de Pull
  Requests vindos de branches `<tipo>/<escopo>` — nunca commit direto.
- `main`: produção. Recebe somente alterações validadas por Pull Request vindo de `dev`.

## Branches de trabalho

Todo trabalho novo acontece em uma branch `<tipo>/<escopo>` criada a partir de `dev`.

### Tipo

Prefixo, no espírito de Conventional Commits:

- `feat` — funcionalidade nova ou melhoria visível.
- `fix` — correção de um bug/comportamento errado (o pedido em si é "conserta isso").
- `chore` — manutenção sem efeito funcional direto: dependências, CI/workflows,
  configuração, limpeza de código morto.
- `docs` — só documentação.
- `refactor` — reorganização de código sem mudar comportamento observável.
- `test` — só testes, sem mudança de código de produção.

`hotfix/*` fica de fora dessa lista — não é um tipo, é uma branch que parte de `main`
em vez de `dev` (ver seção própria abaixo).

### Escopo

Nome da área/módulo do app sendo tocado — abrangente, não a descrição da tarefa.
Vocabulário natural: os módulos que o próprio app já nomeia (aba do dashboard, domínio
de negócio), não uma frase da entrega. Exemplos de escopo válido: `dashboard`,
`importacao`, `config`, `auth`, `contas`, `cartoes`, `transacoes`, `relatorios`,
`infra` (deploy/CI/workflows), `db` (migrations/schema geral).

Evitar dois erros:

- **Específico demais**: `feat/dashboard-modal-cartao-avatar-upload-dropdown-mes`.
- **Abrangente demais / vira genérico**: `feat/app`, `feat/geral`.

Exemplos de corte:

| Trabalho | Branch |
| --- | --- |
| Modais rápidos, nova conta manual, dropdowns/date picker do dashboard | `feat/dashboard` |
| Upload de avatar, página de Configurações, popover de usuário | `feat/config` |
| Padronizar dropdowns nativos da importação pro componente do sistema | `feat/importacao` |
| Corrigir um redirect quebrado, isoladamente, depois que a feature já foi mergeada | `fix/config` |

### Critério para decidir a branch

- Mesmo tipo + mesmo escopo, tema dá continuidade → segue na branch atual.
- Escopo muda (outro módulo) → branch nova, mesmo que o tipo seja igual.
- Tipo muda mas o escopo é o mesmo e a branch atual ainda não foi mergeada → um `fix`
  pontual encontrado no meio de um `feat` do mesmo escopo entra na própria branch, faz
  parte da entrega. Um pedido novo e independente de correção depois do merge vira
  `fix/<escopo>` à parte.
- Branch atual já mergeada → nunca continuar nela; corta nova.

### Passo a passo

```bash
# 1. Início de um tema novo
git switch dev
git pull --ff-only origin dev
git switch -c <tipo>/<escopo>

# 2. Trabalhar — commits livres

# 3. Se a dev andar enquanto o trabalho está em andamento, sincronize por merge
#    (nunca rebase numa branch já enviada ao remoto):
git fetch origin
git merge origin/dev

# 4. Pronta pra entregar:
git push -u origin <tipo>/<escopo>
gh pr create --base dev --title "..." --body "..."
# o workflow CI já dispara sozinho (pull_request: branches: [dev])

# 5. Depois do CI verde e revisão:
gh pr merge --squash
git switch dev && git pull --ff-only origin dev
git branch -D <tipo>/<escopo>   # -D porque squash quebra fast-forward
```

Squash-merge mantém o histórico da `dev` como uma lista limpa de entregas, em vez de
importar todo commit intermediário de WIP.

## Fluxo normal (dev → main)

```bash
git switch dev
git pull --ff-only origin dev
```

Quando a versão em `dev` estiver aprovada:

1. abra um Pull Request de `dev` para `main`;
2. aguarde o CI concluir PHPUnit, Pint, build e Playwright;
3. faça o merge sem commits adicionais diretamente em `main`.

O próprio merge em `main` já dispara o deploy de produção via GitHub Actions
(`.github/workflows/deploy.yml`), atrás de uma aprovação manual obrigatória (GitHub
Environments com reviewers). Não há passo manual adicional nem gatilho por tag.

## Correção urgente

Uma correção urgente parte de `main`, em uma branch `hotfix/*`:

1. `git switch main && git pull --ff-only origin main && git switch -c hotfix/<nome>`;
2. corrija, teste, `git push -u origin hotfix/<nome>`;
3. abra PR com `base: main`, aguarde o CI, faça o merge;
4. faça o backport para `dev` também via PR, para `dev` nunca receber push direto nem
   mesmo nesse caso:

```bash
git switch dev && git pull --ff-only origin dev
git switch -c hotfix/<nome>-backport
git cherry-pick <sha-do-fix>
git push -u origin hotfix/<nome>-backport
gh pr create --base dev
```

## Ambientes externos

Staging e produção rodam na mesma VPS, como projetos Docker Compose separados — nada
compartilhado entre os dois:

| | Staging | Produção |
| --- | --- | --- |
| Branch | `dev` | `main` |
| Domínio | `staging.financiai.cloud` | `financiai.cloud`, `www.financiai.cloud` |
| Porta interna | `127.0.0.1:8081` | `127.0.0.1:8080` |
| Banco | `financiai_staging` (container próprio) | `financiai_prod` (container próprio) |

Nunca reutilize o banco de produção para testes ou previews. Proteja `main` **e `dev`**
contra push direto e exija o workflow `CI` antes do merge em ambas.
