<?php
namespace App\Controllers;

use Dompdf\Dompdf;
use Dompdf\Options;

class ReportController
{
    public function ai($id = null)
    {
        $user = app()->request->user;
        
        // Dữ liệu báo cáo dựa trên nội dung bạn cung cấp
$reportData = [
    'info' => [
        'author'        => 'Trần Thị Lam Thủy',
        'document_name' => 'GIAO TRINH-PHONG CACH HOC-NGU DUNG HOC',
        'total_pages'   => '266/266',
        'check_time'    => '01-10-2022, 08:53:24',
        'report_date'   => '01-10-2022, 09:06:29'
    ],
    'stats' => [
        'duplicate' => 14,
        'original'  => 86,
        'sources'   => ['123doc.org', 'tailieu.vn', 'vi.wikipedia.org']
    ],
    'details' => [
        [
            'page' => 1, 
            'score' => 74, 
            'text' => '(Dùng cho đào tạo giáo viên ngành Giáo dục Tiểu học)',
            'source_name' => 'LA.011600.doc',
            'source_snippet' => 'Hiện nay, việc rèn luyện kỹ năng dạy học môn Âm nhạc cho sinh viên ngành Giáo dục Tiểu học đang bị xếp vào việc không quan trọng trong quá trình đào tạo giáo viên cho trường Tiểu học.'
        ],
        [
            'page' => 4, 
            'score' => 73, 
            'source_name' => 'LA.010906.doc', 
            'text' => 'Phân loại phong cách chức năng ngôn ngữ. .25',
            'source_snippet' => 'Thậm chí do không nắm được đặc trưng và những yêu cầu diễn đạt của từng loại phong cách chức năng ngôn ngữ nên khi tạo lập văn bản thường sử dụng ngôn ngữ sai phong cách.'
        ],
        [
            'page' => 4, 
            'score' => 82, 
            'source_name' => 'luận văn .pdf.html', 
            'text' => 'Hệ thống đại từ nhân xưng... ...88',
            'source_snippet' => 'Hệ thống đại từ nhân xưng này đã tạo cho câu thơ chất trữ tình, tình tứ, ngọt ngào, đằm thắm như những câu ca xưa: Thôi ta về với mình thôi Chân trời đành để chim trời nó bay (Đường xa - Nguyễn Duy) Có ai còn nhớ đến tôi Có thương thuyền giữa sông trôi lững lờ (Chợ Thương - Đồng Đức Bốn) nhưng cũng có lúc suồng sã, vui đùa, tếu táo với cách xưng hô ta - mình, tao - mày...'
        ],
        [
            'page' => 4, 
            'score' => 90, 
            'source_name' => '16.11.2018 _ Luan an Ngu van (Dang Thi Thu) _ Ban sau bao ve.doc', 
            'text' => 'Phân biệt phát ngôn miêu tả và phát ngôn ngữ vi.... 226',
            'source_snippet' => 'Tác giả đã dành chương III để trình bày các vấn đề về hành động ngôn ngữ như: khái niệm hành động ngôn ngữ, các hành động ngôn ngữ, phát ngôn ngữ vi, phân biệt phát ngôn miêu tả và phát ngôn ngữ vi, biểu thức ngữ vi và động từ ngữ vị, các điều kiện sử dụng hành động ở lời, phân loại các hành động ở lời.'
        ],
        [
            'page' => 4, 
            'score' => 85, 
            'source_name' => 'Luận văn Hồng.pdf', 
            'text' => 'Các biện pháp tu từ văn bản.... .169',
            'source_snippet' => 'Các biện pháp tu từ được chia ra các biện pháp tu từ ngữ nghĩa, các biện pháp tu từ cú pháp, các biện pháp tu từ văn bản, các biện pháp tu từ ngữ âm. [32, tr.'
        ],
        [
            'page' => 7, 
            'score' => 82, 
            'source_name' => 'http://123doc.org/...', 
            'text' => 'Tổng quan tình hình nghiên cứu về phong cách học.',
            'source_snippet' => 'Công tác tạo động lực lao động đang là mối quan tâm của rất nhiều nhà lãnh đạo và quản lý bởi kích thích lao động có thể giúp tăng năng suất lao động nhằm tạo ra lợi nhuận cao hơn...'
        ],
        [
            'page' => 8, 
            'score' => 100, 
            'source_name' => 'luan van.pdf.html', 
            'text' => 'Phong cách quân nhân. Phong cách sống giản dị.',
            'source_snippet' => 'Phong cách quân nhân. Phong cách sống giản dị.'
        ]
    ]
];

        // Nếu có tham số ?export=pdf thì render PDF
        if (request('export') === 'pdf') {
            return $this->generatePdf($reportData);
        }

        // Tạm thời trả về giao diện cứng (report/ai) để hiển thị trên Modal
        return view('report/ai', ['id' => $id]);
    }

   private function generatePdf($data)
{
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true); // Cho phép load ảnh từ URL/Path
    $options->set('defaultFont', 'DejaVu Sans');

    // Thiết lập đường dẫn cụ thể và đảm bảo nó không rỗng
    $storagePath = rtrim(app()->basePath(), '/') . '/storage';
    
    // Tự động tạo thư mục nếu chưa có
    if (!is_dir($storagePath)) {
        mkdir($storagePath, 0777, true);
    }

    $options->set('tempDir', $storagePath);
    $options->set('fontCache', $storagePath);
    $options->set('chroot', app()->basePath());

    $dompdf = new Dompdf($options);
    
    // Render HTML
    $html = view('report/pdf_export', $data);
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'portrait');
    
    try {
        $dompdf->render();
    } catch (\Exception $e) {
        // Log lỗi nếu render thất bại để debug
        return $e->getMessage();
    }

    return $dompdf->stream("Bao-cao-ket-qua-kiem-tra.pdf", ["Attachment" => 1]);
}
}