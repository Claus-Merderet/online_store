<?php

namespace App\Enum;

enum DeliveryType: string
{
    case COURIER = 'courier';
    case SELF_DELIVERY = 'self delivery';
}
