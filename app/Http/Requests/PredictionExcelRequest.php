<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PredictionExcelRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'harga_excel' => 'required|file|mimes:xlsx,xls,csv|max:1024',
        ];
    }

    /**
     * Return JSON response on validation failure (for AJAX)
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $response = response()->json([
            'success' => false,
            'error' => $validator->errors()->first()
        ], 422);
        throw new \Illuminate\Validation\ValidationException($validator, $response);
    }
}