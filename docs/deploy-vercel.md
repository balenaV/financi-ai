# Deploy manual na Vercel

Este guia prepara a aplicação para Vercel com Supabase PostgreSQL. Ele não executa o deploy.

## 1. Preparar o banco

1. Crie o projeto no Supabase.
2. Abra **Connect** e escolha a conexão direta, Session Pooler ou Transaction Pooler.
3. Preencha um `.env` local temporário com as credenciais do banco.
4. Mantenha `DB_SCHEMA=finance` e `DB_SSLMODE=require`.
5. Use `DB_POOL_MODE=transaction` somente com o Transaction Pooler.
6. Execute `docker compose run --rm --user root app php artisan migrate --force`.

Nunca use `migrate:fresh` ou o seeder demonstrativo no banco de produção.

## 2. Preparar os ativos

```powershell
docker compose run --rm --user root app npm ci
docker compose run --rm --user root app npm run build
```

Confira se `public/build/manifest.json` e os arquivos em `public/build/assets` foram criados e estão versionados.

## 3. Criar o projeto Vercel

Importe o repositório no painel da Vercel. Não defina um framework preset que substitua `vercel.json`. O arquivo configura:

- `api/index.php` como função PHP;
- `vercel-php@0.9.0` como runtime;
- `public/build` como origem dos ativos compilados;
- todas as demais rotas para o front controller do Laravel.

## 4. Configurar variáveis

Cadastre as variáveis da seção Vercel do `README.md` nos ambientes desejados. Pontos essenciais:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL` com o domínio real
- `APP_KEY` gerada fora do painel
- conexão PostgreSQL com SSL
- caminhos de storage, views e caches em `/tmp`

Não use `DB_URL` ao mesmo tempo que variáveis individuais, a menos que queira que a URL tenha precedência.

## 5. Verificar antes de publicar

```powershell
docker compose run --rm --user root app php artisan test
docker compose run --rm --user root app ./vendor/bin/pint --test
docker compose run --rm --user root app php artisan view:cache
docker compose run --rm --user root app npm run build
```

Faça um backup do banco e aplique migrations antes de direcionar tráfego.

## 6. Smoke test pós-deploy

1. Abra a landing page.
2. Faça login com um usuário real, nunca com a conta demo.
3. Crie uma conta financeira e uma transação de pequeno valor.
4. Confirme o saldo do dashboard.
5. Exporte um CSV.
6. Verifique logs da função e conexões do Supabase.
7. Confirme que `/register` retorna 404 quando `ALLOW_REGISTRATION=false`.

## Limitações serverless consideradas

- Apenas `/tmp` é tratado como armazenamento gravável.
- Uploads persistentes devem ir para um serviço de objetos; o app atual não depende de uploads.
- O processamento de filas está configurado como síncrono.
- Sessões e cache usam o banco para sobreviver entre invocações.
- Migrations não são executadas durante requisições ou builds.
