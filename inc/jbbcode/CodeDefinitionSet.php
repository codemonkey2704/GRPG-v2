<?php

namespace jbbcode;

require_once 'CodeDefinition.php';
/**
 * An interface for sets of code definitons.
 *
 * @author jbowens
 */
interface CodeDefinitionSet
{
    /**
     * Retrieves the CodeDefinitions within this set as an array.
     */
    public function getCodeDefinitions();
}
