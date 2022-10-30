<?php

namespace Saraf\QB\QueryBuilder\Enums;

abstract class JoinDirection
{
    const Inner = "INNER";
    const Left = "LEFT";
    const Right = "RIGHT";
    const Full = "FULL";
}