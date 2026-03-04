<?php
/**
 * @package     Guideway LMS
 * @subpackage  com_splms
 * @author      Michael
 */

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;

class CertificateHelper
{
    public static function gerarPdf($item) 
    {
        // 1. Caminho correto da biblioteca conforme seu print do VS Code
        $libPath = JPATH_SITE . '/components/com_splms/libraries/tcpdf/TCPDF-main/tcpdf.php';

        if (!file_exists($libPath)) {
            die("Erro: Biblioteca não encontrada em: " . $libPath);
        }

        require_once $libPath;

        try {
            // 2. Criar e configurar o objeto $pdf (Isso resolve o erro de sintaxe)
            $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
            
            $pdf->SetCreator('Guideway LMS');
            $pdf->SetAuthor('Michael');
            $pdf->SetTitle('Certificado de Conclusão');
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(10, 10, 10);
            $pdf->AddPage();

            // 3. Montar o HTML com os dados que o Joomla recuperou
            // Note que usamos caminhos absolutos para imagens no TCPDF
            $logoPath = JPATH_SITE . '/' . $item->logo;
            
            $html = '
                <div style="text-align:center; border: 10px solid #2c3e50; padding: 50px;">
                    <br><br>
                    <h1 style="color: #2c3e50; font-size: 30pt;">CERTIFICADO</h1>
                    <p style="font-size: 14pt;">Certificamos que</p>
                    <h2 style="font-size: 24pt; color: #e74c3c;">' . $item->student_info->name . '</h2>
                    <p style="font-size: 14pt;">concluiu com êxito o treinamento em:</p>
                    <h3 style="font-size: 18pt;">' . $item->course . '</h3>
                    <p style="font-size: 12pt;">Emitido por: ' . $item->organization . '</p>
                    <p style="font-size: 10pt;">Código do Certificado: ' . $item->certificate_no . '</p>
                </div>
            ';

            // 4. Se existir logo, insere antes do texto
            if (file_exists($logoPath) && !empty($item->logo)) {
                $pdf->Image($logoPath, 125, 20, 40, '', '', '', 'T', false, 300, 'C', false, false, 0, false, false, false);
            }

            $pdf->writeHTML($html, true, false, true, false, '');
            
            // 5. Saída do arquivo
            $pdf->Output('Certificado_' . $item->student_info->name . '.pdf', 'I');
            exit;

        } catch (Exception $e) {
            die("Erro ao gerar PDF: " . $e->getMessage());
        }
    }

    // Mantendo sua função generate antiga caso precise dela para outros hooks
    public static function generate($studentName, $courseTitle, $hash)
    {
        // ... (seu código da função generate permanece aqui se desejar)
    }
}