<?php
declare(strict_types=1);

namespace jbbcode;

/**
 * This class represents a BBCode Definition. You may construct instances of this class directly,
 * usually through the CodeDefinitionBuilder class, to create text replacement bbcodes, or you
 * may subclass it to create more complex bbcode definitions.
 *
 * @author jbowens
 */
class CodeDefinition
{
    /* NOTE: THIS PROPERTY SHOULD ALWAYS BE LOWERCASE; USE setTagName() TO ENSURE THIS */
    protected string $tagName;
    /* Whether or not this CodeDefinition uses an option parameter. */
    protected bool $useOption;
    /* The replacement text to be used for simple CodeDefinitions */
    protected string $replacementText;
    /* Whether or not to parse elements of this definition's contents */
    protected bool $parseContent;
    /* How many of this element type may be nested within each other */
    protected int $nestLimit;
    /* How many of this element type have been seen */
    protected int $elCounter;
    /* The input validator to run options through */
    protected array $optionValidator;
    /* The input validator to run the body ({param}) through */
    protected ?InputValidator $bodyValidator;

    /**
     * Constructs a new CodeDefinition.
     *
     * @param       $tagName
     * @param       $replacementText
     * @param bool  $useOption
     * @param bool  $parseContent
     * @param int   $nestLimit
     * @param array $optionValidator
     * @param null  $bodyValidator
     *
     * @return CodeDefinition
     */
    public static function construct($tagName, $replacementText, $useOption = false, $parseContent = true, $nestLimit = -1, $optionValidator = [], $bodyValidator = null)
    {
        $def = new self();
        $def->elCounter = 0;
        $def->setTagName($tagName);
        $def->setReplacementText($replacementText);
        $def->useOption = $useOption;
        $def->parseContent = $parseContent;
        $def->nestLimit = $nestLimit;
        $def->optionValidator = $optionValidator;
        $def->bodyValidator = $bodyValidator;

        return $def;
    }

    /**
     * Constructs a new CodeDefinition.
     *
     * This constructor is deprecated. You should use the static construct() method or the
     * CodeDefinitionBuilder class to construct a new CodeDefiniton.
     *
     * @deprecated
     */
    public function __construct()
    {
        /* WARNING: This function is deprecated and will be made protected in a future
         * version of jBBCode. */
        $this->parseContent = true;
        $this->useOption = false;
        $this->nestLimit = -1;
        $this->elCounter = 0;
        $this->optionValidator = [];
        $this->bodyValidator = null;
    }

    /**
     * Determines if the arguments to the given element are valid based on
     * any validators attached to this CodeDefinition.
     *
     * @param ElementNode $el  the ElementNode to validate
     *
     * @return bool true if the ElementNode's {option} and {param} are OK, false if they're not
     */
    public function hasValidInputs(ElementNode $el): bool
    {
        if ($this->optionValidator && $this->usesOption()) {
            $att = $el->getAttribute();
            foreach ($att as $name => $value) {
                if (isset($this->optionValidator[$name]) && !$this->optionValidator[$name]->validate($value)) {
                    return false;
                }
            }
        }
        if ($this->bodyValidator && !$this->parseContent()) {
            /* We only evaluate the content if we're not parsing the content. */
            $content = '';
            foreach ($el->getChildren() as $child) {
                $content .= $child->getAsBBCode();
            }
            if (!$this->bodyValidator->validate($content)) {
                /* The content of the element is not valid. */
                return false;
            }
        }

        return true;
    }

    /**
     * Accepts an ElementNode that is defined by this CodeDefinition and returns the HTML
     * markup of the element. This is a commonly overridden class for custom CodeDefinitions
     * so that the content can be directly manipulated.
     *
     * @param ElementNode $el  the element to return an html representation of
     *
     * @return string the parsed html of this element (INCLUDING ITS CHILDREN)
     */
    public function asHtml(ElementNode $el): string
    {
        if (!$this->hasValidInputs($el)) {
            return $el->getAsBBCode();
        }
        $html = $this->getReplacementText();
        if ($this->usesOption()) {
            $options = $el->getAttribute();
            if (count($options) === 1) {
                $vals = array_values($options);
                $html = str_ireplace('{option}', reset($vals), $html);
            } else {
                foreach ($options as $key => $val) {
                    $html = str_ireplace('{'.$key.'}', $val, $html);
                }
            }
        }
        $content = $this->getContent($el);
        $html = str_ireplace('{param}', $content, $html);

        return $html;
    }

