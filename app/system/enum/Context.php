<?php

namespace Enum;

/**
 * API visszateresi eredmeny statuszok
 */
enum Context: string
{
    case PUBLIC = 'public';
    case ADMIN = 'admin';
}
