# financi.ai

Aplicação web de finanças pessoais construída com Laravel 13, PHP 8.5, PostgreSQL, Blade, Tailwind CSS 4, jQuery e Chart.js. O projeto cobre o fluxo completo de controle financeiro: contas, receitas, despesas, transferências, dívidas, investimentos, orçamentos, metas e relatórios.

## O que está incluído

- Autenticação completa com Laravel Breeze e cadastro controlado por `ALLOW_REGISTRATION`.
- Dashboard com saldo, receitas, despesas, economia, evolução mensal, distribuição por categoria, alertas e próximos vencimentos.
- Contas com saldo inicial, saldo calculado e histórico.
- Transações com filtros, recorrência, parcelamento, duplicação, cancelamento, exportação CSV e transferências atômicas entre contas.
- Categorias personalizadas por usuário, com cor e ícone.
- Dívidas com geração automática de parcelas e baixa vinculada a uma despesa.
- Cartões de crédito com cadastro de faturas mensais, vencimentos, pagamentos e limite disponível.
- Total consolidado de dívidas somando faturas abertas e parcelas de empréstimos.
- Investimentos com aportes, resgates, rendimentos e valor atual.
- Orçamentos mensais por categoria, alertas de consumo e cópia do mês anterior.
- Metas financeiras com aportes e acompanhamento de progresso.
- Relatórios por período, conta, categoria e tipo, com gráficos e CSV.
- Preferências de moeda, tema, primeiro dia do mês e ocultação global de valores.
- Modo noturno persistente, sidebar recolhível e ícones Font Awesome.
- Interface responsiva em português do Brasil, acessível por teclado e pronta para impressão.
- Isolamento dos dados por usuário com policies, validações dedicadas, CSRF e consultas sempre escopadas.
- Verificação de e-mail obrigatória, proteção contra tentativas repetidas e histórico de alterações.
- Notificações de vencimentos, importação CSV/OFX sem duplicatas e PWA instalável.
- CI com cobertura PHPUnit e testes Playwright em desktop e mobile.

## Stack

| Camada | Tecnologia |
| --- | --- |
| Backend | PHP 8.5, Laravel 13, Eloquent |
| Banco | PostgreSQL 17 local ou Supabase PostgreSQL |
| Frontend | Blade, Tailwind CSS 4, jQuery 3, Chart.js 4 |
| Autenticação | Laravel Breeze |
| Desenvolvimento | Docker Compose, Nginx, PHP-FPM, Node 22 |
| Serverless | Vercel + `vercel-php@0.9.0` |

## Arquitetura

Os controllers coordenam HTTP e autorização. Form Requests concentram validação e normalização. Services implementam regras financeiras e transações atômicas. Models, enums e policies formam a camada de domínio. As views Blade usam componentes reutilizáveis, enquanto os módulos JavaScript cuidam de formulários, feedback visual e gráficos.

```text
app/
├── Enums/                 # estados e tipos do domínio
├── Http/
│   ├── Controllers/       # endpoints web
│   ├── Middleware/        # cadastro condicional
│   └── Requests/          # validação de entrada
├── Models/                # entidades Eloquent
├── Policies/              # isolamento e autorização
└── Services/              # cálculos e fluxos financeiros
database/
├── factories/             # dados para testes
├── migrations/            # estrutura relacional e índices
└── seeders/               # ambiente demonstrativo local
resources/
├── css/                   # tema e componentes Tailwind
├── js/                    # jQuery, Chart.js e interações
└── views/                 # telas e componentes Blade
tests/
├── Feature/               # fluxos, segurança e relatórios
└── Unit/                  # cálculos monetários
api/index.php              # entrada serverless
vercel.json                # runtime e roteamento Vercel
```

## Ambiente local com Docker

Pré-requisitos: Docker Desktop com Docker Compose. PHP, Composer, Node e PostgreSQL não precisam estar instalados na máquina.

No PowerShell:

```powershell
Copy-Item .env.example .env
docker compose build app
docker compose run --rm --user root app composer install --no-interaction --prefer-dist
docker compose run --rm app php artisan key:generate
docker compose run --rm --user root app npm install
docker compose run --rm app php artisan migrate:fresh --seed --force
docker compose run --rm --user root app npm run build
docker compose up -d
```

Acesse `http://localhost:8080`.

