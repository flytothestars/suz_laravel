<?php

namespace App\Http\Requests\SuzRequest;

use Exception;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Container\Container;

class ReturnRequest extends FormRequest
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
            "request_id" => "required|int|exists:suz_requests,id",
            "comment" => "required|string",
            'limit_input' => 'nullable|string',
            'materials' => 'nullable|array'
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

        Log::error('Validation failed', $validator->errors()->all());
        throw new Exception('Validation failed',422);

    }

    public static function createFrom(Request $from, $to = null)
    {
        $instance = parent::createFrom($from);
        $instance->request->add($to);

        return $instance;
    }

    public function setContainer(Container $container): ReturnRequest
    {
        parent::setContainer($container);
        parent::getValidatorInstance();

        return $this;
    }
}
