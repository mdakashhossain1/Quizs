<?php

namespace App\Services;

use App\Models\OfferwallTransaction;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Validation\ValidationException;

class OfferwallService
{
    public function recordCompletion(
        User $user,
        string $provider,
        string $transactionId,
        ?string $offerId = null,
        ?string $offerName = null,
        ?CarbonInterface $completedAt = null,
        array $payload = [],
    ): OfferwallTransaction {
        $transaction = OfferwallTransaction::firstOrCreate(
            ['provider' => $provider, 'transaction_key' => hash('sha256', $transactionId)],
            [
                'user_id' => $user->id,
                'transaction_id' => $transactionId,
                'offer_id' => $offerId,
                'offer_name' => $offerName,
                'status' => 'completed',
                'completed_at' => $completedAt ?? now(),
                'provider_payload' => $payload,
            ],
        );

        if ((int) $transaction->user_id !== (int) $user->id) {
            throw ValidationException::withMessages([
                'transaction_id' => 'This transaction is already assigned to a different user.',
            ]);
        }

        return $transaction;
    }
}
