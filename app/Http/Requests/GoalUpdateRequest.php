<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GoalUpdateRequest extends FormRequest
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
            'stamp_type_id' => 'sometimes|integer|exists:stamp_types,id',
            'target_count' => 'sometimes|integer|min:1|max:100',
            'period_type' => 'sometimes|string|in:weekly,monthly',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'reward_text' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'stamp_type_id.exists' => '選択されたスタンプタイプが見つかりません。',
            'target_count.min' => '目標回数は1以上で入力してください。',
            'target_count.max' => '目標回数は100以下で入力してください。',
            'period_type.in' => '期間タイプは「週間」または「月間」を選択してください。',
            'end_date.after' => '終了日は開始日より後の日付を選択してください。',
            'reward_text.max' => '報酬メッセージは500文字以内で入力してください。',
        ];
    }
}
