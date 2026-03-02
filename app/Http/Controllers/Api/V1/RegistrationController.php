<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RegistrationController extends BaseApiController
{
    /**
     * Get registration charges.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function charges(Request $request): JsonResponse
    {
        $charges = config('registration.charges');
        $role = $request->query('role');

        if ($role) {
            if (isset($charges[$role])) {
                return $this->success('Registration charges for ' . $role . ' retrieved successfully.', $charges[$role]);
            }
            return $this->error('Invalid role specified.', 404);
        }

        return $this->success('All registration charges retrieved successfully.', $charges);
    }
}
