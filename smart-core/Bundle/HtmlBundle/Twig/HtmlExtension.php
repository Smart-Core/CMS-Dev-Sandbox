<?php

namespace SmartCore\Bundle\HtmlBundle\Twig;

use SmartCore\Bundle\HtmlBundle\Html;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class HtmlExtension extends AbstractExtension
{
    public function __construct(
        protected Html $html
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('smart_html', [$this, 'getHtml']),
        ];
    }

    public function getHtml(): Html
    {
        return $this->html;
    }

    public function getName(): string
    {
        return 'smart_html_twig_extension';
    }
}
