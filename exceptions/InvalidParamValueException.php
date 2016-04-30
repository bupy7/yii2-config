<?php

namespace bupy7\config\exceptions;

use UnexpectedValueException;

/**
 * Exception thrown if a value dost not match a validation rules.
 * @author Belosludcev Vasilij <https://github.com/bupy7>
 * @since 1.0.4
 */
class InvalidParamValueException extends UnexpectedValueException
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Invalud Parameter Value';
    }
}
