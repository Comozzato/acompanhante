<?php

declare(strict_types=1);

namespace App\Modules\Pagamentos;

enum TipoPagamento: string
{
    case CREDITO = 'cred';
    case DEBITO = 'deb';
    case PIX = 'pix';


    public function forSelect(): array
    {
        return match ($this) {
            self::CREDITO => ['pagamento no crédito'],
            self::DEBITO  => ['pagamento no débito'],
            self::PIX     => ['pagamento via PIX'],
        };
    }
}
