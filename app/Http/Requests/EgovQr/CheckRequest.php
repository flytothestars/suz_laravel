<?php

namespace App\Http\Requests\EgovQr;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CheckRequest extends FormRequest
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
            'suz_id' => 'required'
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
