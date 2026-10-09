<?php

require_once __DIR__ . '/../vendor/autoload.php';

class PdfGenerator
{
    public static function render(string $html, string $filePath): bool
    {
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('College Exam System');
        $pdf->SetAuthor('College Exam System');
        $pdf->SetTitle('Generated Question Paper');
        $pdf->SetHeaderData('', 0, '', '');
        $pdf->setHeaderFont(['helvetica', '', 10]);
        $pdf->setFooterFont(['helvetica', '', 8]);
        $pdf->SetDefaultMonospacedFont('courier');
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetHeaderMargin(10);
        $pdf->SetFooterMargin(10);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->setImageScale(1.25);
        $pdf->SetFont('dejavusans', '', 10);

        $pdf->AddPage();
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output($filePath, 'F');

        return file_exists($filePath);
    }
}
