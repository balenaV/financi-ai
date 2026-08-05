# Estratégia de branches

O projeto mantém dois ambientes permanentes:

- `dev`: desenvolvimento e homologação. Todo trabalho novo começa aqui ou em uma branch curta criada a partir dela.
- `main`: produção. Recebe somente alterações validadas por Pull Request vindo de `dev`.

## Fluxo normal

```powershell
git switch dev
git pull --ff-only origin dev

# desenvolver, testar e versionar
git push origin dev
```

Quando a versão estiver aprovada:

1. abra um Pull Request de `dev` para `main`;
2. aguarde o CI concluir PHPUnit, Pint, build e Playwright;
3. faça o merge sem commits adicionais diretamente em `main`.

O próprio merge em `main` já dispara o deploy de produção via GitHub Actions (`.github/workflows/deploy.yml`), atrás de uma aprovação manual obrigatória (GitHub Environments com reviewers). Não há passo manual adicional nem gatilho por tag.

## Correção urgente

Uma correção urgente parte de `main`, em uma branch `hotfix/*`. Depois do merge em `main`, a mesma alteração deve ser incorporada em `dev` para evitar regressão.

## Ambientes externos

Staging e produção rodam na mesma VPS, como projetos Docker Compose separados — nada compartilhado entre os dois:

| | Staging | Produção |
| --- | --- | --- |
| Branch | `dev` | `main` |
| Domínio | `staging.financiai.cloud` | `financiai.cloud`, `www.financiai.cloud` |
| Porta interna | `127.0.0.1:8081` | `127.0.0.1:8080` |
| Banco | `financiai_staging` (container próprio) | `financiai_prod` (container próprio) |

Nunca reutilize o banco de produção para testes ou previews. Proteja `main` contra push direto e exija o workflow `CI` antes do merge.
