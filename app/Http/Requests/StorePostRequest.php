<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Purifier;

class StorePostRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $validator = Validator::make($request->all(), [
            'content' => ['required', 'string', 'max:1000'],
        ]);

        // Tisztítás a validáció előtt
        $cleanContent = Purifier::clean($this->content);

        // HTML címkék eltávolítása
        $this->merge([
            'content' => $cleanContent,
        ]);
    }

    public function rules()
    {
        return [
            'content' => ['required', 'string', 'max:1000'],
        ];
    }
}