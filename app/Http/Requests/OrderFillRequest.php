<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderFillRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'                => 'required|max:255',
            'email'               => 'required|email|max:255',
            'phone'               => 'required',
            'comment'             => 'string|nullable',
            'pay_system'          => 'required|in:card,invitation,cash,forum,invitation_hide,kaspi',
            'pay_system_imitated' => 'nullable|in:card,installment,kaspi',
            'show_to_organizer'   => 'boolean|nullable',
            'company'             => 'string|nullable|max:255',
            'position'            => 'string|nullable|max:255',
            'country'             => 'string|nullable|max:255',
            'participation'       => 'string|nullable|in:visitor,speaker,exhibitor',
            'source'              => 'string|nullable|max:255',
        ];
    }
}
