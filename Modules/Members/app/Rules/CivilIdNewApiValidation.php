<?php

namespace Modules\Members\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class CivilIdNewApiValidation implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            if(!env('NEW_PORTAL_SYNC')){
                return;
            }
            $api = env('NEW_PORTAL_API').'migration/check/civil_id/';
            // Replace with your actual remote API location and parameters
            $response = Http::timeout(3)->get($api, [
                'civil_id' => $value,
            ]);
            $data = $response['data'];
            // Assuming the remote API returns a JSON field like {"is_valid": true}
            if ($response->failed() || $data['civilIdExists']) {
                $fail('The :attribute is not recognized or active at the new portal.');
            }
        } catch (\Exception $e) {
            // Fail-open strategy: if remote server is down, let it pass and verify later
            // Remove the return statement if you prefer strict fail-closed security
            return; 
        }
    }
}
