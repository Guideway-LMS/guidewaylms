-- Script molde: cria a tabela de progresso de lição
-- Observação: substitua #_ pelo prefixo do seu site (ex.: bak_lepgs_, jos_, etc.)
-- Este script NÃO altera nada sozinho; é para versionamento no projeto.

CREATE TABLE #_splms_lesson_progress (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  lesson_id INT,
  status VARCHAR(20) DEFAULT 'iniciado',
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_user_id (user_id),
  INDEX idx_lesson_id (lesson_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
