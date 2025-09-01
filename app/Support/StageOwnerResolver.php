<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;
use App\Models\Company;
use App\Models\Institution;
use App\Models\Peso;

class StageOwnerResolver
{
    public function resolve(): array
    {
        $user = Auth::user();
        if (!$user) {
            return ['type'=>null,'id'=>null,'role'=>'guest'];
        }

        // Try to detect owning entity explicitly by existing linked records
        $companyId = Company::where('user_id', $user->id)->value('id');
        if ($companyId) {
            return ['type' => 'company', 'id' => $companyId, 'role'=>'company'];
        }

        $institutionId = Institution::where('user_id', $user->id)->value('id');
        if ($institutionId) {
            return ['type' => 'institution', 'id' => $institutionId, 'role'=>'institution'];
        }

        $pesoId = Peso::where('user_id', $user->id)->value('id');
        if ($pesoId) {
            // Treat PESO as global owner (null) — they use defaults
            return ['type' => null, 'id' => null, 'role'=>'peso'];
        }

        // Fallback to global defaults
        return ['type'=>null,'id'=>null,'role'=>'default'];
    }
}