<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * スタンプ作成リクエストのバリデーション
 */
class CreateStampRequest extends FormRequest
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
            'stamp_type_id' => ['required', 'integer', 'exists:stamp_types,id'],
            'comment' => ['nullable', 'string', 'max:500'],
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
            'stamp_type_id.required' => 'スタンプの種類を選択してください。',
            'stamp_type_id.integer' => 'スタンプの種類が正しくありません。',
            'stamp_type_id.exists' => '選択されたスタンプの種類は存在しません。',
            'comment.string' => 'コメントは文字列で入力してください。',
            'comment.max' => 'コメントは500文字以内で入力してください。',
        ];
    }
}