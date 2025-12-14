<?php

namespace App\Entity;

enum AlcoholType: string
{
    case BEER = 'beer';
    case WINE = 'wine';
    case WHISKY = 'whisky';
    case SAKE = 'sake';
}
