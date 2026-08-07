<?php
ini_set('memory_limit', '512M');
set_time_limit(300);

// Extract data
$data  = $result ?? [];
$ai    = $data['ai']              ?? [];
$plag  = $data['plagiarism']      ?? [];
$gram  = $data['grammarSpelling'] ?? [];

// ── Xử lý Highlight Nội Dung Gốc ─────────────────────────────────────
$blocks = $ai['blocks'] ?? [];
if (empty($blocks) && !empty($summary['content'])) {
    preg_match_all('/[^.!?\n]+[.!?\n]*/', $summary['content'], $matches);
    $sentences = $matches[0] ?? [$summary['content']];
    foreach ($sentences as $s) {
        $blocks[] = ['text' => $s];
    }
}

if (!empty($blocks)) {
    foreach ($blocks as &$b) {
        $b['htmlText']      = htmlspecialchars($b['text'] ?? '');
        $b['grammarErrors'] = [];
        $b['highlights']    = [];
        $b['plagiarism']    = null;
    }
    unset($b);

    $grammarMatches = $gram['matches'] ?? [];
    foreach ($grammarMatches as $match) {
        $sentence = trim(strtolower($match['sentence'] ?? ''));
        if (!$sentence) continue;
        foreach ($blocks as $idx => &$b) {
            $bText = trim(strtolower($b['text'] ?? ''));
            if (str_contains($bText, $sentence) || str_contains($sentence, $bText) || $bText === $sentence) {
                $b['grammarErrors'][] = $match;
                $errWord = '';
                if (isset($match['context']['text'], $match['context']['offset'])) {
                    $errWord = mb_substr($match['context']['text'], $match['context']['offset'], $match['length'] ?? 0);
                }
                if ($errWord) $b['highlights'][] = $errWord;
            }
        }
        unset($b);
    }

    foreach ($blocks as &$b) {
        if (!empty($b['highlights'])) {
            $uniqueWords = array_unique($b['highlights']);
            usort($uniqueWords, function($x, $y) { return mb_strlen($y) - mb_strlen($x); });
            foreach ($uniqueWords as $w) {
                $escaped = preg_quote(htmlspecialchars($w), '/');
                $b['htmlText'] = preg_replace_callback('/(<[^>]+>)|(' . $escaped . ')/ui', function ($m) {
                    if (!empty($m[1])) return $m[1];
                    if (!empty($m[2])) return '<span style="color: #dc3545; font-weight: bold; text-decoration: underline;">' . $m[2] . '</span>';
                    return $m[0];
                }, $b['htmlText']);
            }
        }
    }
    unset($b);

    $plagResults = $plag['results'] ?? [];
    if (!empty($plagResults)) {
        foreach ($plagResults as $pr) {
            $phrase = trim(strtolower($pr['phrase'] ?? $pr['match'] ?? $pr['text'] ?? ''));
            if (!$phrase) continue;
            $cleanPhrase = preg_replace('/\s+/', ' ', $phrase);
            $mapped = false;
            foreach ($blocks as $idx => &$b) {
                $bText = trim(preg_replace('/\s+/', ' ', strtolower($b['text'] ?? '')));
                if (str_contains($bText, mb_substr($cleanPhrase, 0, 40)) || str_contains($cleanPhrase, mb_substr($bText, 0, 40))) {
                    if (empty($b['plagiarism'])) { $b['plagiarism'] = $pr; $mapped = true; }
                }
            }
            unset($b);
            if (!$mapped) {
                $target = count($blocks) > 1 ? 1 : 0;
                $blocks[$target]['plagiarism'] = $pr;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; font-family: "DejaVu Sans", sans-serif !important; }
@page { size: A4; margin: 25mm 20mm; }

body {
    font-size: 14px; /* Kích thước chữ tiêu chuẩn giống Word */
    line-height: 1.6;
    color: #000;
    background: #fff;
    word-wrap: break-word;
}

.content-box {
    border: none;
    padding: 0;
    background: #fff;
    white-space: pre-wrap;
    margin-bottom: 20px;
    text-align: justify; /* Căn đều 2 bên giống văn bản thật */
}

.ai-danger { background-color: #f8d7da; }
.ai-warning-high { background-color: #ffeeba; }
.ai-warning-low { background-color: #fff3cd; }
.ai-success { background-color: #d4edda; }

.plag-border { border-bottom: 2px dashed #fd7e14; }
</style>
</head>
<body>

<div class="content-box">
<?php
foreach ($blocks as $block) {
    $classes = [];
    if (isset($block['result']['fake'])) {
        $fake = $block['result']['fake'];
        if ($fake > 0.8) $classes[] = 'ai-danger';
        elseif ($fake > 0.5) $classes[] = 'ai-warning-high';
        elseif ($fake > 0.3) $classes[] = 'ai-warning-low';
        else $classes[] = 'ai-success';
    }

    if (!empty($block['plagiarism'])) {
        $classes[] = 'plag-border';
    }

    $classStr = implode(' ', $classes);
    // Xóa khoảng trắng ảo, sử dụng hoàn toàn khoảng trắng từ dữ liệu gốc
    echo '<span class="' . $classStr . '">' . $block['htmlText'] . '</span>';
}
if (empty($blocks)) {
    echo "<em>" . (isset($__) ? $__('no_content') : 'Không có nội dung.') . "</em>";
}
?>
</div>

</body>
</html>
