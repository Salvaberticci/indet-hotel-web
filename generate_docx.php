<?php
require 'vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use Parsedown;

// Read README.md
$readmeContent = file_get_contents('README.md');

// Convert Markdown to HTML
$parsedown = new Parsedown();
$htmlContent = $parsedown->text($readmeContent);

// Create PHPWord instance
$phpWord = new PhpWord();

// Add a section
$section = $phpWord->addSection();

// Add HTML content
\PhpOffice\PhpWord\Shared\Html::addHtml($section, $htmlContent);

// Save as DOCX
$writer = IOFactory::createWriter($phpWord, 'Word2007');
$writer->save('README.docx');

echo "DOCX generated successfully: README.docx";
?>