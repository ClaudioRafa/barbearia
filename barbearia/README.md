# POR NÓS BARBEARIA — Sistema de Agendamento

Sistema completo em PHP + MySQL (PDO) + Bootstrap-free CSS próprio + JavaScript puro,
pronto para rodar no XAMPP.

## Instalação

1. **Copie a pasta** `barbearia` inteira para dentro de `htdocs` do seu XAMPP
   (ex: `C:\xampp\htdocs\barbearia` ou `/opt/lampp/htdocs/barbearia`).

2. **Inicie o Apache e o MySQL** no painel do XAMPP.

3. **Crie o banco de dados**: abra o phpMyAdmin (`http://localhost/phpmyadmin`),
   vá em "Importar" e selecione o arquivo `sql/schema.sql`.
   Isso cria o banco `barbearia` com as tabelas, os 8 serviços, os dias de
   funcionamento e as configurações padrão já preenchidas.

4. **Confira a conexão** em `config/conexao.php`. Por padrão já está configurado
   para o XAMPP (`localhost`, usuário `root`, senha em branco). Ajuste se o seu
   ambiente for diferente.

5. **Crie o usuário administrador**: acesse
   `http://localhost/barbearia/setup.php` no navegador e cadastre seu usuário
   e senha. Depois de criar, **apague o arquivo `setup.php`** por segurança
   (ele se bloqueia sozinho depois do primeiro uso, mas é boa prática remover).

6. **Pronto.** Acesse:
   - Site do cliente: `http://localhost/barbearia/`
   - Painel administrativo: `http://localhost/barbearia/admin/login.php`

## Estrutura

```
barbearia/
├── index.php                 # Landing page + fluxo de agendamento (modal)
├── setup.php                 # Criação do admin (apagar após uso)
├── config/conexao.php        # Configuração de acesso ao MySQL
├── includes/functions.php    # Regras de negócio (horários, configs, formatação)
├── api/                      # Endpoints públicos (consultar/criar agendamento)
├── admin/
│   ├── login.php / logout.php
│   ├── index.php             # Dashboard com resumo do dia
│   ├── calendario.php        # Calendário visual (bloquear/liberar horários)
│   ├── agendamentos.php      # Listar, criar, editar, cancelar, concluir, excluir
│   ├── servicos.php          # CRUD de serviços e preços
│   ├── configuracoes.php     # Dados da barbearia, dias/horários, senha
│   ├── includes/             # Layout e autenticação do painel
│   └── api/                  # Endpoints protegidos usados pelo painel
├── assets/css/                # style.css (site) + admin.css (painel)
├── assets/js/                 # main.js (site) + admin.js (painel)
└── sql/schema.sql             # Script de criação do banco
```

## O que já foi implementado (e testado na lógica)

- Geração automática de horários a cada 40 min (configurável), respeitando o
  horário de abertura/fechamento por dia da semana e bloqueando o intervalo
  de almoço.
- Impedimento de agendamento duplicado usando transação + `SELECT ... FOR UPDATE`
  no MySQL — mesmo que dois clientes cliquem "confirmar" ao mesmo tempo, apenas
  um consegue reservar aquele horário.
- Cliente pode escolher um ou vários serviços; o valor total é sempre
  recalculado no servidor (nunca confia no preço enviado pelo navegador).
- Painel admin protegido por sessão + senha com hash (bcrypt via
  `password_hash`), com proteção CSRF em todas as rotas que alteram dados.
- Calendário visual admin com os 5 estados pedidos: Disponível, Reservado,
  Concluído, Cancelado, Bloqueado — clicando em um horário livre você bloqueia,
  clicando em um bloqueado você libera.
- Todas as configurações (nome, WhatsApp, textos, dias, horários, duração do
  atendimento, serviços/preços, senha) são editáveis pelo painel, sem precisar
  mexer em código.
- Botão de WhatsApp flutuante e botão de confirmação por WhatsApp após o
  agendamento, ambos apontando para (21) 98767-4006.
- Layout responsivo (mobile-first nos pontos críticos: modal de agendamento,
  grade de horários, tabelas do painel).

## O que testar manualmente antes de entregar ao cliente

1. Criar um agendamento pelo site como cliente.
2. Tentar agendar o mesmo horário em duas abas ao mesmo tempo — a segunda deve
   ser recusada com "horário reservado".
3. Bloquear um horário no calendário do admin e confirmar que ele some da
   lista de horários disponíveis no site.
4. Cancelar um agendamento no painel e confirmar que o horário volta a ficar
   disponível para novos clientes.
5. Login administrativo com usuário/senha errados (deve falhar) e corretos
   (deve entrar).
6. Alterar preço de um serviço e conferir que o site público reflete a
   mudança imediatamente.
7. Testar em um celular real (ou modo responsivo do navegador) o fluxo
   completo de agendamento.

## Sobre o pedido de usar Supabase

O cliente original pediu Supabase (PostgreSQL), mas este projeto foi construído
em PHP + MySQL por decisão sua, para se encaixar no seu ambiente XAMPP atual.
Toda a lógica de negócio (regras de horário, bloqueios, permissões) está
implementada da mesma forma — a diferença é só o banco por trás.
