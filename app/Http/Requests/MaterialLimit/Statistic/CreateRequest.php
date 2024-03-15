<?php

namespace App\Http\Requests\MaterialLimit\Statistic;

use App\Http\Requests\SuzRequest\CompleteRequest;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class CreateRequest extends FormRequest
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
            'request_id' => 'required|int|exists:suz_requests,id',
            'materials' => 'required|array',
            'installer_id' => 'required|int|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'materials' => 'требуется список статистики на материалы',
            'request_id.required' => 'требуется ID заявки',
            'installer_id.required' => 'требуется ID установщика',
            'qty.required' => 'поле qty необходимо',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        //write your business logic here otherwise it will give same old JSON response
        $isApiRequest = $this->is('api/*'); // Check if the request is from the API

        if ($isApiRequest) {
            throw new HttpResponseException(response()->json($validator->errors(), 422));
        } else {
            return redirect()->back()->withErrors($validator->errors())->withInput();
        }
    }

    public static function createFrom(Request $from, $to = null)
    {
        $instance = parent::createFrom($from);
        $instance->request->add($to);

        return $instance;
    }

    public function setContainer(Container $container): CreateRequest
    {
        parent::setContainer($container);
        parent::getValidatorInstance();

        return $this;
    }

}
