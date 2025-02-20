<?php

namespace jbbcode\validators;

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'InputValidator.php';
/**
 * An InputValidator for urls. This can be used to make [url] bbcodes secure.
 *
 * @author jbowens
 *
 * @since May 2013
 */
class UrlValidator implements \JBBCode\InputValidator
{
    /**
     * Returns true iff $input is a valid url.
     *
     * @param the $input string to validate
     *
     * @return bool
     * @return bool
     */
    public function validate($input)
    {
        $valid = filter_var($input, FILTER_VALIDATE_URL);

        return (bool) $valid;
    }
}
