<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TrainingStoreRequest extends FormRequest {
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array {
    return [
      'name' => [
        'required',
        'string',
        'max:255',
        Rule::unique('trainings')->where(function ($query) {
          return $query->where('user_id', $this->user()->id);
        }),
      ],
      'description' => ['nullable', 'string', 'max:1000'],
      'notes' => ['nullable', 'string', 'max:2000'],
    ];
  }

  /**
   * Get custom attributes for validator errors.
   *
   * @return array<string, string>
   */
  public function attributes(): array {
    return [
      'name' => 'training name',
    ];
  }
}
