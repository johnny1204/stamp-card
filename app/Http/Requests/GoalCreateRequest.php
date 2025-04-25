<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GoalCreateRequest extends FormRequest
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
            'stamp_type_id' => 'required|integer|exists:mysql.stamp_types,id',
            'target_count' => 'required|integer|min:1|max:100',
            'period_type' => 'required|string|in:weekly,monthly',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
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
            'stamp_type_id.required' => 'スタンプタイプを選択してください。',
            'stamp_type_id.exists' => '選択されたスタンプタイプが見つかりません。',
            'target_count.required' => '目標回数を入力してください。',
            'target_count.min' => '目標回数は1以上で入力してください。',
            'target_count.max' => '目標回数は100以下で入力してください。',
            'period_type.required' => '期間タイプを選択してください。',
            'period_type.in' => '期間タイプは「週間」または「月間」を選択してください。',
            'start_date.required' => '開始日を入力してください。',
            'start_date.after_or_equal' => '開始日は今日以降の日付を選択してください。',
            'end_date.required' => '終了日を入力してください。',
            'end_date.after' => '終了日は開始日より後の日付を選択してください。',
            'reward_text.max' => '報酬メッセージは500文字以内で入力してください。',
        ];
    }
}
