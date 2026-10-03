<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser as PdfParser;
use ZipArchive;

class DocumentExtractionService
{
    /**
     * Extract text per page from a PDF or DOCX file.
     * Returns an array of: ['page_number' => int, 'text' => string]
     *
     * @param string $filePath Absolute path to the file
     * @param string $fileType 'pdf' or 'docx'
     * @return array<int, array{page_number: int, text: string}>
     * @throws Exception
     */
    public function extractPages(string $filePath, string $fileType): array
    {
        if (!file_exists($filePath)) {
            throw new Exception("File tidak ditemukan: {$filePath}");
        }

        $fileType = strtolower($fileType);

        if ($fileType === 'pdf') {
            return $this->extractPdfPages($filePath);
        } elseif (in_array($fileType, ['docx', 'doc'])) {
            return $this->extractDocxPages($filePath);
        } else {
            throw new Exception("Tipe file {$fileType} belum didukung.");
        }
    }

    /**
     * Extract text page by page from PDF using smalot/pdfparser.
     */
    protected function extractPdfPages(string $filePath): array
    {
        $parser = new PdfParser();
        $pdf = $parser->parseFile($filePath);
        $pages = $pdf->getPages();
        $results = [];

        $pageNumber = 1;
        foreach ($pages as $page) {
            $text = trim($page->getText());
            // Filter out empty lines or excessive whitespace
            $text = preg_replace('/\s+/', ' ', $text);
            
            if (!empty($text)) {
                $results[] = [
                    'page_number' => $pageNumber,
                    'text' => $text,
                ];
            }
            $pageNumber++;
        }

        if (empty($results)) {
            // Fallback: entire text if pages object parsing was flat
            $fullText = trim($pdf->getText());
            $fullText = preg_replace('/\s+/', ' ', $fullText);
            if (!empty($fullText)) {
                $results[] = [
                    'page_number' => 1,
                    'text' => $fullText,
                ];
            }
        }

        return $results;
    }

    /**
     * Extract text from DOCX file.
     * Since DOCX does not have strict physical pages before rendering,
     * we extract sections / paragraphs and divide into virtual pages (~500 words per page).
     */
    protected function extractDocxPages(string $filePath): array
    {
        $text = '';
        $zip = new ZipArchive();
        if ($zip->open($filePath) === true) {
            $xmlIndex = $zip->locateName('word/document.xml');
            if ($xmlIndex !== false) {
                $xmlData = $zip->getFromIndex($xmlIndex);
                $zip->close();

                $dom = new \DOMDocument();
                // Suppress XML warnings for non-standard MS Word tags
                libxml_use_internal_errors(true);
                $dom->loadXML($xmlData, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
                libxml_clear_errors();

                $text = strip_tags($dom->saveXML());
                $text = html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
            } else {
                $zip->close();
            }
        }

        $text = trim(preg_replace('/\s+/', ' ', $text));

        if (empty($text)) {
            return [];
        }

        // Split into virtual pages of ~400 words
        $words = preg_split('/\s+/', $text);
        $wordsPerPage = 400;
        $totalWords = count($words);
        $results = [];

        $pageNumber = 1;
        for ($i = 0; $i < $totalWords; $i += $wordsPerPage) {
            $pageSlice = array_slice($words, $i, $wordsPerPage);
            $pageText = implode(' ', $pageSlice);
            if (!empty($pageText)) {
                $results[] = [
                    'page_number' => $pageNumber,
                    'text' => $pageText,
                ];
                $pageNumber++;
            }
        }

        return $results;
    }
}
