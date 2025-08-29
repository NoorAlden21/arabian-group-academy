<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateQuizRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'subject_id' => ['required','exists:subjects,id'],
            'description' => 'nullable|string',
            'started_at' => 'nullable|date',
            'deadline' => 'nullable|date|after_or_equal:started_at',

            'questions' => 'required|array|min:1',

            // allow either text or image (we enforce XOR in withValidator)
            'questions.*.question_text'  => ['nullable','string'],
            'questions.*.question_image' => ['nullable','image','max:2048'],

            'questions.*.choices' => 'required|array|min:2',

            // allow either text or image (we enforce XOR in withValidator)
            'questions.*.choices.*.choice_text'  => ['nullable','string'],
            'questions.*.choices.*.choice_image' => ['nullable','image','max:2048'],

            'questions.*.choices.*.is_correct' => ['required','boolean'],
        ];
    }

     public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $questions = $this->input('questions', []);

            foreach ($questions as $qi => $q) {
                // ---- Question XOR check ----
                $hasText  = isset($q['question_text']) && trim((string)$q['question_text']) !== '';
                $hasImage = $this->hasFile("questions.$qi.question_image");

                if (!$hasText && !$hasImage) {
                    $validator->errors()->add("questions.$qi", 'Provide either question_text or question_image.');
                }
                if ($hasText && $hasImage) {
                    $validator->errors()->add("questions.$qi", 'Provide only one of question_text or question_image, not both.');
                }

                // ---- Choices XOR check ----
                foreach (($q['choices'] ?? []) as $ci => $c) {
                    $cHasText  = isset($c['choice_text']) && trim((string)$c['choice_text']) !== '';
                    $cHasImage = $this->hasFile("questions.$qi.choices.$ci.choice_image");

                    if (!$cHasText && !$cHasImage) {
                        $validator->errors()->add("questions.$qi.choices.$ci", 'Provide either choice_text or choice_image.');
                    }
                    if ($cHasText && $cHasImage) {
                        $validator->errors()->add("questions.$qi.choices.$ci", 'Provide only one of choice_text or choice_image, not both.');
                    }
                }
            }
        });
    }
}
