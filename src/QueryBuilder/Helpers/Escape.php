<?php

namespace Saraf\QB\QueryBuilder\Helpers;

use Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException;

trait Escape
{
    /**
     * @throws QueryBuilderException
     */
    protected function keyEscape(string $value): string
    {
        if (empty(trim($value)))
            throw new QueryBuilderException("Can not escape empty string");

        $parts = explode(".", $value);
        $str = "";
        foreach ($parts as $part)
            if ($part !== '*')
                $str .= "`$part`.";
            else
                $str .= "$part.";

        return substr($str, 0, -1);
    }

    /**
     * @throws QueryBuilderException
     */
    protected function escape(mixed $value): mixed
    {
        return match (gettype($value)) {
            'NULL' => 'NULL',
            'integer', 'double' => $value,
            'boolean' => intval($value),
            'string' => sprintf("'%s'", $this->escapeString($value)),
            'array' => implode(',', array_map([$this, 'escape'], $value)),
            default => throw new QueryBuilderException(sprintf('Unsupported (%s) value type.', gettype($value)))
        };
    }

    /*
     * Note that this mapping assumes an ASCII-compatible charset encoding such
     * as UTF-8, ISO 8859 and others.
     *
     * Note that `'` will be escaped as `''` instead of `\'` to provide some
     * limited support for the `NO_BACKSLASH_ESCAPES` SQL mode. This assumes all
     * strings will always be enclosed in `'` instead of `"` which is guaranteed
     * as long as this class is only used internally for the `query()` method.
     */
    protected function escapeString(string $query): string
    {
        $replacementMap = [
            "\0" => "\\0",
//            "\n" => "\\n",
//            "\r" => "\\r",
//            "\t" => "\\t",
//            chr(26) => "\\Z",
//            chr(8) => "\\b",
//            '"' => '\"',
            "'"    => "''",
//            '_' => "\_",
//            "%" => "\%",
            "\\" => "\\\\"
        ];

        return strtr($query, $replacementMap);
    }
}
