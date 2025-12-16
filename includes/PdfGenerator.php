<?php
require_once __DIR__ . '/../vendor/autoload.php'; // If using Composer
require_once __DIR__ . '/../fpdf/fpdf.php'; 

class PdfGenerator extends FPDF {
    // Override header
    function Header() {
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 10, 'Lamaran Pekerjaan', 0, 1, 'C');
        $this->Ln(10);
    }
}

function generateApplicationPDF($userData, $job, $coverLetter) {
    // Create PDF instance
    $pdf = new PdfGenerator();
    
    // Add a page
    $pdf->AddPage();

    // Data Pelamar
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Data Pelamar:', 0, 1);
    
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(35, 7, 'Nama', 0);
    $pdf->Cell(5, 7, ':', 0);
    $pdf->Cell(0, 7, $userData['full_name'], 0, 1);
    
    $pdf->Cell(35, 7, 'Email', 0);
    $pdf->Cell(5, 7, ':', 0);
    $pdf->Cell(0, 7, $userData['email'], 0, 1);

    // Pendidikan
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Pendidikan:', 0, 1);
    
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(35, 7, 'Gelar', 0);
    $pdf->Cell(5, 7, ':', 0);
    $pdf->Cell(0, 7, $userData['degree'] ?? '-', 0, 1);
    
    $pdf->Cell(35, 7, 'Jurusan', 0);
    $pdf->Cell(5, 7, ':', 0);
    $pdf->Cell(0, 7, $userData['major'] ?? '-', 0, 1);

    // Posisi yang Dilamar
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Posisi yang Dilamar:', 0, 1);
    
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(0, 7, $job['title'], 0, 1);

    // Cover Letter
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Cover Letter:', 0, 1);
    
    $pdf->SetFont('Arial', '', 11);
    $pdf->MultiCell(0, 7, $coverLetter);

    // Tanggal
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 7, 'Diajukan pada: ' . date('d/m/Y H:i'), 0, 1, 'R');

    return $pdf;
}