<?php

namespace App\Http\Requests\SuzRequest;

use Exception;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CompleteRequest extends FormRequest
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
        //TODO Как будет время, нужно заняться правилами валидации, так не очень, но оставил на потом, т.к требует макс концентрации.
        return [
            'installers' => ['required', 'string'],
            'dt_birthday' => ['required', 'date'],
            'request_id' => ['required'],
            'dt_document_issue_date' => ['required', 'date'],
            'comment' => ['required', 'string'],
            'v_iin' => ['nullable', 'string'],
            'installer_take' => ['nullable'],
            'installer_give' => ['nullable'],
            'equipments_to_take' => ['nullable'],
            'kits_to_give' => ['nullable'],
            'kits_to_take' => ['nullable'],
            'b_unbind_cntr' => ['nullable'],
            'v_param_internet' => ['nullable'],
            'v_kits_transfer' => ['nullable'],
            'materials_take' => ['nullable'],
            'materials_qty_take' => ['nullable'],
            'materials_give' => ['nullable'],
            'materials_qty_give' => ['nullable'],
            'v_document_number' => ['nullable', 'string'],
            'v_document_series' => ['nullable'],
            'use_clients_equipment' => ['nullable'],
            'coordinates' => ['nullable'],
            'id_type' => ['nullable', 'numeric'],
            'limit_input' => 'nullable|string'
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

        Log::error($validator->errors()->getMessageBag(), ['data' => $validator->errors()]);
        throw new Exception('Validation failed', 422);

    }

    public static function createFrom(Request $from, $to = null)
    {
        $instance = parent::createFrom($from);
        $instance->request->add($to);

        return $instance;
    }

    public function setContainer(Container $container): CompleteRequest
    {
        parent::setContainer($container);
        parent::getValidatorInstance();

        return $this;
    }
}
