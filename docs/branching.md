# Estratégia de branches

O projeto mantém dois ambientes permanentes:

- `dev`: desenvolvimento e homologação (staging). Todo trabalho novo acontece em uma branch `feature/*` criada a partir dela — evite commitar direto em `dev` quando o trabalho tiver mais de um commit ou levar mais de um dia.
- `main`: produção. Recebe somente alterações validadas por Pull Request vindo de `dev`.

## Branches de feature

Para qualquer trabalho não-trivial (UI/UX, nova tela, refatoração), crie uma branch a partir de `dev`:

```bash
git switch dev
git pull --ff-only origin dev
git switch -c feature/<escopo-curto>

# desenvolver, testar e versionar
git push -u origin feature/<escopo-curto>
```

Convenção de nome: `feature/<escopo-curto>`, em inglês ou português curto, descrevendo o que está sendo feito (ex.: `feature/landing-page-auth-uiux`, `feature/design-system`, `feature/app-tabs-layout`). Quando pronta, abra um Pull Request de volta para `dev` (não direto para `main`).

## Fluxo normal (dev → main)

```bash
git switch dev
git pull --ff-only origin dev
git push origin dev
```

Quando a versão em `dev` estiver aprovada:

1. abra um Pull Request de `dev` para `main`;
2. aguarde o CI concluir PHPUnit, Pint, build e Playwright;
3. faça o merge sem commits adicionais diretamente em `main`;
4. crie uma tag `vX.Y.Z` para disparar o deploy de produção.

## Correção urgente

Uma correção urgente parte de `main`, em uma branch `hotfix/*`. Depois do merge em `main`, a mesma alteração deve ser incorporada em `dev` para evitar regressão.

## Ambientes externos

- Ambiente de staging/homologação na VPS: alimentado por `dev`.
- Ambiente de produção na VPS: alimentado por `main`.

Nunca reutilize o banco de produção para testes ou previews. Proteja `main` contra push direto e exija o workflow `CI` antes do merge.
