<?php
require_once __DIR__ . '/../../vendor/autoload.php'; // Ajusta ruta si es necesario

class MYPDF extends \TCPDF
{
    public function Header()
    {
        // Logo izquierdo
        $image_file = __DIR__ . '/../../public/img/escudo.png';
        $this->Image($image_file, 15, 10, 14, 15, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);

        // Logo derecho
        $image_file2 = __DIR__ . '/../../public/img/logo.png';
        $this->Image($image_file2, 160, 8, 35, 15, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);

        // Rectángulos y textos (ajusta X/Y/tamaño/colores según tu diseño)
        $this->SetFont('helvetica', 'B', 12);
        $this->SetXY(30, 10);
        $this->SetFillColor(169, 169, 169);
        $this->Cell(15, 15, "PERÚ", 0, 0, 'C', 1);
        $this->SetFont('helvetica', 'B', 10);
        $this->SetXY(46, 10);
        $this->SetFillColor(189, 189, 189);
        $this->MultiCell(30, 15, 'Ministerio de Educación', 0, 'C', 1, 0, '', '', true);
        $this->SetXY(77, 10);
        $this->SetFillColor(192, 192, 192);
        //$this->Cell(90, 15, "Dirección Regional de Educación de Ayacucho", 1, 0, 'C', 1);
        //$pdf->MultiCell($w, $h, $txt, $border, $align, $fill, $ln, $x, $y, $reseth, $stretch, $ishtml, $autopadding, $maxh, 'M', $fitcell);
        $this->MultiCell(65, 15, 'Dirección Regional de Educación de Lima Metropolitana', 0, 'C', 1, 0, '', '', true,0,false,0,0,'M',false);
        $this->SetXY(143, 10);
        $this->SetFont('helvetica', 'B', 12);
        $this->SetFillColor(169, 169, 169);
        $this->Cell(15, 15, "UA", 0, 0, 'C', 1);
    }
    public function Footer() {
        // Posiciona el footer a 25 mm del fondo
        $this->SetY(-25);
        // Ancho de página
        $width = $this->getPageWidth() - $this->lMargin - $this->rMargin;

        // Fondo de color (RGB: #dee6c8 aprox)
        $this->SetFillColor(222, 230, 200);
        // Borde negro
        $this->SetDrawColor(0, 0, 0);

        // Rectángulo del footer
        $this->Rect($this->lMargin, $this->GetY(), $width, 18, 'DF'); // 'DF' = Draw + Fill

        // Texto principal (izquierda)
        $this->SetFont('helvetica', 'B', 10);
        $this->SetTextColor(0, 0, 0);
        $this->SetXY($this->lMargin + 5, $this->GetY() + 2);
        $this->Cell(0, 7, 'IESTP “Huanta” – UNIDAD ACADÉMICA', 0, 1, 'L', false, '', 0, false, 'T', 'M');

        // Segunda línea (izquierda)
        $this->SetFont('helvetica', 'B', 10);
        $this->SetX($this->lMargin + 5);
        $this->Cell(0, 6, 'https://iestphuanta.edu.pe', 0, 0, 'L', false, '', 0, false, 'T', 'M');

        // Dirección (centro, en la misma línea)
        $this->SetFont('helvetica', 'B', 10);
        $this->SetX($this->lMargin + 70);
        $this->Cell(0, 6, 'Jr. General Córdoba N° 650. Huanta Perú', 0, 0, 'L', false, '', 0, false, 'T', 'M');

        // Número de página (derecha)
        $this->SetFont('helvetica', 'B', 10);
        $this->SetXY(-45, $this->GetY());
        $this->Cell(40, 7, 'Pag. '.$this->getAliasNumPage(), 0, 0, 'R', false, '', 0, false, 'T', 'M');
    }
}
