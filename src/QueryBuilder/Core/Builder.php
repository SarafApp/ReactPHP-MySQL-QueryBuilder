<?php

namespace Saraf\QB\QueryBuilder\Core;

class Builder
{
    public static function asAlias(string $aliasName): string
    {
        return " AS $aliasName";
    }

    public static function setOnDuplicateKeyUpdate(array $updates): string
    {
        $baseQuery = " ON DUPLICATE KEY UPDATE ";

        foreach ($updates as $updateKey => $updateValue)
            $baseQuery .= sprintf("%s = %s, ", $updateKey, $updateValue);

        return substr($baseQuery, 0, -2);
    }

    public static function setDeleteTable(string $fromTable): string
    {
        return "DELETE FROM $fromTable";
    }

    public static function setInsertRows(array $rows): string
    {
        $baseQuery = "";
        foreach ($rows as $row) {
            $baseQuery .= "(" . implode(",", $row) . "),";
        }

        return substr($baseQuery, 0, -1);
    }

    public static function setInsertColumns(array $columns): string
    {
        return "(" . implode(",", $columns) . ") VALUES ";
    }

    public static function setInsertTable(string $intoTable): string
    {
        return "INSERT INTO $intoTable ";
    }

    public static function setUpdates(array $updates): string
    {
        if (count($updates) > 0)
            return implode(", ", $updates);
        return "";
    }

    public static function setUpdateTable($table): string
    {
        return "UPDATE $table SET ";
    }

    public static function select(array $statements, bool $isDistinct): string
    {
        $baseQuery = "SELECT ";
        if ($isDistinct) {
            $baseQuery .= "DISTINCT ";
        }

        $baseQuery .= implode(",", $statements);
        return $baseQuery;
    }

    public static function from(string $from): string
    {
        return " FROM $from ";
    }

    public static function joins(array $joins): string
    {
        if (count($joins) > 0) {
            return implode(" ", $joins);
        }
        return "";
    }

    public static function where(array $whereStatements): string
    {
        if (count($whereStatements) > 0) {
            $baseQuery = " WHERE ";
            foreach ($whereStatements as $whereStatement) {
                if (count($whereStatement) != 0)
                    $baseQuery .= "(" . implode(" AND ", $whereStatement) . ") OR ";
            }
            return substr($baseQuery, 0, -4);
        }
        return "";
    }

    public static function groupBy(array $groupBy): string
    {
        if (count($groupBy) > 0) {
            return " GROUP BY " . implode(",", $groupBy);
        }
        return "";
    }

    public static function orderBy(array $orderBy): string
    {
        if (count($orderBy) == 0)
            return "";

        $baseQuery = " ORDER BY";
        foreach ($orderBy as $orderColumn => $orderDirection) {
            $baseQuery .= " $orderColumn";
            if ($orderDirection != "")
                $baseQuery .= " $orderDirection";

            $baseQuery .= ",";
        }
        return substr($baseQuery, 0, -1);
    }

    public static function offset($offset): string
    {
        if (!empty($offset)) {
            return " OFFSET $offset";
        }
        return "";
    }

    public static function count($count): string
    {
        if (!empty($count)) {
            return " LIMIT $count";
        }
        return "";
    }

}
