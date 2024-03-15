<?php

namespace App\Http\Requests\Inventory\Stock;

use App\Models\KitRequest;
use App\Models\MaterialRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StockAcceptRejectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'id' => 'required|array',
            'id.*' => [
                'required',
                function ($attribute, $value, $fail) {
                    $existsInKitRequest = KitRequest::find($value);
                    $existsInMaterialsRequest = MaterialRequest::find($value);

                    if (!$existsInKitRequest && !$existsInMaterialsRequest) {
                        $fail("The selected $attribute is invalid.");
                    }
                },
            ],
        ];
    }

    /**
     * @throws HttpResponseException
     */
    public function failedValidation(Validator $validator)
    {
        $isApiRequest = $this->is('api/*'); // Check if the request is from the API

        if ($isApiRequest) {
            throw new HttpResponseException(response()->json($validator->errors(), 422));
        } else {
            return redirect()->back()->withErrors($validator->errors())->withInput();
        }
    }
}
