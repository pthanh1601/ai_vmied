<?php
namespace App\Controllers;

use Dompdf\Dompdf;
use Dompdf\Options;

class ReportController
{
    // ============================================================
    // HÀM HELPER ĐA NGÔN NGỮ (Tự động dịch theo $lang)
    // ============================================================
    private function getTranslator($lang)
    {
        $translations = [
            'vi' => [
                'original_highlight' => 'Bản gốc Highlight',
                'export_report'      => 'Xuất Báo Cáo',
                'ai_detection'       => 'Phát hiện AI',
                'plagiarism'         => 'Đạo văn',
                'grammar_errors'     => 'Lỗi ngữ pháp',
                'readability'        => 'Khả năng đọc',
                'doc_info'           => 'THÔNG TIN TÀI LIỆU',
                'doc_name'           => 'Tên tài liệu:',
                'word_count'         => 'Số từ:',
                'words'              => 'từ',
                'email'              => 'Email:',
                'scan_time'          => 'Thời gian quét:',
                'source'             => 'Nguồn:',
                'status'             => 'Trạng thái:',
                'completed'          => 'Hoàn thành',
                'original_content'   => 'NỘI DUNG GỐC',
                'ai_analysis_sentence'=> 'PHÂN TÍCH AI TỪNG CÂU',
                'sentence_content'   => 'Nội dung câu',
                'ai_level'           => 'Mức độ AI',
                'grammar_and_spell'  => 'NGỮ PHÁP & CHÍNH TẢ',
                'grading'            => 'Xếp loại:',
                'errors'             => 'lỗi',
                'suggestions'        => 'Gợi ý:',
                'ai_score'           => 'ĐIỂM AI',
                'ai_written'         => 'AI viết',
                'human_written'      => 'Người viết',
                'human'              => 'NGƯỜI',
                'likely'             => 'Có khả năng là',
                'confidence'         => 'Độ tin cậy',
                'confident_text_1'   => 'Chúng tôi tự tin rằng văn bản được quét là',
                'confident_text_2'   => ', nhưng điều đó KHÔNG có nghĩa là',
                'confident_text_3'   => 'văn bản được tạo ra đều là',
                'created_by_ai'      => 'do AI tạo ra',
                'created_by_human'   => 'do con người viết',
                'model_used'         => 'Mô hình được sử dụng:',
                'date'               => 'Ngày:',
                'how_to_read'        => 'Làm thế nào để đọc điểm số?',
                'how_to_read_desc_1' => 'Điểm số này phản ánh mức độ tin cậy của chúng tôi rằng văn bản được quét đã được tạo ra (hoặc viết lại) bởi công cụ AI. Văn bản có điểm số \'Có khả năng là AI/Nguyên bản - Độ tin cậy 90%\' nên được hiểu là:',
                'how_to_read_desc_2' => 'Chúng tôi tin tưởng 90% rằng văn bản này do',
                'how_to_read_desc_3' => 'KHÔNG có nghĩa là 90% văn bản là do',
                'understand_highlight'=> 'Hiểu ý nghĩa phần tô sáng',
                'highlight_desc'     => 'Mức độ tin cậy của AI được thể hiện trong đoạn văn bản được đánh dấu bằng các màu sắc sau:',
                'very_likely_ai'     => 'Rất có thể là AI (≥ 70%)',
                'likely_ai'          => 'Có thể là AI (40% - 69%)',
                'human_written_hl'   => 'Người viết (< 40%)',
                'seo_optimization'   => 'TỐI ƯU SEO',
                'score'              => 'Điểm:',
                'content_structure'  => 'Cấu trúc nội dung',
                'word'               => 'Từ',
                'heading'            => 'Tiêu đề',
                'paragraph'          => 'Đoạn văn',
                'optimization_suggestions' => 'Gợi ý Tối ưu',
                'geo_suggestions'    => 'Đề xuất GEO',
                'seo_suggestions'    => 'Gợi ý SEO',
                'potential_keywords' => 'Từ khóa tiềm năng',
                'competitors'        => 'Đối thủ cạnh tranh',
                'rank'               => 'Hạng',
                'seo_score'          => 'Điểm SEO:',
                'readability_caps'   => 'ĐỌC HIỂU (READABILITY)',
                'text_stats'         => 'Thống kê văn bản',
                'unique_words'       => 'Từ độc nhất',
                'sentences'          => 'Số câu',
                'paragraphs'         => 'Số đoạn',
                'reading_time'       => 'Đọc (phút)',
                'speaking_time'      => 'Nói (phút)',
                'writing_time'       => 'Viết (phút)',
                'eval_metrics'       => 'Chỉ số đánh giá',
                'plag_sources'       => 'NGUỒN ĐẠO VĂN',
                'matching_websites'  => 'Các trang web phù hợp',
                'duplicate_phrases'  => 'Cụm từ trùng lặp',
                'page_details'       => 'Chi tiết trang (Page Details)',
                'page_modified'      => 'Trang cập nhật:',
                'page_published'     => 'Trang xuất bản:',
                'matching_phrases'   => 'Cụm từ trùng khớp',
                'duplicate_sources'  => 'nguồn trùng lặp',
                'no_plagiarism'      => 'Không phát hiện đạo văn',
                'fact_check'         => 'KIỂM TRA SỰ THẬT',
                'total'              => 'Tổng:',
                'errors_suspects'    => 'sai/nghi ngờ',
                'all_correct'        => 'Tất cả đúng',
                'rewrite_suggestion' => 'Gợi ý viết lại:',
                'references'         => 'Nguồn tham khảo',
                'original'           => 'Nguyên bản',
                'author_ai'          => 'AI',
                'author_human'       => 'con người',
                'suspected_plagiarism'=> 'Nghi ngờ đạo văn',
                'original_content_tooltip' => 'Nội dung nguyên bản',
                'grammar_spell_error' => 'lỗi chính tả/ngữ pháp',
            ],
            'en' => [
                'original_highlight' => 'Original Highlight',
                'export_report'      => 'Export Report',
                'ai_detection'       => 'AI Detection',
                'plagiarism'         => 'Plagiarism',
                'grammar_errors'     => 'Grammar Errors',
                'readability'        => 'Readability',
                'doc_info'           => 'DOCUMENT INFO',
                'doc_name'           => 'Document Name:',
                'word_count'         => 'Word Count:',
                'words'              => 'words',
                'email'              => 'Email:',
                'scan_time'          => 'Scan Time:',
                'source'             => 'Source:',
                'status'             => 'Status:',
                'completed'          => 'Completed',
                'original_content'   => 'ORIGINAL CONTENT',
                'ai_analysis_sentence'=> 'AI SENTENCE ANALYSIS',
                'sentence_content'   => 'Sentence Content',
                'ai_level'           => 'AI Level',
                'grammar_and_spell'  => 'GRAMMAR & SPELLING',
                'grading'            => 'Grade:',
                'errors'             => 'errors',
                'suggestions'        => 'Suggestions:',
                'ai_score'           => 'AI SCORE',
                'ai_written'         => 'AI written',
                'human_written'      => 'Human written',
                'human'              => 'HUMAN',
                'likely'             => 'Likely',
                'confidence'         => 'Confidence',
                'confident_text_1'   => 'We are confident that the scanned text is',
                'confident_text_2'   => ', but that DOES NOT mean',
                'confident_text_3'   => 'of the text was generated by',
                'created_by_ai'      => 'AI-generated',
                'created_by_human'   => 'human-written',
                'model_used'         => 'Model Used:',
                'date'               => 'Date:',
                'how_to_read'        => 'How to read the score?',
                'how_to_read_desc_1' => 'This score reflects our confidence level that the scanned text was generated (or rewritten) by an AI tool. A text with a score of \'Likely AI/Original - 90% Confidence\' should be interpreted as:',
                'how_to_read_desc_2' => 'We are 90% confident that this text is written by',
                'how_to_read_desc_3' => 'It DOES NOT mean that 90% of the text is written by',
                'understand_highlight'=> 'Understanding the highlights',
                'highlight_desc'     => 'The AI confidence level is represented in the highlighted text by the following colors:',
                'very_likely_ai'     => 'Very likely AI (≥ 70%)',
                'likely_ai'          => 'Likely AI (40% - 69%)',
                'human_written_hl'   => 'Human written (< 40%)',
                'seo_optimization'   => 'SEO OPTIMIZATION',
                'score'              => 'Score:',
                'content_structure'  => 'Content Structure',
                'word'               => 'Word',
                'heading'            => 'Heading',
                'paragraph'          => 'Paragraph',
                'optimization_suggestions' => 'Optimization Suggestions',
                'geo_suggestions'    => 'GEO Suggestions',
                'seo_suggestions'    => 'SEO Suggestions',
                'potential_keywords' => 'Potential Keywords',
                'competitors'        => 'Competitors',
                'rank'               => 'Rank',
                'seo_score'          => 'SEO Score:',
                'readability_caps'   => 'READABILITY',
                'text_stats'         => 'Text Statistics',
                'unique_words'       => 'Unique Words',
                'sentences'          => 'Sentences',
                'paragraphs'         => 'Paragraphs',
                'reading_time'       => 'Reading (min)',
                'speaking_time'      => 'Speaking (min)',
                'writing_time'       => 'Writing (min)',
                'eval_metrics'       => 'Evaluation Metrics',
                'plag_sources'       => 'PLAGIARISM SOURCES',
                'matching_websites'  => 'Matching Websites',
                'duplicate_phrases'  => 'Duplicate Phrases',
                'page_details'       => 'Page Details',
                'page_modified'      => 'Page modified:',
                'page_published'     => 'Page published:',
                'matching_phrases'   => 'Matching Phrases',
                'duplicate_sources'  => 'duplicate sources',
                'no_plagiarism'      => 'No plagiarism detected',
                'fact_check'         => 'FACT CHECK',
                'total'              => 'Total:',
                'errors_suspects'    => 'errors/suspects',
                'all_correct'        => 'All correct',
                'rewrite_suggestion' => 'Rewrite suggestion:',
                'references'         => 'References',
                'original'           => 'Original',
                'author_ai'          => 'AI',
                'author_human'       => 'human',
                'suspected_plagiarism'=> 'Suspected plagiarism',
                'original_content_tooltip' => 'Original content',
                'grammar_spell_error' => 'grammar/spelling errors',
            ]
        ];

        return function($key) use ($translations, $lang) {
            return $translations[$lang][$key] ?? $translations['vi'][$key] ?? $key;
        };
    }

