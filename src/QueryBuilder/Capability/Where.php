<?php

namespace Saraf\QB\QueryBuilder\Capability;

use Saraf\QB\QueryBuilder\Helpers\Escape;

trait Where
{
    use Escape;

    private array $whereStatements = [];

    public function where(string $key, mixed $value, bool $escapeValue = true, bool $escapeKey = true): static
    {
        if ($escapeValue)
            $value = $this->escape($value);

        if ($escapeKey)
            $key = $this->keyEscape($key);

        $this->append($key, "=", $value);
        return $this;
    }

    public function whereNotEqual(string $key, mixed $value, bool $escapeValue = true, bool $escapeKey = true): static
    {
        if ($escapeValue)
            $value = $this->escape($value);

        if ($escapeKey)
            $key = $this->keyEscape($key);

        $this->append($key, "!=", $value);
        return $this;
    }

    public function whereIn(string $key, array $valuesArray, bool $escapeValue = true, bool $escapeKey = true): static
    {
        if ($escapeValue)
            foreach ($valuesArray as $aKey => $aValue) {
                $valuesArray[$aKey] = $this->escape($aValue);
            }

        if ($escapeKey)
            $key = $this->keyEscape($key);

        $this->append($key, "IN", sprintf("(%s)", implode(",", $valuesArray)));
        return $this;
    }

    /*
     * Creates a custom where clause.
     * This is not escaped and considered dangerous use it wisely
     */
    public function whereQuery(string $query): static
    {
        if (count($this->whereStatements) == 0) {
            $state = 0;
        } else {
            $state = count($this->whereStatements) - 1;
        }

        $this->whereStatements[$state][] = $query;

        return $this;
    }

    public function whereNotIn(string $key, array $valuesArray, bool $escapeValue = true, bool $escapeKey = true): static
    {
        if ($escapeValue)
            foreach ($valuesArray as $aKey => $aValue) {
                $valuesArray[$aKey] = $this->escape($aValue);
            }

        if ($escapeKey)
            $key = $this->keyEscape($key);

        $this->append($key, "NOT IN", sprintf("(%s)", implode(",", $valuesArray)));
        return $this;
    }

    public function whereGreater(string $key, int|float|string $greaterThan, $greaterEquals = false, $escapeValue = true, bool $escapeKey = true): static
    {
        if ($escapeValue)
            $greaterThan = $this->escape($greaterThan);

        if ($escapeKey)
            $key = $this->keyEscape($key);

        $this->append($key, $greaterEquals ? ">=" : ">", $greaterThan);
        return $this;
    }

    public function whereLesser(string $key, int|float|string $lesserThan, bool $lesserEquals = false, bool $escapeValue = true, bool $escapeKey = true): static
    {
        if ($escapeValue)
            $lesserThan = $this->escape($lesserThan);

        if ($escapeKey)
            $key = $this->keyEscape($key);

        $this->append($key, $lesserEquals ? "<=" : "<", $lesserThan);
        return $this;
    }

    public function whereBetween(string $key, int|float|string $lessValue, int|float|string $greatValue, bool $escapeValue = true, bool $escapeKey = true): static
    {
        if ($escapeValue) {
            $greatValue = $this->escape($greatValue);
            $lessValue = $this->escape($lessValue);
        }

        if ($escapeKey)
            $key = $this->keyEscape($key);

        $this->append($key, "BETWEEN", "$lessValue AND $greatValue");
        return $this;
    }

    public function whereNotBetween(string $key, int|float|string $lessValue, int|float|string $greatValue, bool $escapeValue = true, bool $escapeKey = true): static
    {
        if ($escapeValue) {
            $greatValue = $this->escape($greatValue);
            $lessValue = $this->escape($lessValue);
        }

        if ($escapeKey)
            $key = $this->keyEscape($key);

        $this->append($key, "NOT BETWEEN", "$lessValue AND $greatValue");
        return $this;
    }

    public function whereIsNull(string $key, bool $escapeKey = true): static
    {
        if ($escapeKey)
            $key = $this->keyEscape($key);

        $this->append($key, "IS", 'NULL');
        return $this;
    }

    public function whereIsNotNull(string $key, bool $escapeKey = true): static
    {
        if ($escapeKey)
            $key = $this->keyEscape($key);

        $this->append($key, "IS NOT", 'NULL');
        return $this;
    }

    public function whereLike(string $key, mixed $value, bool $begin = true, bool $end = true, bool $escapeValue = true, bool $escapeKey = true): static
    {
        // TODO: START DRY
        if ($escapeValue) {
            if ($begin)
                $value = "%" . $value;

            if ($end)
                $value = $value . "%";

            $value = $this->escape($value);
        } else {
            if ($begin)
                $value = "'%'." . $value;

            if ($end)
                $value = $value . ".'%'";
        }

        if ($escapeKey)
            $key = $this->keyEscape($key);
        // TODO: END DRY

        $this->append($key, "LIKE", $value);
        return $this;
    }

    public function whereNotLike(string $key, mixed $value, bool $begin = true, bool $end = true, bool $escapeValue = true, bool $escapeKey = true): static
    {
        // TODO: START DRY
        if ($escapeValue) {
            if ($begin)
                $value = "%" . $value;

            if ($end)
                $value = $value . "%";

            $value = $this->escape($value);
        } else {
            if ($begin)
                $value = "'%'." . $value;

            if ($end)
                $value = $value . ".'%'";
        }

        if ($escapeKey)
            $key = $this->keyEscape($key);

        // TODO: END DRY


        $this->append($key, "NOT LIKE", $value);
        return $this;
    }

    public function whereGroup(array $keyValuesArray, bool $escapeValue = true, bool $escapeKey = true): static
    {
        foreach ($keyValuesArray as $key => $value) {
            if (is_bool($value))
                $this->where($key, $value, true, $escapeKey);
            else
                $this->where($key, $value, $escapeValue, $escapeKey);
        }

        return $this;
    }

    public function or(): static
    {
        $this->whereStatements[] = [];
        return $this;
    }

    private function append($key, $operator, $value)
    {
        if (count($this->whereStatements) == 0)
            $state = 0;
        else
            $state = count($this->whereStatements) - 1;

        $this->whereStatements[$state][] = sprintf("%s %s %s", $key, $operator, $value);
    }
}
