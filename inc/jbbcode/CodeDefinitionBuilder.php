<?php
declare(strict_types=1);

namespace jbbcode;

use InvalidArgumentException;
use function is_int;

require_once __DIR__.'/CodeDefinition.php';
/**
 * Implements the builder pattern for the CodeDefinition class. A builder
 * is the recommended way of constructing CodeDefinition objects.
 *
 * @author jbowens
 */
class CodeDefinitionBuilder
{
    protected string $tagName;
    protected bool $useOption = false;
    protected string $replacementText;
    protected bool $parseContent = true;
    protected int $nestLimit = -1;
    protected array $optionValidator = [];
    protected ?InputValidator $bodyValidator = null;

    /**
     * Construct a CodeDefinitionBuilder.
     *
     * @param string $tagName  the tag name of the definition to build
     * @param string $replacementText  the replacement text of the definition to build
     */
    public function __construct(string $tagName, string $replacementText)
    {
        $this->tagName = $tagName;
        $this->replacementText = $replacementText;
    }

    /**
     * Sets the tag name the CodeDefinition should be built with.
     *
     * @param string $tagName the tag name for the new CodeDefinition
     * @return CodeDefinitionBuilder
     */
    public function setTagName(string $tagName): CodeDefinitionBuilder
    {
        $this->tagName = $tagName;

        return $this;
    }

    /**
     * Sets the replacement text that the new CodeDefinition should be
     * built with.
     *
     * @param string $replacementText the replacement text for the new CodeDefinition
     *
     * @return CodeDefinitionBuilder
     * @return CodeDefinitionBuilder
     */
    public function setReplacementText(string $replacementText): CodeDefinitionBuilder
    {
        $this->replacementText = $replacementText;

        return $this;
    }

    /**
     * Set whether or not the built CodeDefinition should use the {option} bbcode
     * argument.
     *
     * @param bool $option true if the definition includes an option
     * @return CodeDefinitionBuilder
     */
    public function setUseOption($option): CodeDefinitionBuilder
    {
        $this->useOption = $option;

        return $this;
    }

    /**
     * Set whether or not the built CodeDefinition should allow its content
     * to be parsed and evaluated as bbcode.
     *
     * @param bool $parseContent true if the content should be parsed
     * @return CodeDefinitionBuilder
     */
    public function setParseContent($parseContent): CodeDefinitionBuilder
    {
        $this->parseContent = $parseContent;

        return $this;
    }

    /**
     * Sets the nest limit for this code definition.
     *
     * @param int $limit
     *
     * @return CodeDefinitionBuilder
     * @throws InvalidArgumentException
     */
    public function setNestLimit(int $limit): CodeDefinitionBuilder
    {
        if (!is_int($limit) || ($limit <= 0 && $limit !== -1)) {
            throw new InvalidArgumentException('A nest limit must be a positive integer or -1.');
        }
        $this->nestLimit = $limit;

        return $this;
    }

    /**
     * Sets the InputValidator that option arguments should be validated with.
     *
     * @param InputValidator $validator the InputValidator instance to use
     * @param string|null    $option
     * @return CodeDefinitionBuilder
     */
    public function setOptionValidator(InputValidator $validator, $option = null): CodeDefinitionBuilder
    {
        if (empty($option)) {
            $option = $this->tagName;
        }
        $this->optionValidator[$option] = $validator;

        return $this;
    }

    /**
     * Sets the InputValidator that body ({param}) text should be validated with.
     *
     * @param InputValidator $validator the InputValidator instance to use
     * @return CodeDefinitionBuilder
     */
    public function setBodyValidator(InputValidator $validator): CodeDefinitionBuilder
    {
        $this->bodyValidator = $validator;

        return $this;
    }

    /**
     * Removes the attached option validator if one is attached.
     *
     * @return CodeDefinitionBuilder
     */
    public function removeOptionValidator(): CodeDefinitionBuilder
    {
        $this->optionValidator = [];

        return $this;
    }

    /**
     * Removes the attached body validator if one is attached.
     *
     * @return CodeDefinitionBuilder
     */
    public function removeBodyValidator(): CodeDefinitionBuilder
    {
        $this->bodyValidator = null;

        return $this;
    }

    /**
     * Builds a CodeDefinition with the current state of the builder.
     *
     * @return CodeDefinition a new CodeDefinition instance
     */
    public function build(): CodeDefinition
    {
        return CodeDefinition::construct($this->tagName, $this->replacementText, $this->useOption, $this->parseContent, $this->nestLimit, $this->optionValidator, $this->bodyValidator);
    }
}