    // ============================================================
    // ENTRY POINT
    // ============================================================

    public function ai($id = null)
    {
        $paramId = is_array($id) ? end($id) : $id;
        $reportId = !empty($paramId) ? (int) $paramId : (int) ($_GET['id'] ?? request('id') ?? 0);
        $type = $_GET['type'] ?? request('type') ?? 'report';

        if (empty($reportId)) {
            return '<div style="padding:50px;text-align:center;">
                        <h3 style="color:red;">Lỗi: Không tìm thấy mã báo cáo (ID).</h3>
                        <p>Vui lòng quay lại trang danh sách và thử lại.</p>
                    </div>';
        }

        $user = app()->request->user;

        $history = app()->db->get('originality_history', '*', [
            'id'           => $reportId,
            'account_uuid' => $user->uuid,
        ]);

        if (!$history) {
            return "Báo cáo #{$reportId} không tồn tại hoặc không thuộc quyền sở hữu của bạn.";
        }

        // Khởi tạo bộ dịch theo ngôn ngữ của tài liệu
        $lang = $history['lang'] ?? 'vi';
        $__ = $this->getTranslator($lang);

        // ── Decode tất cả cột JSON (giống HistoryDetail) ──────────────
        $jsonCols = ['ai', 'plagiarism', 'grammar', 'readability', 'facts', 'content_optimizer', 'metadata'];
        foreach ($jsonCols as $col) {
            $history[$col] = isset($history[$col]) && is_string($history[$col])
                ? (json_decode($history[$col], true) ?? [])
                : ($history[$col] ?? []);
        }

        // facts luôn là indexed array
        $history['facts'] = array_values($history['facts'] ?? []);

        // ── Build $result chuẩn (key giống API response) ──────────────
        $result = [
            'ai'               => $history['ai']               ?? [],
            'plagiarism'       => $history['plagiarism']       ?? [],
            'grammarSpelling'  => $history['grammar']          ?? [],   // DB=grammar → API=grammarSpelling
            'readability'      => $history['readability']      ?? [],
            'properties'       => $history['metadata']['properties'] ?? [],
            'credits'          => $history['metadata']['credits']    ?? [],
        ];

        // ── $summary: số liệu tổng hợp ───────────────────────────────
        $summary = $this->buildSummary($history, $result, $user);

        // Tạo cục Data chung truyền vào views
        $dataExport = [
            'history' => $history,
            'result'  => $result,
            'summary' => $summary,
            'data'    => $result,   // alias cho pdf_export.php dùng $data
            'user'    => $user,
            '__'      => $__        // Truyền hàm dịch vào view
        ];

        if ($type === 'highlight') {
            $this->exportOriginalFileHighlight($history, $result, $summary, $user, $__);
            exit;
        }

        // Luôn luôn xuất PDF, không render giao diện Web
        $this->generatePdf($dataExport, $type);
        exit;
    }
    

