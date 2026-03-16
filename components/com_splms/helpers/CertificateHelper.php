<?php
/**
 * @package    Guideway LMS
 * @subpackage com_splms
 * GUIDEWAY CUSTOM - Certificado Frente e Verso V21 (Ajuste Final do Tamanho da Fonte do Curso)
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
            // ── Dados dinâmicos ──────────────────────────────────────────
            $db = Factory::getDbo();
	    $queryAluno = $db->getQuery(true)
   	      ->select($db->quoteName('name'))
   	      ->from($db->quoteName('#__users'))
  	      ->where($db->quoteName('id') . ' = ' . (int) $item->userid);
	    $db->setQuery($queryAluno);
	    $aluno = htmlspecialchars($db->loadResult() ?: '[Nome do Aluno]');

	    $queryCurso = $db->getQuery(true)
	         ->select($db->quoteName('title'))
		 ->from($db->quoteName('#__splms_courses'))
		 ->where($db->quoteName('id') . ' = ' . (int) $item->course_id);
	   $db->setQuery($queryCurso);
	   $curso = htmlspecialchars($db->loadResult() ?: '[Nome do Curso]');
            
            $data       = (!empty($item->issue_date) && $item->issue_date !== '0000-00-00') ? date('d/m/Y', strtotime($item->issue_date)) : date('d/m/Y');
            $codigo     = isset($item->certificate_no) ? htmlspecialchars($item->certificate_no) : 'GW-DEFAULT-000';
            $organizacao = isset($item->organization) ? htmlspecialchars($item->organization) : 'Guideway LMS';
            $instrutor  = isset($item->instructor) && !empty($item->instructor) ? htmlspecialchars($item->instructor) : 'Instrutor Guideway';
            // GUIDEWAY CUSTOM -  Busca carga horaria e data de conclusao reais do banco
	    $db = Factory::getDbo();

	    $queryCarga = $db->getQuery(true)
   	      ->select($db->quoteName('workload_hours'))
   	      ->from($db->quoteName('#__splms_courses'))
   	      ->where($db->quoteName('id') . ' = ' . (int) $item->course_id);
	   $db->setQuery($queryCarga);
	   $cargaHoraria = $db->loadResult() ?: '40';

	   $queryConc = $db->getQuery(true)
   	     ->select($db->quoteName('created'))
   	     ->from($db->quoteName('#__splms_useritems'))
   	     ->where($db->quoteName('user_id') . ' = ' . (int) $item->userid)
   	     ->where($db->quoteName('item_id') . ' = ' . (int) $item->course_id)
   	     ->where($db->quoteName('item_type') . ' = ' . $db->quote('course'));
	  $db->setQuery($queryConc);
	  $dataConclusao = $db->loadResult();
	  $data = $dataConclusao ? date('d/m/Y', strtotime($dataConclusao)) : $data;
            // ─────────────────────────────────────────────────────────────

            $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
            $pdf->SetCreator('Guideway LMS');
            $pdf->SetAuthor($organizacao);
            $pdf->SetTitle('Certificado - ' . $aluno);
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(0, 0, 0);
            $pdf->SetAutoPageBreak(FALSE, 0); 

            // ==========================================
            // PÁGINA 1: FRENTE
            // ==========================================
            $pdf->AddPage();

            $htmlFrente = '
            <table width="100%" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF" style="font-family: helvetica, arial, sans-serif;">
                <tr><td height="25" bgcolor="#111827"></td></tr>
                <tr>
                    <td style="padding: 30px 40px;">
                        
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td width="70%">
                                    <h1 style="font-size: 24px; color: #111827; margin: 0; text-transform: uppercase; letter-spacing: 2px;">Certificado de Conclusão</h1>
                                    <p style="font-size: 11px; color: #666666;">
                                        Código de Autenticidade: <strong style="color: #111827;">' . $codigo . '</strong> &nbsp;&nbsp;|&nbsp;&nbsp; 
                                        Data de Conclusão: <strong style="color: #111827;">' . $data . '</strong>
                                    </p>
                                </td>
                                <td width="30%" style="text-align: right; vertical-align: top; padding-right: 15px;">
                                    <span style="font-size: 18px; font-weight: bold; color: #111827;">GUIDEWAY <span style="font-weight: normal; color: #666666;">LMS</span></span>
                                </td>
                            </tr>
                        </table>
                        <br/><br/>
                        
                        <table width="100%" cellpadding="0" cellspacing="0" style="border: 1px solid #D1D5DB; background-color: rgba(240, 239, 235, 0.7);">
                            <tr>
                                <td style="padding: 35px; text-align: center;">
                                    <p style="font-size: 16px; color: #333333; font-style: italic; margin-top: 0; margin-bottom: 15px;">Certificamos com orgulho que</p>
                                    
                                    <h2 style="font-size: 24px; color: #111827; margin: 0;"><strong>' . $aluno . '</strong></h2>
                                    <br/>
                                    
                                    <p style="font-size: 16px; line-height: 1.8; color: #333333; font-style: italic; margin-top: 0; margin-bottom: 0;">
                                        concluiu com êxito todos os requisitos acadêmicos do curso <br />
                                        
                                        <strong style="font-size: 16px; color: #111827; font-style: normal;">' . $curso . '</strong><br />
                                        
                                        cumprindo a carga horária de <strong style="font-style: normal; color: #111827;">' . $cargaHoraria . ' horas</strong>, na modalidade online.
                                    </p>
                                </td>
                            </tr>
                        </table>
                        
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td height="45"></td> 
                            </tr>
                            <tr>
                                <td width="33%"></td>
                                <td width="34%" style="vertical-align: bottom; text-align: center;">
                                    <div style="width: 250px; margin: 0 auto; border-top: 1px solid #1A1A1A; padding-top: 8px;">
                                        <strong style="font-size: 16px; color: #111827;">' . $instrutor . '</strong><br/>
                                        <span style="font-size: 13px; color: #666666;">Instrutor do curso</span>
                                    </div>
                                </td>
                                <td width="33%"></td>
                            </tr>
                        </table>

                    </td>
                </tr>
            </table>';

            $pdf->writeHTML($htmlFrente, true, false, true, false, '');


            // ==========================================
            // PÁGINA 2: VERSO (CONTEÚDO PROGRAMÁTICO E QR CODE CENTRALIZADO)
            // ==========================================
            $pdf->AddPage();

            $htmlVerso = '
            <table width="100%" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF" style="font-family: helvetica, arial, sans-serif;">
                <tr><td height="25" bgcolor="#111827"></td></tr>
                <tr>
                    <td style="padding: 30px 40px;">
                        
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td width="70%">
                                    <h1 style="font-size: 24px; color: #111827; margin: 0; text-transform: uppercase; letter-spacing: 2px;">Conteúdo Programático</h1>
                                    <p style="font-size: 11px; color: #666666;">
                                        ' . $curso . ': <strong style="color: #111827;">' . $cargaHoraria . ' horas</strong>
                                    </p>
                                </td>
                                <td width="30%" style="text-align: right; vertical-align: top; padding-right: 15px;">
                                    <span style="font-size: 18px; font-weight: bold; color: #111827;">GUIDEWAY <span style="font-weight: normal; color: #666666;">LMS</span></span>
                                </td>
                            </tr>
                        </table>
                        <br/><br/>
                        
                        <table width="100%" cellpadding="8" cellspacing="0" style="border: 1px solid #D1D5DB; font-size: 16px; color: #333333; background-color: rgba(240, 239, 235, 0.7);">
                            <tr>
                                <td width="10%" style="border-bottom: 1px solid #D1D5DB;"><strong>Módulo</strong></td>
                                <td width="70%" style="border-bottom: 1px solid #D1D5DB;"><strong>Descrição do Conteúdo Acadêmico</strong></td>
                                <td width="20%" style="border-bottom: 1px solid #D1D5DB; text-align: center;"><strong>Status</strong></td>
                            </tr>
                            <tr>
                                <td style="border-bottom: 1px solid #D1D5DB;">01</td>
                                <td style="border-bottom: 1px solid #D1D5DB;">Introdução e Conceitos Fundamentais do Framework</td>
                                <td style="border-bottom: 1px solid #D1D5DB; text-align: center; color: #059669;">Concluído</td>
                            </tr>
                            <tr>
                                <td style="border-bottom: 1px solid #D1D5DB;">02</td>
                                <td style="border-bottom: 1px solid #D1D5DB;">Desenvolvimento de Lógica de Negócio e Persistência de Dados</td>
                                <td style="border-bottom: 1px solid #D1D5DB; text-align: center; color: #059669;">Concluído</td>
                            </tr>
                            <tr>
                                <td style="border-bottom: 1px solid #D1D5DB;">03</td>
                                <td style="border-bottom: 1px solid #D1D5DB;">Integração de APIs e Serviços Externos</td>
                                <td style="border-bottom: 1px solid #D1D5DB; text-align: center; color: #059669;">Concluído</td>
                            </tr>
                            <tr>
                                <td style="border-bottom: 1px solid #D1D5DB;">04</td>
                                <td style="border-bottom: 1px solid #D1D5DB;">Segurança, Otimização e Deploy de Aplicações</td>
                                <td style="border-bottom: 1px solid #D1D5DB; text-align: center; color: #059669;">Concluído</td>
                            </tr>
                             <tr>
                                <td style="border-bottom: 1px solid #D1D5DB;">05</td>
                                <td style="border-bottom: 1px solid #D1D5DB;">Trabalho Final de Conclusão de Curso (TCC)</td>
                                <td style="border-bottom: 1px solid #D1D5DB; text-align: center; color: #059669;">Aprovado</td>
                            </tr>
                        </table>
                        
                        <div style="height: 60px;"></div>
                        
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="text-align: center; border-top: 1px solid #E5E5E5; padding-top: 15px;">
                                    <p style="font-size: 9px; color: #999999; line-height: 1.5;">
                                        Este certificado é emitido em conformidade com as normas da ' . $organizacao . '.<br/>
                                        A autenticidade deste documento pode ser verificada através do QR Code acima ou pelo código: ' . $codigo . '
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>';

            $pdf->writeHTML($htmlVerso, true, false, true, false, '');

            // Injeção do QR Code
            $urlValidacao = 'https://www.guidewaylms.com/index.php?option=com_splms&view=validate&hash=' . $codigo;
            $pdf->write2DBarcode($urlValidacao, 'QRCODE,H', 136, 165, 24, 24, array('border' => false), 'N');
            
            $pdf->SetXY(136, 190);
            $pdf->SetFontSize(7);
            $pdf->Cell(24, 5, 'Validar certificado', 0, 0, 'C');

            $pdf->Output('Certificado_' . $aluno . '.pdf', 'I');
            exit;

        } catch (Exception $e) {
            die("Erro ao gerar PDF: " . $e->getMessage());
        }
    }
}