<?php

namespace SmartCore\Bundle\HtmlBundle\Twig;

use SmartCore\Bundle\HtmlBundle\Html;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class HtmlExtension extends AbstractExtension
{
    protected $html;

    /**
     * Constructor.
     */
    public function __construct(Html $html)
    {
        $this->html = $html;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('smart_html', [$this, 'getHtml']),
        ];
    }

    /**
     * @return Html
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'smart_html_twig_extension';
    }
}
