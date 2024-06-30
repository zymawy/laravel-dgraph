<?php

namespace Zymawy\Dgraph\Types;

enum EnumType: string
{
    case Int = 'Int';
    case String = 'String';
    case Boolean = 'Bool';
    case DateTime = 'DateTime';
    case Float = 'Float';
    case ID = 'ID';
    case Point = 'Geo';
    case UID = 'uid';
}
