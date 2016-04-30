<?php

namespace bupy7\config\exceptions;

use RuntimeException;

/**
 * Exception thrown if a value is not found in configuations.
 * @author Belosludcev Vasilij <https://github.com/bupy7>
 * @since 1.0.4
 */
class NotFoundParamException extends RuntimeException
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Not Found Parameter';
    }
}