O seeder local cria:

- E-mail: `demo@financi.ai.local`
- Senha: `password`

Essas credenciais são apenas demonstrativas. O seeder recusa execução quando `APP_ENV=production`.

Comandos úteis:

```powershell
docker compose ps
docker compose logs -f app web
docker compose run --rm app php artisan test
docker compose run --rm app ./vendor/bin/pint --test
docker compose run --rm --user root app npm run build
docker compose down
```

Para apagar também o banco e os volumes locais, use `docker compose down -v` somente quando quiser descartar os dados.

## Banco e regras de saldo

Todas as tabelas do domínio ficam no schema `finance`; tabelas do framework também o utilizam. O `search_path` configurado é `finance,public`.

O saldo atual de uma conta é:

```text
saldo inicial
+ receitas pagas
- despesas pagas
+ transferências recebidas
- transferências enviadas
```

Transações pendentes e canceladas não alteram o saldo. Transferências geram duas transações vinculadas e são salvas na mesma transação de banco. Compras no cartão são ligadas automaticamente à fatura correta conforme os dias de fechamento e vencimento; o saldo bancário só é reduzido no pagamento da fatura, evitando dupla contabilização. Valores monetários usam `decimal` e cálculos inteiros em centavos para evitar erros de ponto flutuante.

## Supabase PostgreSQL

Crie um projeto no Supabase e copie os dados exibidos em **Connect**. Não grave chaves ou senhas no repositório.

Conexão direta ou Session Pooler:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=db.SEUPROJETO.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=SUA_SENHA
DB_SCHEMA=finance
DB_SSLMODE=require
DB_POOL_MODE=session
```

Transaction Pooler, indicado para muitas conexões serverless curtas:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=HOST_DO_POOLER
DB_PORT=6543
DB_DATABASE=postgres
DB_USERNAME=postgres.SEUPROJETO
DB_PASSWORD=SUA_SENHA
DB_SCHEMA=finance
DB_SSLMODE=require
DB_POOL_MODE=transaction
```

Use exatamente host, porta e usuário apresentados pelo painel do seu projeto. `DB_POOL_MODE=transaction` faz o driver usar prepared statements emulados, compatíveis com esse modo de pool.

Para criar a estrutura no Supabase, execute de uma estação segura:

```powershell
docker compose run --rm app php artisan migrate --force
```

O comando usa as variáveis do `.env` ativo. Faça backup antes de apontar um ambiente local para um banco com dados reais. Não execute `migrate:fresh` nem o seeder de demonstração em produção.

## Testes e qualidade

```powershell
docker compose run --rm app php artisan test
docker compose run --rm app ./vendor/bin/pint --test
docker compose run --rm app php artisan view:cache
docker compose run --rm --user root app npm run build
```

A suíte cobre autenticação, autorização entre usuários, validação, saldo, transferências, parcelamentos, dívidas, investimentos, orçamentos, metas, dashboard, relatórios e exportações.

O GitHub Actions também executa cobertura e testes ponta a ponta. Os workflows de migração, deploy e backup usam um environment protegido `production` e os secrets `APP_KEY`, `SUPABASE_DB_URL` e `VERCEL_TOKEN`.

## Lembretes automáticos

`php artisan finance:send-reminders` cria notificações internas para obrigações dos próximos sete dias. O scheduler está configurado para 08:00; em produção, conecte `php artisan schedule:run` a um cron externo.

## E-mail e Gmail

O ambiente local usa `MAIL_MAILER=log` por padrão e, portanto, não entrega mensagens. Para Gmail SMTP, configure no `.env`:

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=seu-email@gmail.com
MAIL_PASSWORD=SENHA_DE_APP
MAIL_SCHEME=
MAIL_FROM_ADDRESS=seu-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

Ative a verificação em duas etapas na conta Google e gere uma senha de app exclusiva. Depois limpe o cache e teste sem expor a senha:

```powershell
docker compose exec -T app php artisan optimize:clear
docker compose exec -T app php artisan mail:diagnose --send=seu-email@gmail.com
```

Se `storage/logs/laravel.log` tiver sido criado por um comando executado como root:

```powershell
docker compose exec -T --user root app chown -R www-data:www-data storage bootstrap/cache
```

Execute comandos Artisan normalmente, sem `--user root`, para preservar as permissões.

