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
| Banco | PostgreSQL 17 (Docker local, ou self-hosted na VPS) |
| Frontend | Blade, Tailwind CSS 4, jQuery 3, Chart.js 4 |
| Autenticação | Laravel Breeze |
| Desenvolvimento | Docker Compose, Nginx, PHP-FPM, Node 22 |

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

## Banco em staging e produção (VPS)

Staging e produção rodam na mesma VPS, mas cada ambiente tem seu **próprio container PostgreSQL**, isolado (`financiai_staging` e `financiai_prod`, respectivamente) — nada é compartilhado entre os dois. Não há pooler em nenhum lugar: local, staging e produção usam sempre `DB_POOL_MODE=session`, direto contra um Postgres self-hosted comum.

```dotenv
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=financiai_prod
DB_USERNAME=SEU_USUARIO
DB_PASSWORD=SUA_SENHA
DB_SCHEMA=finance
DB_SSLMODE=disable
DB_POOL_MODE=session
```

`DB_HOST=db` é o nome do serviço Docker do Postgres, resolvido internamente pela rede do Compose — não é um host público. Cada ambiente mantém seu próprio `.env` (nunca versionado) na respectiva pasta da VPS.

As migrations em staging e produção rodam automaticamente a cada deploy (veja `.github/workflows/deploy.yml`). Para rodar manualmente, sem um deploy completo, use o workflow `migrate-vps.yml` (`workflow_dispatch`, escolhendo o ambiente) ou, direto na VPS:

```bash
docker compose -p financiai-prod -f compose.prod.yaml exec -T app php artisan migrate --force
```

Faça backup antes de qualquer migration em ambiente com dados reais — veja `.github/workflows/backup-vps.yml`. Não execute `migrate:fresh` nem o seeder de demonstração fora do ambiente local.

## Testes e qualidade

```powershell
docker compose run --rm app php artisan test
docker compose run --rm app ./vendor/bin/pint --test
docker compose run --rm app php artisan view:cache
docker compose run --rm --user root app npm run build
```

A suíte cobre autenticação, autorização entre usuários, validação, saldo, transferências, parcelamentos, dívidas, investimentos, orçamentos, metas, dashboard, relatórios e exportações.

O GitHub Actions também executa cobertura e testes ponta a ponta. Os workflows de migração, deploy e backup usam um environment protegido `production` e os secrets `VPS_HOST` e `VPS_SSH_KEY`.

## Branches e ambientes

- `dev` é a branch de desenvolvimento e homologação.
- `main` é exclusiva para produção.
- mudanças chegam à produção por Pull Request de `dev` para `main`;
- o próprio merge em `main` já dispara o deploy de produção, com aprovação manual via GitHub Environments.

O fluxo completo está documentado em [docs/branching.md](docs/branching.md).

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

## Deploy (VPS)

Staging e produção rodam na mesma VPS (Hostinger), como dois projetos Docker Compose totalmente separados — clones, `.env`, containers e bancos próprios, nada compartilhado:

| | Staging | Produção |
| --- | --- | --- |
| Branch | `dev` | `main` |
| Caminho na VPS | `/var/www/financiai-staging` | `/var/www/financiai` |
| Projeto Compose (`-p`) | `financiai-staging` | `financiai-prod` |
| Porta interna | `127.0.0.1:8081` | `127.0.0.1:8080` |
| Domínio | `staging.financiai.cloud` | `financiai.cloud`, `www.financiai.cloud` |

`compose.prod.yaml` é o mesmo arquivo, versionado no repositório, para os dois ambientes — o que muda é só o `.env` (nunca versionado) e a porta exposta de cada um. O Nginx do host (instalado via `apt`, fora do Docker) faz o proxy reverso de cada domínio para a porta interna do container correspondente e cuida do HTTPS via Certbot/Let's Encrypt. O container Nginx da aplicação (dentro do Docker) escuta só em `127.0.0.1`, nunca exposto direto à internet.

O deploy é automático via GitHub Actions por SSH (`.github/workflows/deploy.yml`):

- push em `dev` → deploy em staging, sem aprovação manual;
- push em `main` → deploy em produção, atrás de aprovação manual (GitHub Environments com reviewers obrigatórios).

Cada job roda, na VPS: `git pull`, `docker compose -p <projeto> -f compose.prod.yaml up -d --build`, `composer install --no-dev --optimize-autoloader`, `php artisan migrate --force`, depois `config:cache`/`route:cache`/`view:cache`. Não há passo manual de variável de ambiente em painel externo — tudo fica no `.env` de cada pasta na própria VPS.

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

## Checklist antes de produção

- Trocar todas as credenciais e manter `.env` fora do Git.
- Gerar uma `APP_KEY` exclusiva.
- Definir `APP_DEBUG=false` e `ALLOW_REGISTRATION=false`, se o cadastro público não for desejado.
- Criar backup e executar apenas `php artisan migrate --force`.
- Compilar e versionar `public/build`.
- Configurar domínio e `APP_URL` com HTTPS.
- Validar login, criação de transação, exportação CSV e isolamento entre usuários.
- Configurar monitoramento de erros e política de backup no provedor do banco.
