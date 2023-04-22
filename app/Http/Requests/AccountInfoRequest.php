<?php

namespace Bo\Base\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccountInfoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // only allow updates if the user is logged in
        return bo_auth()->check();
    }

    /**
     * Restrict the fields that the user can change.
     *
     * @return array
     */
    public function validationData()
    {
        return $this->only(bo_authentication_column(), 'name');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $user = bo_auth()->user();

        return [
            bo_authentication_column() => [
                'required',
                bo_authentication_column() == 'email' ? 'email' : '',
                Rule::unique($user->getConnectionName().'.'.$user->getTable())
                    ->ignore($user->getKey(), $user->getKeyName()),
            ],
            'name' => 'required',
        ];
    }
}
