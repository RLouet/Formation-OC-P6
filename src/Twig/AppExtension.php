<?php


namespace App\Twig;


use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('regex_replace', [$this, 'regexReplace']),
        ];
    }

    public function regexReplace(string $subject, string $pattern, string $replacement)
    {
        return preg_replace($pattern, $replacement, $subject);
    }
}