<?php

namespace App\Console\Commands;

use App\Services\SchoolProgramService;
use Illuminate\Console\Command;

class ReleaseExpiredSchoolProgramBlocks extends Command
{
    protected $signature   = 'school:release-blocks';
    protected $description = 'Libérer les blocages de solde expirés des programmes scolaires';

    public function handle(SchoolProgramService $service): int
    {
        $count = $service->releaseExpiredBlocks();
        $this->info("Blocages libérés : {$count} compte(s) traité(s).");
        return self::SUCCESS;
    }
}
