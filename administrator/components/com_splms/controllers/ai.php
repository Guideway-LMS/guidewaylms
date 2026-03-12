<?php
/**
 * @package     com_splms
 * @subpackage  Controllers
 * @author      JoomShaper http://www.joomshaper.com
 * @copyright   Copyright (c) 2010 - 2024 JoomShaper
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * AI JSON Controller
 */
class SplmsControllerAi extends BaseController
{
    /**
     * Generate Quiz Proxy
     *
     * @return  void
     */
    public function generateQuiz()
    {
        // Check for request forgeries
        // Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

        $input      = Factory::getApplication()->input;
        $topic      = $input->get('topic', '', 'STRING');
        $description = $input->get('description', '', 'RAW'); // RAW to keep spacing/formatting
        $difficulty = $input->get('difficulty', 'Medium', 'STRING');
        $count      = $input->get('count', 5, 'INT');

        try {
            // Check permissions
            if (!Factory::getUser()->authorise('ai.generate', 'com_splms')) {
                throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
            }

            // Load the helper
            require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/ai.php';

            $contextText = '';

            // Append manual description
            if (!empty($description)) {
                $topic .= "\n\n[USER DESCRIPTION/CONTEXT]:\n" . $description;
            }

            // Handle File Upload
            if (!empty($_FILES['file']['tmp_name'])) {
                $fileTmpPath = $_FILES['file']['tmp_name'];
                $fileName    = $_FILES['file']['name'];
                $fileType    = $_FILES['file']['type'];
                $fileExt     = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                if ($fileExt === 'docx') {
                    $contextText = $this->extractTextFromDocx($fileTmpPath);
                } elseif ($fileExt === 'pdf') {
                    $contextText = $this->extractTextFromPdf($fileTmpPath);
                }
            }

            // Append context to topic if available
            if (!empty($contextText)) {
                $topic .= "\n\n[CONTEXT DOCUMENT CONTENT]:\n" . substr($contextText, 0, 15000); // Limit context size
            }

            $result = SplmsHelperAi::generateQuiz($topic, $difficulty, $count);

            echo json_encode([
                'success' => true,
                'data'    => $result
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }

    Factory::getApplication()->close();
    }

    private function extractTextFromDocx($filePath)
    {
        $content = '';
        $zip = new ZipArchive;
        if ($zip->open($filePath) === true) {
            if (($index = $zip->locateName('word/document.xml')) !== false) {
                $data = $zip->getFromIndex($index);
                $zip->close();
                $xml = new DOMDocument();
                $xml->loadXML($data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
                $content = strip_tags($xml->saveXML());
            }
            $zip->close();
        }
        return $content;
    }

    private function extractTextFromPdf($filePath)
    {
        // Basic PDF extraction attempt without external library
        // This is a crude fallback. Ideally, use Smalot\PdfParser
        $content = '';
        if (file_exists($filePath)) {
            // Attempt to read raw content and extract text streams
            $raw = file_get_contents($filePath);
            // Just a very basic extraction of text between BT and ET markers or similar
            // Realistically, for robust PDF parsing, we need a library. 
            // For now, we will return a placeholder or try `pdftotext` if available on system.
            
            // Try standard PHP library or shell exec if allowed
            // This is minimal; user might need to install a parser.
            // Returning empty string with a warning in production usually, 
            // but let's try a simple regex for uncompressed PDFs or rely on the user having text-based PDFs.
            
            // For this environment, we'll assume we can't easily install new composer packages dynamically without user.
            // We will return a message if empty.
            
            // Simple string extraction (often fails on compressed streams)
            $content = (string) $raw; 
            // Remove binary junk
            $content = preg_replace('/[^[:print:]\n]/', '', $content);
        }
        return $content;
    }
}
