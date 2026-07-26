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
3. faça o merge sem commits adicionais diretamente em `main`;
4. crie uma tag `vX.Y.Z` para disparar o deploy de produção.

## Correção urgente

Uma correção urgente parte de `main`, em uma branch `hotfix/*`. Depois do merge em `main`, a mesma alteração deve ser incorporada em `dev` para evitar regressão.

## Ambientes externos

- Vercel Production Branch: `main`.
- Preview/Homologação Vercel: `dev`.
- Supabase produção: usado somente por `main`.
- Supabase desenvolvimento/homologação: projeto separado, usado por `dev`.

Nunca reutilize o banco de produção para testes ou previews. Proteja `main` contra push direto e exija o workflow `CI` antes do merge.
