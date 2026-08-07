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
<div style="font-family: 'Times New Roman', serif; font-size: 14pt; line-height: 1.5; text-align: justify;">
<?php
foreach ($blocks as $block) {
    $bgColor = 'transparent';
    $styles = [];

    if (isset($block['result']['fake'])) {
        $fake = $block['result']['fake'];
        if ($fake > 0.8) {
            $bgColor = '#f8d7da';
        } elseif ($fake > 0.5) {
            $bgColor = '#ffeeba';
        } elseif ($fake > 0.3) {
            $bgColor = '#fff3cd';
        } else {
            $bgColor = '#d4edda';
        }
    }

    if ($bgColor !== 'transparent') {
        $styles[] = "background-color: {$bgColor}";
    }

    if (!empty($block['plagiarism'])) {
        $styles[] = "border-bottom: 2px dashed #fd7e14";
    }

    $styleAttr = !empty($styles) ? ' style="' . implode('; ', $styles) . '"' : '';
    $htmlText = nl2br($block['htmlText']); // MS Word nhận diện <br> làm ngắt dòng tự nhiên

    echo '<span' . $styleAttr . '>' . $htmlText . '</span>';
}

if (empty($blocks)) {
    echo "<em>" . (isset($__) ? $__('no_content') : 'Không có nội dung.') . "</em>";
}
?>
</div>