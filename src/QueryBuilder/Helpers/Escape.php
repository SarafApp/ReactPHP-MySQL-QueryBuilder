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

    protected function escape(mixed $value): mixed
    {
        if (is_null($value))
            return 'NULL';

        if (gettype($value) == 'integer' || gettype($value) == 'double')
            return $value;

        if (is_bool($value))
            return intval($value);

        return sprintf("'%s'", $this->escapeString($value));
    }

    protected function escapeString(string $query): string
    {
        $replacementMap = [
            "\0" => "\\0",
            "\n" => "\\n",
            "\r" => "\\r",
            "\t" => "\\t",
            chr(26) => "\\Z",
            chr(8) => "\\b",
            '"' => '\"',
            "'" => "\'",
            '_' => "\_",
//            "%" => "\%",
            '\\' => '\\\\'
        ];

        return strtr($query, $replacementMap);
    }
}
