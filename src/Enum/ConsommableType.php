<?php

namespace App\Enum;

enum ConsommableType: string
{
    // Tarots
    case FOOL = 'fool';
    case MAGICIAN = 'magician';
    case HIGH_PRIEST = 'high_priest';
    case EMPRESS = 'empress';
    case EMPEROR = 'emperor';
    case HIEROPHANT = 'hierophant';
    case LOVERS = 'lovers';
    case CHARIOT = 'chariot';
    case JUSTICE = 'justice';
    case HERMIT = 'hermit';
    case WHEEL = 'wheel';
    case STRENGTH = 'strength';
    case HANGED_MAN = 'hanged_man';
    case DEATH = 'death';
    case TEMPERANCE = 'temperance';
    case DEVIL = 'devil';
    case TOWER = 'tower';
    case STAR = 'star';
    case MOON = 'moon';
    case SUN = 'sun';
    case JUDGMENT = 'judgment';
    case WORLD = 'world';
    
    // Planets
    case MERCURY = 'mercury';
    case VENUS = 'venus';
    case EARTH = 'earth';
    case MARS = 'mars';
    case JUPITER = 'jupiter';
    case SATURN = 'saturn';
    case URANUS = 'uranus';
    case NEPTUNE = 'neptune';
    case PLUTO = 'pluto';
    case PLANET_X = 'planetX';
    case CERES = 'ceres';
    case ERIS = 'eris';
    
    // Spectrals
    case FAMILIAR = 'familiar';
    case GRIM = 'grim';
    case INCANTATION = 'incantation';
    case TALISMAN = 'talisman';
    case AURA = 'aura';
    case WRAITH = 'wraith';
    case SIGIL = 'sigil';
    case OUIJA = 'ouija';
    case ECTOPLASM = 'ectoplasm';
    case IMMOLATE = 'immolate';
    case ANKH = 'ankh';
    case DEJA_VU = 'dejavu';
    case HEX = 'hex';
    case TRANCE = 'trance';
    case MEDIUM = 'medium';
    case CRYPTID = 'cryptid';
    case SOUL = 'soul';
    case BLACK_HOLE = 'blackhole';
}