    public function original($id = null)
    {
        $paramId  = is_array($id) ? end($id) : $id;
        $reportId = !empty($paramId) ? (int)$paramId : (int)($_GET['id'] ?? request('id') ?? 0);
    
        if (empty($reportId)) {
            return '<div style="padding:50px;text-align:center;">
                        <h3 style="color:red;">Lỗi: Không tìm thấy mã báo cáo (ID).</h3>
                        <p>Vui lòng quay lại trang danh sách và thử lại.</p>
                    </div>';
        }
    
        $user    = app()->request->user;
        $history = app()->db->get('originality_history', '*', [
            'id'           => $reportId,
            'account_uuid' => $user->uuid,
        ]);
    
        if (!$history) {
            return "Báo cáo #{$reportId} không tồn tại hoặc không thuộc quyền sở hữu của bạn.";
        }

        // Khởi tạo bộ dịch theo ngôn ngữ của tài liệu
        $lang = $history['lang'] ?? 'vi';
        $__ = $this->getTranslator($lang);
    
        // Decode JSON columns
        $jsonCols = ['ai', 'plagiarism', 'grammar', 'readability', 'facts', 'content_optimizer', 'metadata'];
        foreach ($jsonCols as $col) {
            $history[$col] = isset($history[$col]) && is_string($history[$col])
                ? (json_decode($history[$col], true) ?? [])
                : ($history[$col] ?? []);
        }
        $history['facts'] = array_values($history['facts'] ?? []);
    
        $result = [
            'ai'               => $history['ai']                ?? [],
            'plagiarism'       => $history['plagiarism']        ?? [],
            'grammarSpelling'  => $history['grammar']           ?? [],
            'readability'      => $history['readability']       ?? [],
            'facts'            => $history['facts']             ?? [],
            'contentOptimizer' => $history['content_optimizer'] ?? [],
            'properties'       => $history['metadata']['properties'] ?? [],
            'credits'          => $history['metadata']['credits']    ?? [],
        ];
    
        $summary = $this->buildSummary($history, $result, $user);
    
        // Xuất PDF gốc có highlight
        $this->exportOriginalFileHighlight($history, $result, $summary, $user, $__);
        exit;
    }
    