## Vercel

O projeto está preparado para Vercel, mas não executa deploy automaticamente. A configuração usa `api/index.php` como entrada Laravel, armazenamento gravável em `/tmp`, ativos compilados em `public/build` e o runtime comunitário `vercel-php@0.9.0`.

Antes de enviar:

```powershell
docker compose run --rm --user root app composer install --no-dev --optimize-autoloader
docker compose run --rm --user root app npm ci
docker compose run --rm --user root app npm run build
```

O diretório `public/build` deve ser versionado, pois os ativos precisam existir no pacote do deploy. Restaure as dependências de desenvolvimento localmente com `composer install` após preparar um artefato dessa forma.

Cadastre estas variáveis no projeto Vercel:

```dotenv
APP_NAME=financi.ai
APP_ENV=production
APP_KEY=base64:CHAVE_GERADA
APP_DEBUG=false
APP_URL=https://SEU_DOMINIO
APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=pt_BR
APP_TIMEZONE=America/Sao_Paulo
ALLOW_REGISTRATION=false

LOG_CHANNEL=stderr
LOG_LEVEL=warning

DB_CONNECTION=pgsql
DB_HOST=HOST_DO_SUPABASE_OU_POOLER
DB_PORT=6543
DB_DATABASE=postgres
DB_USERNAME=USUARIO_DO_SUPABASE
DB_PASSWORD=SENHA_DO_SUPABASE
DB_SCHEMA=finance
DB_SSLMODE=require
DB_POOL_MODE=transaction

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=sync

LARAVEL_STORAGE_PATH=/tmp/storage
VIEW_COMPILED_PATH=/tmp/storage/framework/views
APP_CONFIG_CACHE=/tmp/config.php
APP_EVENTS_CACHE=/tmp/events.php
APP_PACKAGES_CACHE=/tmp/packages.php
APP_ROUTES_CACHE=/tmp/routes.php
APP_SERVICES_CACHE=/tmp/services.php
```

Gere a chave fora da Vercel:

```powershell
docker compose run --rm app php artisan key:generate --show
```

Copie apenas o valor retornado para `APP_KEY`. Execute as migrations no Supabase antes do primeiro acesso. A documentação operacional detalhada está em [docs/deploy-vercel.md](docs/deploy-vercel.md).

## Cadastro e segurança

Em ambiente local, `ALLOW_REGISTRATION=true` permite criar contas. Em produção, prefira `false`; nesse modo as rotas GET e POST de cadastro retornam 404 e o link deixa de ser exibido.

Cada tabela de negócio possui `user_id`. Policies impedem leitura ou alteração de recursos de outra pessoa, inclusive por IDs manipulados. Senhas usam o hash padrão seguro do Laravel e todos os formulários mutáveis incluem CSRF.

## Solução de problemas

**A porta 8080 está ocupada**

Defina outra porta antes de subir os serviços:

```powershell
$env:APP_PORT=8081
docker compose up -d
```

**A porta 5432 está ocupada**

```powershell
$env:DB_FORWARD_PORT=5433
docker compose up -d
```

**O schema `finance` não existe**

Em um volume PostgreSQL antigo, crie o schema ou recrie apenas o ambiente local. O script `docker/postgres/init.sql` roda na primeira inicialização do volume.

**CSS ou JavaScript não aparecem**

```powershell
docker compose run --rm --user root app npm run build
docker compose run --rm app php artisan optimize:clear
```

**Erro de escrita no serverless**

Confirme as variáveis apontando cache, views e storage para `/tmp`, conforme a seção Vercel.

**Erro de conexão com Supabase**

Confira SSL, usuário completo do pooler, porta e modo. Para Transaction Pooler, use `DB_POOL_MODE=transaction`.

## Checklist antes de produção

- Trocar todas as credenciais e manter `.env` fora do Git.
- Gerar uma `APP_KEY` exclusiva.
- Definir `APP_DEBUG=false` e `ALLOW_REGISTRATION=false`, se o cadastro público não for desejado.
- Criar backup e executar apenas `php artisan migrate --force`.
- Compilar e versionar `public/build`.
- Configurar domínio e `APP_URL` com HTTPS.
- Validar login, criação de transação, exportação CSV e isolamento entre usuários.
- Configurar monitoramento de erros e política de backup no provedor do banco.
