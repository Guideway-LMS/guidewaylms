/*<?php
/**
 * @package com_splms
 * GUIDEWAY CUSTOM - Joshua - Gerenciador de Arquivos
 * Gerencia salvamento, exclusão e manipulação de arquivos
 */

/*defined('_JEXEC') or die('Restricted access');

require_once __DIR__ . '/UploadValidator.php';

class SplmsFileManager
{
    private $uploadDir;

    public function __construct()
    {
        $this->uploadDir = JPATH_ROOT . '/uploads/trabalhos';
        $this->criarDiretorioSeNaoExistir();
    }

    /**
     * Criar diretório se não existir
     */
/*    private function criarDiretorioSeNaoExistir()
    {
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * Gerar nome seguro para arquivo
     */
  /*  public function gerarNomeSeguro($userId, $lessonId, $extensao)
    {
        $timestamp = time();
        $random = bin2hex(random_bytes(8));
        return sprintf(
            'trabalho_%d_%d_%d_%s.%s',
            $userId,
            $lessonId,
            $timestamp,
            $random,
            $extensao
        );
    }

    /**
     * Salvar arquivo
     * @return array com dados do arquivo ou ['error' => 'mensagem']
     */
    public function salvar($arquivo, $userId, $lessonId)
  /*  {
        $validator = new SplmsUploadValidator();
        
        // Validar
        $resultadoValidacao = $validator->validar($arquivo);
        if ($resultadoValidacao !== true) {
            return ['error' => $resultadoValidacao];
        }

        // Gerar nome seguro
        $extensao = $validator->getExtensao($arquivo['name']);
        $nomeSalvo = $this->gerarNomeSeguro($userId, $lessonId, $extensao);
        $caminhoCompleto = $this->uploadDir . '/' . $nomeSalvo;

        // Mover arquivo
        if (!move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
            return ['error' => 'Erro ao salvar arquivo no servidor'];
        }

        // Retornar dados
        return [
            'success' => true,
            'nome_original' => $arquivo['name'],
            'nome_salvo' => $nomeSalvo,
            'extensao' => $extensao,
            'tamanho' => $arquivo['size'],
            'tipo_mime' => $arquivo['type'],
            'caminho' => $caminhoCompleto
        ];
    }

    /**
     * Deletar arquivo
     */
/*    public function deletar($nomeArquivo)
    {
        $caminho = $this->uploadDir . '/' . $nomeArquivo;
        
        if (file_exists($caminho)) {
            return unlink($caminho);
        }

        return false;
    }

    /**
     * Verificar se arquivo existe
     */
 /*   public function existe($nomeArquivo)
    {
        $caminho = $this->uploadDir . '/' . $nomeArquivo;
        return file_exists($caminho);
    }

    /**
     * Obter caminho completo do arquivo
     */
 /*   public function getCaminho($nomeArquivo)
    {
        return $this->uploadDir . '/' . $nomeArquivo;
    }

    /**
     * Formatar bytes para leitura humana
     */
 /*   public function formatarTamanho($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }
}*/
<?php
/**
 * @package com_splms
 * GUIDEWAY CUSTOM - Joshua - Gerenciador de Arquivos
 * Gerencia salvamento, exclusão e manipulação de arquivos
 * 
 * ATUALIZADO: Fevereiro 2026
 * - Anonimização de nomes (random_bytes)
 * - Armazenamento fora do public_html
 */
defined('_JEXEC') or die('Restricted access');

require_once __DIR__ . '/UploadValidator.php';

class SplmsFileManager
{
    private $uploadDir;
    
    public function __construct()
    {
        // MUDANÇA: Pasta FORA do public_html
        $this->uploadDir = dirname(JPATH_ROOT) . '/uploads_privados/trabalhos';
        $this->criarDiretorioSeNaoExistir();
    }
    
    /**
     * Criar diretório se não existir
     */
    private function criarDiretorioSeNaoExistir()
    {
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    /**
     * Gerar nome seguro e anonimizado para arquivo
     * MUDANÇA: Usa apenas random_bytes, sem expor user_id ou lesson_id
     */
    public function gerarNomeSeguro($userId, $lessonId, $extensao)
    {
        // Gerar hash completamente anonimizado
        $hash = bin2hex(random_bytes(20)); // 40 caracteres hexadecimais
        
        return sprintf('%s.%s', $hash, $extensao);
    }
    
    /**
     * Salvar arquivo
     * 
     * @param array $arquivo Array do arquivo ($_FILES ou Input API)
     * @param int $userId ID do usuário
     * @param int $lessonId ID da lição
     * @return array Informações do arquivo salvo
     */
    public function salvar($arquivo, $userId, $lessonId)
    {
        // Validar arquivo
        $validator = new SplmsUploadValidator();
        $validacao = $validator->validar($arquivo);
        
        if ($validacao !== true) {
            throw new Exception($validacao);
        }
        
        // Pegar extensão
        $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        
        // Gerar nome anonimizado
        $nomeSeguro = $this->gerarNomeSeguro($userId, $lessonId, $extensao);
        $caminhoCompleto = $this->uploadDir . '/' . $nomeSeguro;
        
        // Mover arquivo
        if (!move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
            throw new Exception('Erro ao salvar arquivo no servidor');
        }
        
        // Retornar informações
        return [
            'success' => true,
            'nome_original' => $arquivo['name'], // Guardar para o professor ver
            'nome_salvo' => $nomeSeguro,
            'extensao' => $extensao,
            'tamanho' => $arquivo['size'],
            'tipo_mime' => $arquivo['type'],
            'caminho' => 'uploads_privados/trabalhos/' . $nomeSeguro // Caminho relativo
        ];
    }
    
    /**
     * Excluir arquivo
     */
    public function excluir($nomeArquivo)
    {
        $caminho = $this->uploadDir . '/' . $nomeArquivo;
        
        if (file_exists($caminho)) {
            return unlink($caminho);
        }
        
        return false;
    }
    
    /**
     * Verificar se arquivo existe
     */
    public function existe($nomeArquivo)
    {
        return file_exists($this->uploadDir . '/' . $nomeArquivo);
    }
    
    /**
     * Obter caminho completo do arquivo
     * IMPORTANTE: Use apenas em controllers com validação de permissão
     */
    public function getCaminhoCompleto($nomeArquivo)
    {
        return $this->uploadDir . '/' . $nomeArquivo;
    }
    
    /**
     * Formatar tamanho do arquivo
     */
    public function formatarTamanho($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