    /**
     * @param ElementNode $el
     *
     * @return string
     */
    protected function getContent(ElementNode $el): string
    {
        if ($this->parseContent()) {
            $content = '';
            foreach ($el->getChildren() as $child) {
                $content .= $child->getAsHTML();
            }
        } else {
            $content = '';
            foreach ($el->getChildren() as $child) {
                $content .= $child->getAsBBCode();
            }
        }

        return $content;
    }

    /**
     * Accepts an ElementNode that is defined by this CodeDefinition and returns the text
     * representation of the element. This may be overridden by a custom CodeDefinition.
     *
     * @param ElementNode $el  the element to return a text representation of
     *
     * @return string the text representation of $el
     */
    public function asText(ElementNode $el): string
    {
        if (!$this->hasValidInputs($el)) {
            return $el->getAsBBCode();
        }
        $s = '';
        foreach ($el->getChildren() as $child) {
            $s .= $child->getAsText();
        }

        return $s;
    }

    /**
     * Returns the tag name of this code definition.
     *
     * @return string $this definition's associated tag name
     */
    public function getTagName(): string
    {
        return $this->tagName;
    }

    /**
     * Returns the replacement text of this code definition. This usually has little, if any meaning if the
     * CodeDefinition class was extended. For default, html replacement CodeDefinitions this returns the html
     * markup for the definition.
     *
     * @return string the replacement text of this CodeDefinition
     */
    public function getReplacementText(): string
    {
        return $this->replacementText;
    }

    /**
     * Returns whether or not this CodeDefinition uses the optional {option}.
     *
     * @return bool true if this CodeDefinition uses the option, false otherwise
     */
    public function usesOption(): bool
    {
        return $this->useOption;
    }

    /**
     * Returns whether or not this CodeDefnition parses elements contained within it,
     * or just treats its children as text.
     *
     * @return bool true if this CodeDefinition parses elements contained within itself
     */
    public function parseContent(): bool
    {
        return $this->parseContent;
    }

    /**
     * Returns the limit of how many elements defined by this CodeDefinition may be
     * nested together. If after parsing elements are nested beyond this limit, the
     * subtrees formed by those nodes will be removed from the parse tree. A nest
     * limit of -1 signifies no limit.
     *
     * @return int
     */
    public function getNestLimit(): int
    {
        return $this->nestLimit;
    }

    /**
     * Sets the tag name of this CodeDefinition.
     *
     * @deprecated
     *
     * @param string $tagName the new tag name of this definition
     */
    public function setTagName($tagName): void
    {
        $this->tagName = strtolower($tagName);
    }

    /**
     * Sets the html replacement text of this CodeDefinition.
     *
     * @deprecated
     *
     * @param string $txt the new replacement text
     */
    public function setReplacementText($txt): void
    {
        $this->replacementText = $txt;
    }

    /**
     * Sets whether or not this CodeDefinition uses the {option}.
     *
     * @deprecated
     *
     * @param bool $bool
     */
    public function setUseOption($bool): void
    {
        $this->useOption = $bool;
    }

    /**
     * Sets whether or not this CodeDefinition allows its children to be parsed as html.
     *
     * @deprecated
     *
     * @param bool $bool
     */
    public function setParseContent($bool): void
    {
        $this->parseContent = $bool;
    }

    /**
     * Increments the element counter. This is used for tracking depth of elements of the same type for next limits.
     *
     * @deprecated
     */
    public function incrementCounter(): void
    {
        ++$this->elCounter;
    }

    /**
     * Decrements the element counter.
     *
     * @deprecated
     */
    public function decrementCounter(): void
    {
        --$this->elCounter;
    }

    /**
     * Resets the element counter.
     *
     * @deprecated
     */
    public function resetCounter(): void
    {
        $this->elCounter = 0;
    }

    /**
     * Returns the current value of the element counter.
     *
     * @deprecated
     *
     * @return int
     */
    public function getCounter(): int
    {
        return $this->elCounter;
    }
}
