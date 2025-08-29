<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuizRequest extends FormRequest
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
            // quiz meta – optional
            'title'       => 'sometimes|string|max:255',
            'subject_id'  => 'sometimes|exists:subjects,id',
            'description' => 'sometimes|nullable|string',
            'started_at'  => 'sometimes|nullable|date',
            'deadline'    => 'sometimes|nullable|date|after_or_equal:started_at',

            // questions – whole array is optional; only required if provided
            'questions' => 'sometimes|array|min:1',

            // if questions provided, each question may be text OR image (XOR enforced below)
            'questions.*.question_text'  => ['nullable','string'],
            'questions.*.question_image' => ['nullable','image','max:2048'],

            // choices – only required if questions is present
            'questions.*.choices' => 'required_with:questions|array|min:2',

            // choice text/image XOR (enforced below)
            'questions.*.choices.*.choice_text'  => ['nullable','string'],
            'questions.*.choices.*.choice_image' => ['nullable','image','max:2048'],

            // bools: accept true/false/1/0/on/off to play nice with form-data
            'questions.*.choices.*.is_correct' => ['required_with:questions','in:true,false,1,0,on,off'],
        ];
    }

    public function withValidator($validator): void
    {
        // Only run XOR checks if 'questions' is actually in the payload
        if (!$this->has('questions')) {
            return;
        }

        $validator->after(function ($validator) {
            $questions = $this->input('questions', []);

            foreach ($questions as $qi => $q) {
                $hasText  = isset($q['question_text']) && trim((string)$q['question_text']) !== '';
                $hasImage = $this->hasFile("questions.$qi.question_image");

                if (!$hasText && !$hasImage) {
                    $validator->errors()->add("questions.$qi", 'Provide either question_text or question_image.');
                }
                if ($hasText && $hasImage) {
                    $validator->errors()->add("questions.$qi", 'Provide only one of question_text or question_image, not both.');
                }

                // choices XOR + exactly one correct choice (optional but recommended)
                $correctCount = 0;
                foreach (($q['choices'] ?? []) as $ci => $c) {
                    $cHasText  = isset($c['choice_text']) && trim((string)$c['choice_text']) !== '';
                    $cHasImage = $this->hasFile("questions.$qi.choices.$ci.choice_image");

                    if (!$cHasText && !$cHasImage) {
                        $validator->errors()->add("questions.$qi.choices.$ci", 'Provide either choice_text or choice_image.');
                    }
                    if ($cHasText && $cHasImage) {
                        $validator->errors()->add("questions.$qi.choices.$ci", 'Provide only one of choice_text or choice_image, not both.');
                    }

                    if (in_array(($c['is_correct'] ?? null), [true, 'true', 1, '1', 'on'], true)) {
                        $correctCount++;
                    }
                }

                if ($correctCount !== 1) {
                    $validator->errors()->add("questions.$qi.choices", 'Each question must have exactly one correct choice.');
                }
            }
        });
    }
}
