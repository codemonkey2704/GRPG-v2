<?php

namespace jbbcode;

/**
 * Defines an interface for validation filters for bbcode options and
 * parameters.
 *
 * @author jbowens
 *
 * @since May 2013
 */
interface InputValidator
{
    /**
     * Returns true iff the given input is valid, false otherwise.
     *
     * @param $input
     */
    public function validate($input);
}