    private function exportOriginalFileHighlight_2(array $history, array $result, array $summary): void
    {
        $fileUrl = $history['file_url'] ?? null;
    
        if (empty($fileUrl)) {
            http_response_code(404);
            echo 'Không tìm thấy file PDF gốc.';
            return;
        }
    
        // 1. Tải PDF về thư mục tạm
        $tmpInput = tempnam(sys_get_temp_dir(), 'pdf_orig_') . '.pdf';
        $pdfBytes = @file_get_contents($fileUrl);
    
        if ($pdfBytes === false) {
            http_response_code(500);
            echo 'Không thể tải file PDF từ: ' . htmlspecialchars($fileUrl);
            return;
        }
        file_put_contents($tmpInput, $pdfBytes);
    
        // 2. Lấy danh sách đoạn text cần highlight từ cột `ai`
        //    Cấu trúc mẫu: $result['ai']['blocks'] = [['text'=>'...','result'=>['fake'=>0.9,...]], ...]
        $highlightTexts = $this->extractHighlightTexts($result['ai']);
    
        // 3. Highlight và stream PDF
        try {
            $outputPath = tempnam(sys_get_temp_dir(), 'pdf_out_') . '.pdf';
            $this->highlightPdf($tmpInput, $highlightTexts, $outputPath);
    
            $filename = 'bao-cao-goc-' . ($history['id'] ?? 'report') . '.pdf';
    
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($outputPath));
            header('Cache-Control: no-cache');
    
            readfile($outputPath);
        } finally {
            @unlink($tmpInput);
            @unlink($outputPath ?? '');
        }
    }
    
    private function extractHighlightTexts(array $aiData): array
    {
        $texts = [];
    
        // Cấu trúc: { blocks: [{text: "...", result: {fake: 0.9}}] }
        $blocks = $aiData['blocks'] ?? [];
    
        foreach ($blocks as $block) {
            $text     = trim($block['text'] ?? '');
            $fakeProb = (float)($block['result']['fake'] ?? 0);
    
            if (!empty($text) && $fakeProb > 0.5) {
                $texts[] = [
                    'text'  => $text,
                    'score' => $fakeProb,
                    // Màu highlight theo mức độ AI
                    'color' => $fakeProb > 0.8
                        ? [255, 80,  80]   // đỏ  — rất có khả năng AI
                        : [255, 200, 50],  // vàng — nghi ngờ AI
                ];
            }
        }
    
        return $texts;
    }
    

    private function highlightPdf(string $inputPath, array $highlightTexts, string $outputPath): void
    {
        // composer require setasign/fpdi tecnickcom/tcpdf smalot/pdfparser
        $pdf       = new \setasign\Fpdi\Tcpdf\Fpdi();
        $parser    = new \Smalot\PdfParser\Parser();
        $document  = $parser->parseFile($inputPath);
        $pages     = $document->getPages();
        $pageCount = $pdf->setSourceFile($inputPath);
    
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $tplId = $pdf->importPage($pageNo);
            $size  = $pdf->getTemplateSize($tplId);
    
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tplId);
    
            if (!isset($pages[$pageNo - 1])) continue;
    
            // Lấy text items kèm tọa độ từ pdfparser
            try {
                $dataTm = $pages[$pageNo - 1]->getDataTm();
            } catch (\Exception $e) {
                continue;
            }
    
            foreach ($dataTm as $item) {
                [$matrix, $rawText] = $item;
                $rawText = trim($rawText);
                if (empty($rawText)) continue;
    
                foreach ($highlightTexts as $hl) {
                    // So khớp một phần (vì pdfparser trả từng dòng nhỏ)
                    if (mb_stripos($hl['text'], $rawText) !== false
                        || mb_stripos($rawText, mb_substr($hl['text'], 0, 20)) !== false
                    ) {
                        // Tọa độ PDF (pt) → mm, đổi gốc trục Y
                        $xMm    = $matrix[4] / 2.83465;
                        $yPt    = $matrix[5];
                        $fontSizePt = abs($matrix[3]);                 // scale Y ≈ font size
                        $fontSizeMm = $fontSizePt / 2.83465;
                        $yMm    = ($size['height']) - ($yPt / 2.83465) - $fontSizeMm;
                        $wMm    = mb_strlen($rawText) * ($fontSizeMm * 0.55); // ước tính chiều rộng
    
                        [$r, $g, $b] = $hl['color'];
    
                        $pdf->SetFillColor($r, $g, $b);
                        $pdf->setAlpha(0.35);
                        $pdf->Rect($xMm, $yMm, $wMm, $fontSizeMm + 1, 'F');
                        $pdf->setAlpha(1.0);
                        break;
                    }
                }
            }
        }
    
        $pdf->Output($outputPath, 'F');
    }
    
    private function exportOriginalFileHighlight(array $history, array $result, array $summary, $user, $__): void
        {
            // Tìm tên file gốc từ url hoặc title
            $fileUrl = $summary['file_url'] ?? $summary['scan_url'] ?? '';
            $fileName = '';
            
            if (!empty($fileUrl) && str_contains($fileUrl, '/')) {
                $fileName = basename(parse_url($fileUrl, PHP_URL_PATH));
            } elseif (!empty($summary['title'])) {
                $fileName = $summary['title'];
            }
    
            if (empty($fileName)) {
                $this->exportHighlightDoc($history, $result, $summary, $user, $__);
                return;
            }
    
            $basePath = rtrim(app()->basePath(), '/');
            $possiblePaths = [
                $basePath . '/public/uploads/scans/' . $fileName,
                $basePath . '/public/uploads/' . $fileName,
                $basePath . '/public/uploads/scans/' . $fileName . '.pdf',
                $basePath . '/public/uploads/' . $fileName . '.pdf'
            ];
    
            $originalFilePath = '';
            foreach ($possiblePaths as $path) {
                if (is_file($path)) {
                    $originalFilePath = $path;
                    break;
                }
            }
    
            if (empty($originalFilePath)) {
                $this->exportHighlightDoc($history, $result, $summary, $user, $__);
                return;
            }
    
            $ext = strtolower(pathinfo($originalFilePath, PATHINFO_EXTENSION));
            if ($ext !== 'pdf') {
                // Nếu không phải PDF (ví dụ .docx), tiếp tục dùng định dạng Word .doc đã tạo
                $this->exportHighlightDoc($history, $result, $summary, $user, $__);
                return;
            }
    
            $ai = $result['ai'] ?? [];
            $blocks = $ai['blocks'] ?? [];
            $highlightData = [];
    
            foreach ($blocks as $block) {
                $text = trim($block['text'] ?? '');
                if (!$text) continue;
    
                $color = null;
                if (isset($block['result']['fake'])) {
                    $fake = $block['result']['fake'];
                    if ($fake > 0.8) $color = '#ff9999'; // Rất có thể AI -> Đỏ nhạt
                    elseif ($fake > 0.5) $color = '#ffcc99'; // Có thể AI -> Cam nhạt
                    elseif ($fake > 0.3) $color = '#ffff99'; // Nghi ngờ AI -> Vàng nhạt
                }
    
                if (!empty($block['plagiarism'])) {
                    $color = '#ffb3e6'; // Đạo văn -> Hồng
                }
    
                if ($color) {
                    $highlightData[] = ['text' => $text, 'color' => $color];
                }
            }
    
            $storagePath = rtrim(app()->basePath(), '/') . '/storage/tmp';
            if (!is_dir($storagePath)) mkdir($storagePath, 0777, true);
    
            $jsonFile = $storagePath . '/' . uniqid('hl_') . '.json';
            $outputPdf = $storagePath . '/' . uniqid('out_') . '.pdf';
            file_put_contents($jsonFile, json_encode($highlightData, JSON_UNESCAPED_UNICODE));
    
            $pythonScript = rtrim(app()->basePath(), '/') . '/resources/themes/vmied/report/highlight_pdf.py';
            $command = escapeshellcmd("python3") . " " . escapeshellarg($pythonScript) . " " . escapeshellarg($originalFilePath) . " " . escapeshellarg($outputPdf) . " " . escapeshellarg($jsonFile) . " 2>&1";
            $output = shell_exec($command);
    
            if (file_exists($outputPdf)) {
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="Highlight_' . $fileName . '"');
                readfile($outputPdf);
                unlink($jsonFile);
                unlink($outputPdf);
            } else {
                echo "Lỗi xử lý file gốc bằng Python: " . htmlspecialchars($output);
                unlink($jsonFile);
            }
        }
    
    private function exportHighlightDoc(array $history, array $result, array $summary, $user, $__): void
        {
            $data = [
                'history' => $history,
                'result'  => $result,
                'summary' => $summary,
                'data'    => $result,
                'user'    => $user,
                '__'      => $__,
            ];
    
            $html = view('report/word_highlight', $data);
            
            $date = date('d-m-Y_H-i-s');
            $filename = "AI-Vmied-Checker-{$date}-Highlight.doc";
    
            header("Content-Type: application/vnd.ms-word; charset=UTF-8");
            header("Content-Disposition: attachment; filename=\"$filename\"");
            echo "<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'><head><meta charset='utf-8'><title>Highlight Report</title></head><body>\n" . $html . "\n</body></html>";
        }
    
    private function buildSummary(array $history, array $result, $user): array
        {
            $ai   = $result['ai']   ?? [];
            $gram = $result['grammarSpelling'] ?? [];
            $plag = $result['plagiarism'] ?? [];
            $read = $result['readability'] ?? [];
            $facts = $result['facts'] ?? [];
    
            return [
                'title'             => $history['title']          ?? 'Báo cáo',
                'type'              => $history['type']           ?? 'text',
                'word_count'        => $history['word_count']     ?? 0,
                'points_used'       => $history['points_used']   ?? 0,
                'ai_score'          => $history['ai_score']       ?? round(($ai['confidence']['AI'] ?? 0) * 100),
                'email'             => $user->email ?? 'Không xác định', // <--- Đã thay thế mô hình AI thành email tại đây
                'plag_score'        => $history['plag_score']     ?? ($plag['score'] ?? 0),
                'grammar_errors'    => $history['grammar_errors'] ?? count($gram['matches'] ?? []),
                'readability_score' => $history['readability_score'] ?? ($read['readability']['fleschReadingEase'] ?? null),
                'readability_grade' => $history['readability_grade'] ?? ($read['readability']['fleschGradeLevel'] ?? null),
                'facts_total'       => $history['facts_total']   ?? count($facts),
                'facts_true'        => $history['facts_true']    ?? 0,
                'facts_errors'      => $history['facts_errors']  ?? count(array_filter($facts, fn($f) =>
                                            in_array(strtolower($f['classification'] ?? ''), ['false', 'unverified'], true)
                                        )),
                'seo_score'         => $history['seo_score']     ?? null,
                'scan_url'          => $history['scan_url']      ?? null,
                'file_url'          => $history['file_url']      ?? null,
                'created_at'        => $history['created_at']    ?? null,
                'content'           => $history['content']       ?? null,
            ];
        }
    
    private function generatePdf(array $data, string $type = 'report'): void
        {
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
        
            // Dùng DejaVu Sans từ thư viện dompdf — hỗ trợ đầy đủ Unicode/tiếng Việt
            $options->set('defaultFont', 'DejaVu Sans');
        
            $storagePath = rtrim(app()->basePath(), '/') . '/storage';
            if (!is_dir($storagePath)) {
                mkdir($storagePath, 0777, true);
            }
        
            // Trỏ fontDir về thư mục font của dompdf để dùng DejaVu có sẵn
            $dompdfFontDir = rtrim(app()->basePath(), '/') . '/vendor/dompdf/dompdf/lib/fonts';
        
            $options->set('tempDir',   $storagePath);
            $options->set('fontCache', $storagePath);
            $options->set('fontDir',   $dompdfFontDir);
            $options->set('chroot',    app()->basePath());
        
            $dompdf = new Dompdf($options);
        
            $viewName = $type === 'highlight' ? 'report/pdf_highlight' : 'report/pdf_export';
            $html = view($viewName, $data);
        
            // Bọc toàn bộ HTML trong CSS khai báo font DejaVu Sans
            $fontStyle = '
            <style>
                * {
                    font-family: "DejaVu Sans", sans-serif !important;
                }
            </style>';
        
            // Inject style vào trước </head> hoặc đầu HTML
            if (stripos($html, '</head>') !== false) {
                $html = str_ireplace('</head>', $fontStyle . '</head>', $html);
            } else {
                $html = $fontStyle . $html;
            }
        
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
        
            try {
                $dompdf->render();
            } catch (\Exception $e) {
                echo 'Lỗi xuất PDF: ' . $e->getMessage();
                exit;
            }
        
            $date = date('d-m-Y_H-i-s');
            $suffix = $type === 'highlight' ? '-Highlight' : '';
        
            $dompdf->stream("AI-Vmied-Checker-{$date}{$suffix}.pdf", [
                'Attachment' => 1,
            ]);
        }
    }