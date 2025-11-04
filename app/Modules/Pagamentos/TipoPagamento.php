<?php

declare(strict_types=1);

namespace App\Modules\Pagamentos;

use App\Modules\Pagamentos\Strategies\Interfaces\Pagamento;
use App\Modules\Pagamentos\Strategies\Metodos\Pix;


enum TipoPagamento: string
{
    case CREDITO = 'cred';
    case DEBITO = 'deb';
    case PIX = 'pix';


    public function metodoDePagamento(): Pagamento
    {
        return match ($this) {
            self::PIX     => new Pix(),
            //self::CREDITO => new Credito(),
            //self::DEBITO  => new Debito(),
        };
    }

    public function forSelect(): array
    {
        return match ($this) {
            self::CREDITO => ['pagamento no crédito'],
            self::DEBITO  => ['pagamento no débito'],
            self::PIX     => ['pagamento via PIX'],
        };
    }
}
