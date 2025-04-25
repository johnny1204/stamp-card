<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * スタンプ作成リクエストのバリデーション
 */
class StoreStampRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // 認証機能実装後に適切な認可ロジックに変更
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'child_id' => ['required', 'integer', 'exists:children,id'],
            'stamp_type_id' => ['required', 'integer'],
            'comment' => ['nullable', 'string', 'max:500'],
            'stamped_at' => ['nullable', 'date'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'child_id.required' => '子どもの選択は必須です。',
            'child_id.integer' => '子どもIDは数値で指定してください。',
            'child_id.exists' => '指定された子どもが見つかりません。',
            'stamp_type_id.required' => 'スタンプ種類の選択は必須です。',
            'stamp_type_id.integer' => 'スタンプ種類IDは数値で指定してください。',
            'comment.string' => 'コメントは文字列で入力してください。',
            'comment.max' => 'コメントは500文字以内で入力してください。',
            'stamped_at.date' => 'スタンプ日時は有効な日付で入力してください。',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        if (!$this->has('stamped_at')) {
            $this->merge([
                'stamped_at' => now(),
            ]);
        }
    }
}
