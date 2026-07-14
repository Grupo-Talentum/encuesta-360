<?php

namespace App\Enums;

enum RelationType: string
{
    case Superior = 'superior';
    case Subordinate = 'subordinate';
    case Peer = 'peer';
}
