<?php
namespace App\Jobs;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;

class ExportJob
{
    protected $type;
    protected $subscription;

    public function __construct($type, $subscription)
    {
        $this->type = $type;
        $this->subscription = $subscription; // Web Push Subscription
    }

    public function handle()
    {
        // 1. Fake Data Generation (Simulate heavy DB query)
        $data = [];
        for ($i = 1; $i <= 100; $i++) {
            $data[] = [
                'ID' => $i,
                'Name' => 'User ' . $i,
                'Email' => "user$i@example.com",
                'Date' => date('Y-m-d H:i:s')
            ];
        }

        // 2. Generate File
        $filename = 'export_' . time() . '.' . ($this->type == 'pdf' ? 'pdf' : 'xlsx');
        $path = app()->basePath() . '/public/exports/' . $filename;

        // Ensure directory exists
        if (!is_dir(dirname($path)))
            mkdir(dirname($path), 0755, true);

        if ($this->type == 'excel') {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Header
            $sheet->fromArray(array_keys($data[0]), null, 'A1');
            // Data
            $sheet->fromArray($data, null, 'A2');

            $writer = new Xlsx($spreadsheet);
            $writer->save($path);
        } else {
            // PDF
            $html = '<h1>Export Data</h1><table border="1" cellpadding="5" cellspacing="0" width="100%">';
            $html .= '<thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Date</th></tr></thead><tbody>';
            foreach ($data as $row) {
                $html .= "<tr><td>{$row['ID']}</td><td>{$row['Name']}</td><td>{$row['Email']}</td><td>{$row['Date']}</td></tr>";
            }
            $html .= '</tbody></table>';

            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            file_put_contents($path, $dompdf->output());
        }

        // 3. Notify User via Web Push
        // Ensure VAPID is configured
        if (app()->push->isConfigured() && $this->subscription) {
            $downloadLink = '/exports/' . $filename;
            $payload = json_encode([
                'title' => 'Export Complete!',
                'body' => "Your {$this->type} file is ready. Click to download.",
                'url' => $downloadLink
            ]);

            app()->push->send($this->subscription, $payload);
        }
    }
}
