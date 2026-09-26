<?php

declare(strict_types=1);

/**
 * Merepresentasikan satu transaksi keuangan (deposit / penarikan).
 * Properti dienkapsulasi sebagai private dan hanya dapat dibaca melalui getter.
 */
class Transaction
{
    public function __construct(
        private readonly string $id,
        private readonly string $type,
        private readonly float $amount
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * Memproses transaksi terhadap saldo yang diberikan (by reference).
     * Mengembalikan true jika berhasil, false jika gagal (mis. saldo tidak cukup).
     */
    public function process(float &$balance): bool
    {
        return match ($this->type) {
            'deposit'  => $this->processDeposit($balance),
            'withdraw' => $this->processWithdraw($balance),
            default    => false,
        };
    }

    private function processDeposit(float &$balance): bool
    {
        $balance += $this->amount;
        return true;
    }

    private function processWithdraw(float &$balance): bool
    {
        if ($this->amount > $balance) {
            return false;
        }

        $balance -= $this->amount;
        return true;
    }

    /**
     * Representasi array untuk disimpan di sesi / ditampilkan di riwayat.
     */
    public function toArray(): array
    {
        return [
            'id'     => $this->id,
            'type'   => $this->type,
            'amount' => $this->amount,
        ];
    } 
}