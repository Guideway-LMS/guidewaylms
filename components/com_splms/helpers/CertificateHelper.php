<?php
/**
 * @package    Guideway LMS
 * @subpackage com_splms
 * GUIDEWAY CUSTOM - Injeção de dados dinâmicos no certificado PDF com Layout V8 (Sem Base64 para evitar Crash de Memória)
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
                            : 'GW-DEFAULT-000';

            $organizacao = isset($item->organization)
                            ? htmlspecialchars($item->organization)
                            : 'Guideway LMS';

            $instrutor  = isset($item->instructor) && !empty($item->instructor)
                            ? htmlspecialchars($item->instructor)
                            : 'Instrutor Guideway';
                            
            $cargaHoraria = isset($item->duration) && !empty($item->duration) 
                            ? htmlspecialchars($item->duration) 
                            : '40';
            // ─────────────────────────────────────────────────────────────

            $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
            $pdf->SetCreator('Guideway LMS');
            $pdf->SetAuthor($organizacao);
            $pdf->SetTitle('Certificado - ' . $aluno);
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            
            // Margens zeradas para a tabela V8 ocupar 100% da A4
            $pdf->SetMargins(0, 0, 0);
            $pdf->SetAutoPageBreak(FALSE, 0); 
            $pdf->AddPage();

            // HTML V8 com a injeção PHP (TAG DE IMAGEM BASE64 REMOVIDA PARA EVITAR OUT OF MEMORY)
            $html = '
            <table width="100%" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF" style="font-family: helvetica, arial, sans-serif;">
                <tr>
                    <td height="35" bgcolor="#EDEBE4"></td>
                </tr>
                <tr>
                    <td style="padding: 35px 40px;">
                        <table width="100%" cellpadding="45" cellspacing="0" style="border: 1px solid #1A1A1A; background-color: #FFFFFF;">
                            <tr>
                                <td>
                                    <table width="100%" cellpadding="0" cellspacing="0" style="border-bottom: 1px solid #E5E5E5; padding-bottom: 20px; margin-bottom: 45px;">
                                        <tr>
                                            <td width="50%" style="text-align: left; vertical-align: bottom;">
                                                <span style="font-size: 22px; font-weight: bold; color: #111827; letter-spacing: 3px;">
                                                    GUIDEWAY <span style="font-weight: normal; color: #666666;">LMS</span>
                                                </span>
                                            </td>
                                            <td width="50%" style="text-align: right; vertical-align: bottom;">
                                                <span style="font-size: 10px; color: #888888; text-transform: uppercase; letter-spacing: 1px;">
                                                    Código de Autenticidade
                                                </span><br/>
                                                <strong style="font-size: 14px; color: #111827; letter-spacing: 1px;">' . $codigo . '</strong>
                                            </td>
                                        </tr>
                                    </table>

                                    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 40px;">
                                        <tr>
                                            <td style="text-align: center;">
                                                <h1 style="font-size: 36px; color: #111827; font-weight: normal; margin: 0; letter-spacing: 6px; text-transform: uppercase;">
                                                    Certificado de Conclusão
                                                </h1>
                                            </td>
                                        </tr>
                                    </table>

                                    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 35px;">
                                        <tr>
                                            <td style="text-align: center;">
                                                <p style="font-size: 16px; color: #666666; margin: 0 0 15px 0; font-style: italic;">
                                                    Certificamos com orgulho que
                                                </p>
                                                <h2 style="font-size: 42px; color: #2563eb; font-weight: bold; margin: 0; letter-spacing: 1px;">
                                                    ' . $aluno . '
                                                </h2>
                                            </td>
                                        </tr>
                                    </table>

                                    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 60px;">
                                        <tr>
                                            <td style="text-align: center; padding: 0 50px;">
                                                <p style="font-size: 18px; line-height: 1.8; color: #333333; margin: 0;">
                                                    concluiu com êxito todos os requisitos acadêmicos do curso <br />
                                                    <strong style="font-size: 24px; color: #111827; display: block; margin: 15px 0;">' . $curso . '</strong>
                                                    cumprindo a carga horária de <strong>' . $cargaHoraria . ' horas</strong>, na modalidade online.
                                                </p>
                                            </td>
                                        </tr>
                                    </table>

                                    <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: 25px;">
                                        <tr>
                                            <td width="50%" style="text-align: left; vertical-align: bottom;">
                                                <div style="border-left: 3px solid #2563eb; padding-left: 15px;">
                                                    <p style="font-size: 14px; color: #666666; line-height: 1.6; margin: 0;">
                                                        <strong style="color: #111827;">Data de Conclusão:</strong> ' . $data . '<br />
                                                        <strong style="color: #111827;">Local e Data:</strong> Porto Alegre – RS, ' . $data . '
                                                    </p>
                                                </div>
                                            </td>
                                            <td width="50%" style="text-align: right; vertical-align: bottom;">
                                                <div style="height: 30px;"></div><br/>
                                                <table align="right" width="280" cellpadding="0" cellspacing="0" style="border-top: 1px solid #1A1A1A;">
                                                    <tr>
                                                        <td style="padding-top: 8px; text-align: center;">
                                                            <strong style="font-size: 16px; color: #111827;">' . $instrutor . '</strong><br/>
                                                            <span style="font-size: 13px; color: #666666; font-style: italic;">Instrutor do curso</span>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>';

            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Output('Certificado_' . $aluno . '.pdf', 'I');
            exit;

        } catch (Exception $e) {
            die("Erro ao gerar PDF: " . $e->getMessage());
        }
    }
}