<?php


class CertificadosModel extends FPDF{
    function Header()
    {
        $logo = __DIR__ . '/../../public/img/ifbalogo.png';
        list($w, $h) = getimagesize($logo);
        // Logo
        $this->Image($logo, 0, 50, 80);
    }

    function Footer()
    {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial','I',8);
        // Page number
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }
}