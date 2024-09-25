<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserRequest extends BaseFormRequest
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
            'name' => "{$this->getRequiredRule()}|string|max:255",
            'email' => [
                $this->getRequiredRule(),
                'email',
                'max:255',
                $this->getUniqRule(),
            ],
            'password' => [
                $this->getRequiredRule(),
                'string',
                'min:8',
                'confirmed',
            ],
            'file' => 'nullable|image|max:10240',
        ];
    }

    private function getRequiredRule()
    {
        return $this->isMethod('PATCH') ? 'sometimes' : 'required';
    }

    private function getUniqRule()
    {
        $rule = Rule::unique(User::class);

        if ($this->isMethod('PATCH') && Auth::check()) {
            return $rule->ignore(Auth::user());
        }

        return $rule;
    }
}
