<?php
/**
 * @package     Guideway LMS
 * @subpackage  com_splms
 * GUIDEWAY CUSTOM -  Injeção de dados dinâmicos no certificado PDF
 */

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

class CertificateHelper
{
    public static function gerarPdf($item)
    {
        $libPath = JPATH_SITE . '/components/com_splms/libraries/tcpdf/TCPDF-main/tcpdf.php';

        if (!file_exists($libPath)) {
            die("Erro: Biblioteca TCPDF não encontrada.");
        }

        require_once $libPath;

        try {
            // ── Dados dinâmicos vindos do banco ──────────────────────────
            $aluno      = (isset($item->student_info->name) && $item->student_info->name)
                            ? htmlspecialchars($item->student_info->name)
                            : 'Aluno';

            $curso      = isset($item->course)
                            ? htmlspecialchars($item->course)
                            : 'Curso';

            $data       = (!empty($item->issue_date) && $item->issue_date !== '0000-00-00')
                            ? date('d/m/Y', strtotime($item->issue_date))
                            : date('d/m/Y');

            $codigo     = isset($item->certificate_no)
                            ? htmlspecialchars($item->certificate_no)
                            : '';

            $organizacao = isset($item->organization)
                            ? htmlspecialchars($item->organization)
                            : 'Guideway LMS';

            $instrutor  = isset($item->instructor)
                            ? htmlspecialchars($item->instructor)
                            : '';
            // ─────────────────────────────────────────────────────────────

            $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
            $pdf->SetCreator('Guideway LMS');
            $pdf->SetAuthor($organizacao);
            $pdf->SetTitle('Certificado - ' . $aluno);
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(15, 15, 15);
            $pdf->AddPage();

            // Logo (se existir)
            if (!empty($item->logo)) {
                $logoPath = JPATH_SITE . '/' . $item->logo;
                if (file_exists($logoPath)) {
                    $pdf->Image($logoPath, 120, 15, 50, '', '', '', 'T', false, 300, 'C');
                }
            }

            // HTML com placeholders substituídos pelos dados reais
            $html = '
                <div style="text-align:center; border: 8px double #2c3e50; padding: 40px;">
                    <h1 style="color:#2c3e50; font-size:28pt; margin-bottom:5px;">CERTIFICADO DE CONCLUSÃO</h1>
                    <p style="font-size:12pt; color:#555;">Emitido por <strong>' . $organizacao . '</strong></p>
                    <br>
                    <p style="font-size:13pt;">Certificamos que</p>
                    <h2 style="font-size:22pt; color:#e74c3c; margin:10px 0;">' . $aluno . '</h2>
                    <p style="font-size:13pt;">concluiu com êxito o curso:</p>
                    <h3 style="font-size:17pt; color:#2c3e50; margin:10px 0;">' . $curso . '</h3>
                    <br>
                    <p style="font-size:11pt;">Data de emissão: <strong>' . $data . '</strong></p>
                    ' . ($instrutor ? '<p style="font-size:11pt;">Instrutor: <strong>' . $instrutor . '</strong></p>' : '') . '
                    <br><br>
                    <p style="font-size:9pt; color:#888;">Código de verificação: ' . $codigo . '</p>
                </div>
            ';

            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Output('Certificado_' . $aluno . '.pdf', 'I');
            exit;

        } catch (Exception $e) {
            die("Erro ao gerar PDF: " . $e->getMessage());
        }
    }
}
