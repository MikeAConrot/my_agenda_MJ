<?php

namespace App\Controller\CreadorPDF;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Smalot\PdfParser\Parser;
use Symfony\Component\HttpKernel\KernelInterface;

class PdfController extends AbstractController
{
    private $kernel;
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }


    public function pdf(): Response
{ 
    $pdffile = $this->kernel->getProjectDir(). '/public/RutronickFactura.PDF';

    $parser = new Parser();
    $pdf = $parser->parseFile($pdffile); // Asegúrate de proporcionar la ruta correcta al archivo PDF
    
    $text = $pdf->getText(); // Obtiene todo el texto del PDF

    // Función para limpiar y formatear el texto
    $cleanedText = $this->formatPdfText($text);

    // Pasar el texto limpio a la plantilla Twig
    return $this->render('my_agendamj\CreadorPDF\pdfMain.html.twig', [
        'pdfText' => $cleanedText,
    ]);
}

private function formatPdfText($text)
{
    // Primero, intentamos eliminar o reemplazar caracteres especiales y secuencias de escape comunes
    $cleanedText = preg_replace('/[^A-Za-z0-9\s\-\.\/\,\:\;\?\!\@\#\$\%\^\&\*\(\)\_\+\=\[\]\{\}\\\|\`\~\`\_\'\"]/','', $text);
    
    // Luego, intentamos reemplazar secuencias de escape específicas con espacios o guiones bajos
    $cleanedText = preg_replace('/(&[0-9]{2})([0-9])/','&$1$2 ', $cleanedText);
    $cleanedText = preg_replace('/(&[0-9]{2})/','&$1 ', $cleanedText);
    
    // Intentamos normalizar el texto para mejorar la legibilidad
    $cleanedText = str_replace('&', '&amp;', $cleanedText); // Reemplaza '&' con '&amp;'
    $cleanedText = str_replace('(', '&#40;', $cleanedText); // Reemplaza '(' con '&#40;'
    $cleanedText = str_replace(')', '&#41;', $cleanedText); // Reemplaza ')' con '&#41;'
    $cleanedText = str_replace('/', '&#47;', $cleanedText); // Reemplaza '/' con '&#47;'
    
    // Reemplazar múltiples espacios en blanco con un solo espacio
    $cleanedText = preg_replace('/\s+/', ' ', $cleanedText);
    
    // Convertir el texto en minúsculas para consistencia
    $cleanedText = strtolower($cleanedText);
    
    // Dividir el texto en líneas legibles
    $lines = explode("\n", $cleanedText);
    
    // Formatear cada línea para mejorar la legibilidad
    $formattedLines = array_map(function($line) {
        return nl2br($line); // Convertir saltos de línea en etiquetas HTML
    }, $lines);
    
    // Unir las líneas formateadas en una sola cadena
    $formattedText = implode('<br>', $formattedLines);
    
    return $formattedText;
}



    
}
