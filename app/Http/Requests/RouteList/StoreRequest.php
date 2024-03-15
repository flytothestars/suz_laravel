<?php

namespace App\Http\Requests\RouteList;

use Exception;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreRequest extends FormRequest
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
            'location_id' => 'required',
            'installer_1' => 'nullable',
            'installer_2' => 'nullable',
            'date' => 'required|date_format:Y-m-d',
            'specialization' => 'required|array',
            'specialization.*' => 'exists:specialization,id',
        ];
    }

    /**
     * @throws Exception
     */
    protected function failedValidation(Validator $validator)
    {
        //write your business logic here otherwise it will give same old JSON response
        $isApiRequest = $this->is('api/*'); // Check if the request is from the API

        if ($isApiRequest) {
            throw new HttpResponseException(response()->json($validator->errors(), 422));
        }

        throw new Exception('Validation failed',422);

    }
}
