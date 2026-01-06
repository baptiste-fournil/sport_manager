<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSessionSetRequest extends FormRequest {
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool {
    return true; // Authorization handled in controller
  }

  /**
   * Get the validation rules that apply to the request.
   */
  public function rules(): array {
    return [
      'reps' => ['nullable', 'integer', 'min:1', 'max:500'],
      'weight' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
      'duration_seconds' => ['nullable', 'integer', 'min:1', 'max:86400'], // max 24 hours
      'distance' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
      'notes' => ['nullable', 'string', 'max:1000'],
    ];
  }

  /**
   * Get custom messages for validator errors.
   */
  public function messages(): array {
    return [
      'reps.integer' => 'Reps must be a whole number.',
      'reps.min' => 'Reps must be at least 1.',
      'reps.max' => 'Reps cannot exceed 500.',
      'weight.numeric' => 'Weight must be a number.',
      'weight.min' => 'Weight cannot be negative.',
      'weight.max' => 'Weight cannot exceed 9999.99.',
      'duration_seconds.integer' => 'Duration must be in seconds.',
      'duration_seconds.min' => 'Duration must be at least 1 second.',
      'duration_seconds.max' => 'Duration cannot exceed 24 hours.',
      'distance.numeric' => 'Distance must be a number.',
      'distance.min' => 'Distance cannot be negative.',
      'distance.max' => 'Distance cannot exceed 9999.99.',
      'notes.max' => 'Notes cannot exceed 1000 characters.',
    ];
  }
}
