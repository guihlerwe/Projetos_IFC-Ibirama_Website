-- Script para adicionar campo de timestamp de criação do token
-- Execute este script no seu banco de dados

USE website;

-- Adicionar coluna token_criado_em (execute apenas se a coluna não existir)
ALTER TABLE pessoa
ADD COLUMN token_criado_em DATETIME DEFAULT NULL AFTER token;

-- Se você já tem as colunas token e confirmado, pode comentar ou pular as linhas abaixo
-- Caso contrário, descomente se precisar:

-- ALTER TABLE pessoa
-- ADD COLUMN confirmado TINYINT(1) DEFAULT 0 AFTER area;

-- ALTER TABLE pessoa
-- ADD COLUMN token VARCHAR(32) DEFAULT NULL AFTER confirmado;

-- Limpar contas não verificadas antigas (mais de 10 minutos)
DELETE FROM pessoa 
WHERE confirmado = 0 
AND token_criado_em IS NOT NULL 
AND token_criado_em < DATE_SUB(NOW(), INTERVAL 10 MINUTE);

SELECT 'Coluna token_criado_em adicionada com sucesso!' AS status;
