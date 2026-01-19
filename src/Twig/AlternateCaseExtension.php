<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AlternateCaseExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('alternateCase', [$this, 'alternateCase']),
        ];
    }

    public function alternateCase(string $text): string
    {
        $result = '';
        $index = 0;
        
        // Parcourir chaque caractère de la chaîne
        for ($i = 0; $i < mb_strlen($text); $i++) {
            $char = mb_substr($text, $i, 1);
            
            // Si c'est une lettre, alterner majuscule/minuscule
            if (preg_match('/\p{L}/u', $char)) {
                if ($index % 2 === 0) {
                    $result .= mb_strtoupper($char);
                } else {
                    $result .= mb_strtolower($char);
                }
                $index++;
            } else {
                // Garder les espaces et la ponctuation tels quels
                $result .= $char;
            }
        }
        
        return $result;
    }
}
