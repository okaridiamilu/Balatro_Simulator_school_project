<?php

namespace App\Enum;

enum CarteStatusMatter: string
{
    case BASE = 'base';
    case BONUS = 'bonus';
    case MULT = 'mult';
    case WILD = 'wild';
    case GLASS = 'glass';
    case STEEL = 'steel';
    case STONE = 'stone';
    case GOLD = 'gold';
    case LUCKY = 'lucky';
}
