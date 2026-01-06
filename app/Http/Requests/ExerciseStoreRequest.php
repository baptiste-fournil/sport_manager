<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExerciseStoreRequest extends FormRequest {
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
        Rule::unique('exercises')->where(function ($query) {
          return $query->where('user_id', $this->user()->id);
        }),
      ],
      'description' => ['nullable', 'string', 'max:1000'],
      'type' => ['required', 'string', 'in:strength,cardio,flexibility,other'],
      'muscle_group' => ['nullable', 'string', 'max:255'],
    ];
  }

  /**
   * Get custom attribute names for validator errors.
   *
   * @return array<string, string>
   */
  public function attributes(): array {
    return [
      'name' => 'exercise name',
      'type' => 'exercise type',
      'muscle_group' => 'muscle group',
    ];
  }
}
