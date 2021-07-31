<?php

declare(strict_types=1);

namespace Codeception\Task\Exception;

use Exception;

class XPathExpressionException extends Exception
{
    public static function malformedXPath(string $xpathExpr): self
    {
        return new self('The expression is malformed or the contextnode is invalid: ' . $xpathExpr);
    }
}
