<?php

namespace App\Domain\Inventory\Enums;

enum AuditEvent: string
{
    case Create = 'create';
    case Update = 'update';
    case Delete = 'delete';
    case Post = 'post';
    case Upload = 'upload';
    case Download = 'download';
}
