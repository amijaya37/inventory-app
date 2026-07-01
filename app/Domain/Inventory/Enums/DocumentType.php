<?php

namespace App\Domain\Inventory\Enums;

enum DocumentType: string
{
    case Invoice = 'invoice';
    case PO = 'po';
    case BeritaAcara = 'berita_acara';
    case Foto = 'foto';
    case Other = 'other';

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
