<?php
declare(strict_types=1);

namespace jbbcode;

require_once __DIR__.'/CodeDefinition.php';
require_once __DIR__.'/CodeDefinitionBuilder.php';
require_once __DIR__.'/CodeDefinitionSet.php';
require_once __DIR__.'/validators/CssColorValidator.php';
require_once __DIR__.'/validators/UrlValidator.php';
require_once __DIR__.'/validators/ImageValidator.php';
/**
 * Provides a default set of common bbcode definitions.
 *
 * @author jbowens
 */
class DefaultCodeDefinitionSet implements CodeDefinitionSet
{
    /* The default code definitions in this set. */
    protected array $definitions = [];

    /**
     * Constructs the default code definitions.
     */
    public function __construct()
    {
        /* [b] bold tag */
        $builder = new CodeDefinitionBuilder('b', '<strong>{param}</strong>');
        $this->definitions[] = $builder->build();
        /* [i] italics tag */
        $builder = new CodeDefinitionBuilder('i', '<em>{param}</em>');
        $this->definitions[] = $builder->build();
        /* [u] underline tag */
        $builder = new CodeDefinitionBuilder('u', '<u>{param}</u>');
        $this->definitions[] = $builder->build();
        $urlValidator = new \JBBCode\validators\UrlValidator();
        /* [url] link tag */
        $builder = new CodeDefinitionBuilder('url', '<a href="{param}">{param}</a>');
        $builder->setParseContent(false)->setBodyValidator($urlValidator);
        $this->definitions[] = $builder->build();
        /* [url=https://example.com] link tag */
        $builder = new CodeDefinitionBuilder('url', '<a href="{option}">{param}</a>');
        $builder->setUseOption(true)->setParseContent(true)->setOptionValidator($urlValidator);
        $this->definitions[] = $builder->build();
        /* [img] image tag */
        $builder = new CodeDefinitionBuilder('img', '<img src="{param}" />');
        $builder->setUseOption(false)->setParseContent(false)->setBodyValidator($urlValidator);
        $this->definitions[] = $builder->build();
        /* [img=alt text] image tag */
        $builder = new CodeDefinitionBuilder('img', '<img src="{param}" alt="{option}" />');
        $builder->setUseOption(true)->setParseContent(false)->setBodyValidator($urlValidator);
        $this->definitions[] = $builder->build();
        /* [color] color tag */
        $builder = new CodeDefinitionBuilder('color', '<span style="color: {option}">{param}</span>');
        $builder->setUseOption(true)->setOptionValidator(new \JBBCode\validators\CssColorValidator());
        $this->definitions[] = $builder->build();
        /* [size] font size tag */
        $builder = new CodeDefinitionBuilder('size', '<span style="font-size: {option}">{param}</span>');
        $builder->setUseOption(true)->setParseContent(true);
        $this->definitions[] = $builder->build();
        /* [font] font face tag */
        $builder = new CodeDefinitionBuilder('font', '<span style="font: {option}">{param}</span>');
        $builder->setUseOption(true)->setParseContent(true);
        $this->definitions[] = $builder->build();
        /* [quote] quote tag */
        $builder = new CodeDefinitionBuilder('quote', '<blockquote>{param}<br /><p style="text-align:right;">~ {option}</p></blockquote>');
        $builder->setUseOption(true)->setParseContent(true);
        $this->definitions[] = $builder->build();
        /* [quote] quote tag */
        $builder = new CodeDefinitionBuilder('quote', '<blockquote>{param}</blockquote>');
        $builder->setUseOption(false)->setParseContent(true);
        $this->definitions[] = $builder->build();
    }

    /**
     * Returns an array of the default code definitions.
     */
    public function getCodeDefinitions()
    {
        return $this->definitions;
    }
}
