<?php

namespace App\Services\Business;

use App\Models\Business\BusinessCurrentAccount;
use App\Models\Business\BusinessCurrentAccountTransaction;
use App\Models\Business\BusinessEntity;
use App\Models\Business\BusinessSavingsAccount;
use App\Models\Business\BusinessUser;
use Illuminate\Support\Facades\DB;

class BusinessEntityService
{
    /**
     * Create a new business entity with its owner business user in one atomic transaction.
     */
    const FRAIS_OUVERTURE_GDS = 1000;

    public function create(array $data, int $createdBy): BusinessEntity
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $entity = BusinessEntity::create(array_merge($data, [
                'created_by' => $createdBy,
                'status_kyc' => 'pending',
                'status'     => 'active',
            ]));

            BusinessUser::create([
                'business_id'        => $entity->id,
                'client_id'          => $entity->owner_client_id,
                'role'               => 'owner',
                'can_approve_payroll' => true,
                'can_request_credit'  => true,
                'is_active'           => true,
            ]);

            // Ouvrir KCB avec frais d'ouverture de 1000 GDS (collectés en espèces)
            $kcb = BusinessCurrentAccount::create([
                'business_id' => $entity->id,
                'balance'     => 0,
                'status'      => 'actif',
                'created_by'  => $createdBy,
            ]);

            BusinessCurrentAccountTransaction::create([
                'business_current_account_id' => $kcb->id,
                'account_number'              => $kcb->account_number,
                'business_id'                 => $entity->id,
                'type'                        => BusinessCurrentAccountTransaction::TYPE_FRAIS_OUVERTURE,
                'amount'                      => self::FRAIS_OUVERTURE_GDS,
                'balance_after'               => 0,
                'note'                        => 'Frais d\'ouverture business — collectés en espèces',
                'created_by'                  => $createdBy,
            ]);

            // Ouvrir KEB (pas de frais séparé — inclus dans les 1000 GDS globaux)
            BusinessSavingsAccount::create([
                'business_id' => $entity->id,
                'balance'     => 0,
                'status'      => 'actif',
                'created_by'  => $createdBy,
            ]);

            return $entity;
        });
    }

    /**
     * Mark KYC as verified and record the verifier.
     */
    public function verifyKyc(BusinessEntity $entity, int $verifiedBy): BusinessEntity
    {
        $entity->update([
            'status_kyc'      => 'verified',
            'kyc_verified_at' => now(),
            'kyc_verified_by' => $verifiedBy,
        ]);

        return $entity->fresh();
    }

    /**
     * Open a KCB (current account) for the business. Only one allowed per business.
     */
    public function openCurrentAccount(BusinessEntity $entity, int $createdBy): BusinessCurrentAccount
    {
        if ($entity->currentAccount()->exists()) {
            throw new \RuntimeException('Ce business possède déjà un compte courant (KCB).');
        }

        return DB::transaction(function () use ($entity, $createdBy) {
            $account = BusinessCurrentAccount::create([
                'business_id' => $entity->id,
                'balance'     => 0,
                'status'      => 'actif',
                'created_by'  => $createdBy,
            ]);

            // Record account opening fee transaction (zero amount opening)
            BusinessCurrentAccountTransaction::create([
                'business_current_account_id' => $account->id,
                'account_number'              => $account->account_number,
                'business_id'                 => $entity->id,
                'type'                        => BusinessCurrentAccountTransaction::TYPE_FRAIS_OUVERTURE,
                'amount'                      => 0,
                'balance_after'               => 0,
                'note'                        => "Ouverture du compte courant {$account->account_number}",
                'created_by'                  => $createdBy,
            ]);

            return $account;
        });
    }

    /**
     * Open a KEB (savings account) for the business. Only one allowed per business.
     */
    public function openSavingsAccount(BusinessEntity $entity, int $createdBy): BusinessSavingsAccount
    {
        if ($entity->savingsAccount()->exists()) {
            throw new \RuntimeException("Ce business possède déjà un compte épargne (KEB).");
        }

        return BusinessSavingsAccount::create([
            'business_id' => $entity->id,
            'balance'     => 0,
            'status'      => 'actif',
            'created_by'  => $createdBy,
        ]);
    }

    /**
     * Update business status (active/suspended).
     * Owner cannot be deactivated — only business-level status is changed here.
     */
    public function updateStatus(BusinessEntity $entity, string $status): BusinessEntity
    {
        $entity->update(['status' => $status]);
        return $entity->fresh();
    }
}
