<?php
declare(strict_types=1);

namespace jbbcode;

require_once __DIR__.'/Node.php';
/**
 * An element within the tree. Consists of a tag name which defines the type of the
 * element and any number of Node children. It also contains a CodeDefinition matching
 * the tag name of the element.
 *
 * @author jbowens
 */
class ElementNode extends Node
{
    /* The tag name of this element, for i.e. "b" in [b]bold[/b] */
    protected string $tagName;
    /* The attribute, if any, of this element node */
    protected array $attribute;
    /* The child nodes contained within this element */
    protected array $children;
    /* The code definition that defines this element's behavior */
    protected CodeDefinition $codeDefinition;
    /* How deeply this node is nested */
    protected int $nestDepth;

    /**
     * Constructs the element node.
     */
    public function __construct()
    {
        $this->children = [];
        $this->nestDepth = 0;
    }

    /**
     * Accepts the given NodeVisitor. This is part of an implementation
     * of the Visitor pattern.
     *
     * @param NodeVisitor $nodeVisitor  the visitor attempting to visit this node
     */
    public function accept(NodeVisitor $nodeVisitor): void
    {
        $nodeVisitor->visitElementNode($this);
    }

    /**
     * Gets the CodeDefinition that defines this element.
     *
     * @return CodeDefinition $this element's code definition
     */
    public function getCodeDefinition(): CodeDefinition
    {
        return $this->codeDefinition;
    }

    /**
     * Sets the CodeDefinition that defines this element.
     *
     * @param CodeDefinition $codeDef the code definition that defines this element node
     */
    public function setCodeDefinition(CodeDefinition $codeDef): void
    {
        $this->codeDefinition = $codeDef;
        $this->setTagName($codeDef->getTagName());
    }

    /**
     * Returns the tag name of this element.
     *
     * @return string the element's tag name
     */
    public function getTagName(): string
    {
        return $this->tagName;
    }

    /**
     * Returns the attribute (used as the option in bbcode definitions) of this element.
     *
     * @return array the attribute of this element
     */
    public function getAttribute(): array
    {
        return $this->attribute;
    }

    /**
     * Returns all the children of this element.
     *
     * @return array an array of this node's child nodes
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * (non-PHPdoc).
     *
     * @see JBBCode.Node::getAsText()
     *
     * Returns the element as text (not including any bbcode markup)
     *
     * @return string the plain text representation of this node
     */
    public function getAsText(): ?string
    {
        if ($this->codeDefinition) {
            return $this->codeDefinition->asText($this);
        }

        $s = '';
        foreach ($this->getChildren() as $child) {
            $s .= $child->getAsText();
        }

        return $s;
    }

    /**
     * (non-PHPdoc).
     *
     * @see JBBCode.Node::getAsBBCode()
     *
     * Returns the element as bbcode (with all unclosed tags closed)
     *
     * @return string the bbcode representation of this element
     */
    public function getAsBBCode(): ?string
    {
        $str = '['.$this->tagName;
        if (!empty($this->attribute)) {
            foreach ($this->attribute as $key => $value) {
                if ($key === $this->tagName) {
                    $str .= '='.$value;
                } else {
                    $str .= ' '.$key.'='.$value;
                }
            }
        }
        $str .= ']';
        foreach ($this->getChildren() as $child) {
            $str .= $child->getAsBBCode();
        }
        $str .= '[/'.$this->tagName.']';

        return $str;
    }

    /**
     * (non-PHPdoc).
     *
     * @see JBBCode.Node::getAsHTML()
     *
     * Returns the element as html with all replacements made
     *
     * @return string the html representation of this node
     */
    public function getAsHTML(): string
    {
        if ($this->codeDefinition) {
            return $this->codeDefinition->asHtml($this);
        }

        return '';
    }

    /**
     * Adds a child to this node's content. A child may be a TextNode, or
     * another ElementNode... or anything else that may extend the
     * abstract Node class.
     *
     * @param Node $child the node to add as a child
     */
    public function addChild(Node $child): void
    {
        $this->children[] = $child;
        $child->setParent($this);
    }

    /**
     * Removes a child from this node's contnet.
     *
     * @param Node $child the child node to remove
     */
    public function removeChild(Node $child): void
    {
        foreach ($this->children as $key => $value) {
            if ($value === $child) {
                unset($this->children[$key]);
            }
        }
    }

    /**
     * Sets the tag name of this element node.
     *
     * @param string $tagName the element's new tag name
     */
    public function setTagName($tagName): void
    {
        $this->tagName = $tagName;
    }

    /**
     * Sets the attribute (option) of this element node.
     *
     * @param array $attribute the attribute of this element node
     */
    public function setAttribute($attribute): void
    {
        $this->attribute = $attribute;
    }

    /**
     * Traverses the parse tree upwards, going from parent to parent, until it finds a
     * parent who has the given tag name. Returns the parent with the matching tag name
     * if it exists, otherwise returns null.
     *
     * @param string $str the tag name to search for
     *
     * @return ElementNode the closest parent with the given tag name
     */
    public function closestParentOfType($str): ?ElementNode
    {
        $str = strtolower($str);
        $currentEl = $this;
        while (strtolower($currentEl->getTagName()) !== $str && $currentEl->hasParent()) {
            $currentEl = $currentEl->getParent();
        }
        if (strtolower($currentEl->getTagName()) !== $str) {
            return null;
        }

        return $currentEl;
    }
}
