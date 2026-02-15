<?php

namespace App\Enum;

enum CarteStatus: string
{
    case BASE = 'base';
    case FOIL = 'foil';
    case HOLOGRAPHIC = 'holographic';
    case POLYCHROME = 'polychrome';
}
