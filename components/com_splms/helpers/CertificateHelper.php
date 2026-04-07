<?php

/**
 * @package    Guideway LMS
 * @subpackage com_splms
 * GUIDEWAY CUSTOM - Certificado Frente e Verso V31
 * Changelog V31: Logos Parceiros e Assinatura do Instrutor via caminho físico (sem Base64)
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
            $db->setQuery("SET NAMES 'utf8mb4'");
            $db->execute();
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

            $data        = (!empty($item->issue_date) && $item->issue_date !== '0000-00-00') ? date('d/m/Y', strtotime($item->issue_date)) : date('d/m/Y');
            $codigo      = isset($item->certificate_no) ? htmlspecialchars($item->certificate_no) : 'GW-DEFAULT-000';
            $organizacao = isset($item->organization) ? htmlspecialchars($item->organization) : 'Guideway LMS';
            $instrutor   = isset($item->instructor) && !empty($item->instructor) ? htmlspecialchars($item->instructor) : 'Instrutor Guideway';

            // GUIDEWAY CUSTOM - Busca carga horaria e data de conclusao reais do banco
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
                ->where($db->quoteName('item_type') . ' = ' . $db->quote('course'))
                ->where($db->quoteName('published') . ' = 1')
                ->order($db->quoteName('created') . ' DESC');
            $db->setQuery($queryConc);
            $dataConclusao = $db->loadResult();
            $data = $dataConclusao ? date('d/m/Y', strtotime($dataConclusao)) : $data;

            //-------------------------------------------------------------//

            // $queryImagens = $db->getQuery(true)
            //     ->select($db->quoteName(array('assinatura_instrutor', 'logo_parceiro_1', 'logo_parceiro_2')))

            //     // Mudamos aqui para a tabela correta onde você criou as colunas
            //     ->from($db->quoteName('#__splms_certificates'))

            //     // Relacionamos o certificado ao curso atual
            //     ->where($db->quoteName('course_id') . ' = ' . (int) $item->course_id);

            // $db->setQuery($queryImagens);
            // $dadosImagens = $db->loadObject();

            // // Define os caminhos das imagens vindos do banco
            // $caminhoAssinatura = isset($dadosImagens->assinatura_instrutor) ? $dadosImagens->assinatura_instrutor : '';
            // $caminhoLogo1      = isset($dadosImagens->logo_parceiro_1) ? $dadosImagens->logo_parceiro_1 : '';
            // $caminhoLogo2      = isset($dadosImagens->logo_parceiro_2) ? $dadosImagens->logo_parceiro_2 : '';

            // // Função auxiliar (Closure) para montar a tag <img src="..."> física do servidor
            // $gerarTagImagem = function ($caminhoRelativo, $altura = 40) {
            //     if (!empty($caminhoRelativo) && file_exists(JPATH_ROOT . '/' . ltrim($caminhoRelativo, '/'))) {
            //         $urlImagem = Joomla\CMS\Uri\Uri::root() . ltrim($caminhoRelativo, '/');
            //         return '<img src="' . $urlImagem . '" height="' . $altura . '" />';
            //     }
            //     return '';
            // };

            // // Gera as tags prontas para serem injetadas na variável $htmlFrente
            // $tagAssinatura = $gerarTagImagem($caminhoAssinatura, 50);
            // $tagLogo1      = $gerarTagImagem($caminhoLogo1, 40);
            // $tagLogo2      = $gerarTagImagem($caminhoLogo2, 40);
// ─────────────────────────────────────────────
// BUSCA DAS IMAGENS DO TEMPLATE (CORRETO)
// ─────────────────────────────────────────────

$queryImagens = $db->getQuery(true)
    ->select([
        $db->quoteName('assinatura_instrutor'),
        $db->quoteName('logo_parceiro_1'),
        $db->quoteName('logo_parceiro_2')
    ])
    ->from($db->quoteName('#__splms_certificate_templates'))
    ->where($db->quoteName('published') . ' = 1')
    ->order('id DESC');

$db->setQuery($queryImagens);
$dadosImagens = $db->loadObject();

// Evita erro se não houver template
if (!$dadosImagens) {
    $dadosImagens = new stdClass();
}

// Caminhos vindos do banco
$caminhoAssinatura = $dadosImagens->assinatura_instrutor ?? '';
$caminhoLogo1      = $dadosImagens->logo_parceiro_1 ?? '';
$caminhoLogo2      = $dadosImagens->logo_parceiro_2 ?? '';

// Função para gerar imagem corretamente (TCPDF-safe)
$gerarTagImagem = function ($caminhoRelativo, $altura = 40) {

    if (!empty($caminhoRelativo)) {

        // Remove o #joomlaImage
        $caminhoLimpo = explode('#', $caminhoRelativo)[0];

        // Decodifica URL
        $caminhoLimpo = urldecode($caminhoLimpo);

        // Caminho físico no servidor
        $fullPath = JPATH_ROOT . '/' . ltrim($caminhoLimpo, '/');

        // 🔥 TCPDF funciona MELHOR com caminho físico
        if (file_exists($fullPath)) {
            return '<img src="' . $fullPath . '" height="' . $altura . '" />';
        }
    }

    return '';
};

// Gera as tags
$tagAssinatura = $gerarTagImagem($caminhoAssinatura, 50);
$tagLogo1      = $gerarTagImagem($caminhoLogo1, 40);
$tagLogo2      = $gerarTagImagem($caminhoLogo2, 40);

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

            // ─────────────────────────────────────────────
            // BUSCA DAS IMAGENS DO CERTIFICADO (CORRIGIDO)
            // ─────────────────────────────────────────────

            // Busca direto do certificado atual
        //     $queryImagens = $db->getQuery(true)
        //         ->select([
        //             $db->quoteName('assinatura_instrutor'),
        //             $db->quoteName('logo_parceiro_1'),
        //             $db->quoteName('logo_parceiro_2')
        //         ])
        //         ->from($db->quoteName('#__splms_certificates'))
        //         ->where($db->quoteName('id') . ' = ' . (int) $item->id);

        //     $db->setQuery($queryImagens);
        //     $dadosImagens = $db->loadObject();
            

        //     // Garante que não quebre se vier vazio
        //     $caminhoAssinatura = $dadosImagens->assinatura_instrutor ?? '';
        //     $caminhoLogo1      = $dadosImagens->logo_parceiro_1 ?? '';
        //     $caminhoLogo2      = $dadosImagens->logo_parceiro_2 ?? '';

        //     // Função para gerar imagem corretamente
        //     $gerarTagImagem = function ($caminhoRelativo, $altura = 40) {
        //         if (!empty($caminhoRelativo)) {

        //             // Remove #joomlaImage
        //             $caminhoLimpo = explode('#', $caminhoRelativo)[0];

        //             // 🔥 CORREÇÃO AQUI
        //             $caminhoLimpo = urldecode($caminhoLimpo);

        //             $fullPath = JPATH_ROOT . '/' . ltrim($caminhoLimpo, '/');

        //             if (file_exists($fullPath)) {
        //                 $urlImagem = Uri::root() . ltrim($caminhoLimpo, '/');
        //                 return '<img src="' . $urlImagem . '" height="' . $altura . '" />';
        //             }
        //         }
        //         return '';
        //     };

        //     // Gera as imagens
        //     $tagAssinatura = $gerarTagImagem($caminhoAssinatura, 50);
        //     $tagLogo1      = $gerarTagImagem($caminhoLogo1, 40);
        //     $tagLogo2      = $gerarTagImagem($caminhoLogo2, 40);           


        //     $pdf->AddPage();

        //    file_put_contents(JPATH_ROOT . '/images/debug2.txt', 'PASSOU AQUI');
        
            // Tabela com a barra azul apenas na primeira linha (<tr>)
            $pdf->AddPage();
            
            $htmlFrente = '
<table width="100%" cellpadding="0" cellspacing="0" bgcolor="#FDFBF7" style="font-family: helvetica, arial, sans-serif;">
    <tr>
        <td height="10" bgcolor="#111827"></td>
    </tr>
    <tr>
        <td style="padding: 20px 40px;">
            
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="text-align: center;">
                        <h1 style="font-size: 24px; color: #111827; margin: 0; text-transform: uppercase; letter-spacing: 2px;">Certificado de Conclusão</h1>
                        <p style="font-size: 11px; color: #666666; margin-top: 5px;">
                            Código de Autenticidade: <strong style="color: #111827;">' . $codigo . '</strong> &nbsp;&nbsp;|&nbsp;&nbsp; 
                            Data de Conclusão: <strong style="color: #111827;">' . $data . '</strong>
                        </p>
                        <div style="margin-top: 10px;">
                            <span style="font-size: 10px; font-weight: bold; color: #111827;">GUIDEWAY <span style="font-weight: normal; color: #666666;">LMS</span></span>
                        </div>
                    </td>
                </tr>
            </table>
            <br/><br/>
            
            <table width="100%" cellpadding="0" cellspacing="0" style="border: 1px solid #D1D5DB; background-color: rgba(255, 255, 255, 0.6);">
                <tr>
                    <td style="padding: 35px; text-align: center;">
                        <p style="font-size: 12px; line-height: 1.5; color: #333333; font-style: italic; margin-top: 0; margin-bottom: 15px;">Certificamos com orgulho que</p>
                        
                        <h2 style="font-size: 28px; color: #111827; margin: 0;"><strong>' . $aluno . '</strong></h2>
                        
                        <div style="height: 1px;"></div>
                        
                        <p style="font-size: 16px; line-height: 1.5; color: #333333; font-style: italic; margin-top: 0; margin-bottom: 0;">
                            concluiu com êxito todos os requisitos acadêmicos do curso <br />
                            
                            <strong style="font-size: 16px; color: #111827; font-style: normal;">' . $curso . '</strong><br />
                            
                            cumprindo a carga horária de <strong style="font-style: normal; color: #111827;">' . $cargaHoraria . ' horas</strong>, na modalidade online.
                        </p>
                        
                        <div style="height: 5px;"></div>
                    </td>
                </tr>
            </table>
            
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td height="5"></td> 
                </tr>
                <tr>
                    <td width="33%"></td>
                    <td width="34%" style="vertical-align: bottom; text-align: center;">
                        <div style="width: 250px; margin: 0 auto; border-top: 1px solid #1A1A1A; padding-top: 8px;">
                            
                            <div style="margin-bottom: 5px;">
                                ' . $tagAssinatura . '
                            </div>
                            
                            <strong style="font-size: 16px; color: #111827;">' . $instrutor . '</strong><br/>
                            <span style="font-size: 13px; color: #666666;">Instrutor do curso</span>
                        </div>
                    </td>
                    <td width="33%"></td>
                </tr>
            </table>

            <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: 10px;">
                <tr>
                    <td width="100%" style="text-align: center;">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td width="35%"></td>
                                
                                <td width="12%" style="height: 5px; text-align: center; vertical-align: middle;">
                                    ' . $tagLogo1 . '
                                </td>
                                
                                <td width="6%"></td>
                                
                                <td width="12%" style="height: 5px; text-align: center; vertical-align: middle;">
                                    ' . $tagLogo2 . '
                                </td>
                                
                                <td width="35%"></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            
            <div style="height: 15px;"></div>

        </td>
    </tr>
</table>';

            $pdf->writeHTML($htmlFrente, true, false, true, false, '');


            // ==========================================
            // PÁGINA 2: VERSO (CONTEÚDO PROGRAMÁTICO E QR CODE CENTRALIZADO)
            // ==========================================

            $pdf->AddPage();

            // GUIDEWAY CUSTOM - 17/03/2026 - Joshua - Busca topicos reais do curso para o verso
            $queryTopicos = $db->getQuery(true)
                ->select('t.id, t.title, t.ordering')
                ->from($db->quoteName('#__splms_lessiontopics', 't'))
                ->where($db->quoteName('t.course_id') . ' = ' . (int) $item->course_id)
                ->where($db->quoteName('t.published') . ' = 1')
                ->order($db->quoteName('t.ordering') . ' ASC');
            $db->setQuery($queryTopicos);
            $topicos = $db->loadObjectList();

            $linhasTopicos = '';
            $contador = 1;
            foreach ($topicos as $topico) {
                $numero = str_pad($contador, 2, '0', STR_PAD_LEFT);
                $linhasTopicos .= '
                <tr>
                    <td width="20%" style="border-bottom: 1px solid #D1D5DB; text-align: center; padding: 8px;">' . $numero . '</td>
                    <td width="80%" style="border-bottom: 1px solid #D1D5DB; text-align: center; font-style: italic; padding: 8px;">' . htmlspecialchars($topico->title) . '</td>
                </tr>';
                $contador++;
            }

            $htmlVerso = '
            <table width="100%" cellpadding="0" cellspacing="0" bgcolor="#FDFBF7" style="font-family: helvetica, arial, sans-serif;">
                <tr>
                    <td height="20" bgcolor="#111827"></td>
                </tr>
                <tr>
                    <td style="padding: 30px 40px; height: 185mm;">
                        
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="text-align: center;">
                                    <h1 style="font-size: 24px; color: #111827; margin: 0; text-transform: uppercase; letter-spacing: 2px;">Conteúdo Programático</h1>
                                    <p style="font-size: 11px; color: #666666; margin-top: 5px;">
                                        ' . $curso . ': <strong style="color: #111827;">' . $cargaHoraria . ' horas</strong>
                                    </p>
                                    <div style="margin-top: 10px;">
                                        <span style="font-size: 14px; font-weight: bold; color: #111827;">GUIDEWAY <span style="font-weight: normal; color: #666666;">LMS</span></span>
                                    </div>
                                </td>
                            </tr>
                        </table>
                        <br/><br/>
                        
                        <table width="100%" cellpadding="8" cellspacing="0" style="border: 1px solid #D1D5DB; font-size: 16px; color: #333333; background-color: rgba(255, 255, 255, 0.6);">
                            <tr>
                                <td width="20%" style="border-bottom: 1px solid #D1D5DB; text-align: center;"><strong>Módulo</strong></td>
                                <td width="80%" style="border-bottom: 1px solid #D1D5DB; text-align: center;"><strong>Descrição do Conteúdo Acadêmico</strong></td>
                            </tr>
                            ' . $linhasTopicos . '
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
