-- =====================================================
-- POR NÓS BARBEARIA - Schema do banco de dados
-- Importar via phpMyAdmin ou: mysql -u root -p < schema.sql
-- =====================================================

CREATE DATABASE IF NOT EXISTS barbearia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE barbearia;

-- ---------------------------------------------------
-- Administradores do painel
-- ---------------------------------------------------
CREATE TABLE admin_usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- O usuário administrador padrão é criado executando setup.php (na raiz do projeto)
-- uma única vez pelo navegador. Isso gera o hash de senha real no seu servidor PHP,
-- em vez de depender de um hash fixo neste arquivo. Apague setup.php depois de usar.

-- ---------------------------------------------------
-- Configurações gerais da barbearia (chave/valor)
-- ---------------------------------------------------
CREATE TABLE configuracoes (
    chave VARCHAR(60) PRIMARY KEY,
    valor TEXT
) ENGINE=InnoDB;

INSERT INTO configuracoes (chave, valor) VALUES
('nome_barbearia', 'POR NÓS BARBEARIA'),
('whatsapp', '5521987674006'),
('duracao_atendimento', '40'),
('almoco_inicio', '13:00'),
('almoco_fim', '15:00'),
('texto_hero', 'Estilo, precisão e tradição em cada corte.'),
('texto_sobre', 'Na POR NÓS BARBEARIA, cada atendimento é feito com cuidado e atenção aos detalhes. Agende seu horário online e evite filas.'),
('endereco', '');

-- ---------------------------------------------------
-- Dias e horários de funcionamento (0=domingo ... 6=sábado)
-- ---------------------------------------------------
CREATE TABLE dias_funcionamento (
    dia_semana TINYINT PRIMARY KEY,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    hora_abertura TIME NOT NULL DEFAULT '09:00:00',
    hora_fechamento TIME NOT NULL DEFAULT '20:00:00',
    ordem_chegada TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB;

INSERT INTO dias_funcionamento (dia_semana, ativo, hora_abertura, hora_fechamento, ordem_chegada) VALUES
(0, 0, '09:00:00', '20:00:00', 0), -- Domingo: fechado
(1, 1, '09:00:00', '20:00:00', 0), -- Segunda
(2, 1, '09:00:00', '20:00:00', 0), -- Terça
(3, 1, '09:00:00', '20:00:00', 0), -- Quarta
(4, 1, '09:00:00', '20:00:00', 0), -- Quinta
(5, 1, '09:00:00', '20:00:00', 1), -- Sexta: ordem de chegada
(6, 1, '09:00:00', '20:00:00', 1); -- Sábado: ordem de chegada

-- ---------------------------------------------------
-- Serviços oferecidos
-- ---------------------------------------------------
CREATE TABLE servicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    preco DECIMAL(10,2) NOT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    ordem INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

INSERT INTO servicos (nome, preco, ordem) VALUES
('Corte com Máquina', 20.00, 1),
('Corte com Navalha', 25.00, 2),
('Corte Disfarçado', 30.00, 3),
('Corte na Tesoura', 35.00, 4),
('Pigmentação', 15.00, 5),
('Barba', 25.00, 6),
('Sobrancelha', 7.00, 7),
('Corte Disfarçado + Tesoura', 35.00, 8);

-- ---------------------------------------------------
-- Agendamentos (também usada para bloqueios manuais do admin)
-- status: reservado | concluido | cancelado | bloqueado
-- ---------------------------------------------------
CREATE TABLE agendamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_nome VARCHAR(150) NULL,
    cliente_telefone VARCHAR(30) NULL,
    data DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fim TIME NOT NULL,
    valor_total DECIMAL(10,2) NOT NULL DEFAULT 0,
    status ENUM('reservado','concluido','cancelado','bloqueado') NOT NULL DEFAULT 'reservado',
    observacao VARCHAR(255) NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_data_hora (data, hora_inicio),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- ---------------------------------------------------
-- Serviços escolhidos em cada agendamento (N:N)
-- ---------------------------------------------------
CREATE TABLE agendamento_servicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    agendamento_id INT NOT NULL,
    servico_id INT NOT NULL,
    nome_servico VARCHAR(100) NOT NULL,
    preco DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (agendamento_id) REFERENCES agendamentos(id) ON DELETE CASCADE,
    FOREIGN KEY (servico_id) REFERENCES servicos(id) ON DELETE RESTRICT
) ENGINE=InnoDB;
