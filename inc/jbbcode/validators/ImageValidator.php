<?php

namespace jbbcode\validators;

require_once dirname(__DIR__).'/InputValidator.php';
class ImageValidator implements \JBBCode\InputValidator
{
    /**
     * Returns true if $input is a valid image URL.
     *
     * @param the $input string to validate
     *
     * @return bool
     * @return bool
     */
    public function validate($input)
    {
        if (!filter_var($input, FILTER_VALIDATE_URL)) {
            return false;
        }
        $image = @getimagesize($input) or false;
        if (!is_array($image) || !$image[0] || !$image[1]) {
            return false;
        }

        return true;
    }
}
