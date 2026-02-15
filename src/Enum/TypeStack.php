<?php

namespace App\Enum;

enum TypeStack: string
{
    case CHIPS = 'chips';               // Chips additifs (+30)
    case MULT_FLAT = 'mult_flat';       // Multiplicateurs plats (+3)
    case MULT_MULTIPLICATEUR = 'mult_multiplicateur';  // Multiplicateurs multiplicatifs (x1.5)
    case XMULT = 'xmult';               // X Multiplicateurs (x2)
}
