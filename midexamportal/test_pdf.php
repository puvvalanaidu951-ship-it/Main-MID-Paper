<?php
require 'd:/vendor/autoload.php';
require 'd:/includes/PdfGenerator.php';
$html = '<h1>Test PDF</h1><p>This is a validation.</p>';
$ok = PdfGenerator::render($html, 'd:/uploads/papers/test-pdf-generator.pdf');
echo $ok ? 'OK' : 'FAIL';

