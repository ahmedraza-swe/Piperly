<?php

namespace App\Validator;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoginValidator
{
    public function validateRequest(Request $request)
    {
        return $request->validate(
            $this->getValidationRules(
                $request->all()
            ));
    }

    public function validate(array $fields)
    {
        return Validator::make($fields, $this->getValidationRules($fields));
    }

    private function getValidationRules(array $fields): array
    {
        $rules = [
            'email' => 'required|string',
        ];

        if (! config('app.otp_login_enabled')) {
            $rules['password'] = 'required|string';
        }

        return $rules;
    }
}
