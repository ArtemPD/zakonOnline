<?php

namespace App\Http\Requests;

use App\Exceptions\UnprocessableContentException;
use App\Rules\IntegerKeys;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class ConclusionRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'keywords' => ['required', 'array', new IntegerKeys()],
            'keywords.*.keyword' => ['required', 'string'],
            'keywords.*.success_rate' => ['required', 'integer', 'between:0,100'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'keywords' => [
                'description' => 'Array of keyword objects',
                'example' => [
                    ["keyword" => "Accident", "success_rate" => 80],
                ],
            ],
            'keywords.*.keyword' => [
                'description' => 'The keyword string',
                'example' => 'Accident',
            ],
            'keywords.*.success_rate' => [
                'description' => 'The success rate of the keyword as an integer between 0 and 100',
                'example' => 70,
            ],
        ];
    }

    /**
     * @param Validator $validator
     * @throws UnprocessableContentException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new UnprocessableContentException($validator->errors(), __('exceptions.get_conclusion.failed'));
    }
}
