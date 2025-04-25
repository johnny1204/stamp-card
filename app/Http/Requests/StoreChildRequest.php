<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 子ども登録リクエストのバリデーション
 */
class StoreChildRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100'],
            'birth_date' => ['required', 'date', 'before:today', 'after:' . now()->subYears(20)->toDateString()],
            'target_stamps' => ['required', 'integer', 'min:1', 'max:50'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,jpg,png,heic,heif', 'max:2048'], // 2MB以下
            'avatar_path' => ['nullable', 'string', 'max:255'],
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
            'name.required' => '名前は必須です。',
            'name.string' => '名前は文字列で入力してください。',
            'name.max' => '名前は100文字以内で入力してください。',
            'birth_date.required' => '誕生日は必須です。',
            'birth_date.date' => '誕生日は正しい日付形式で入力してください。',
            'birth_date.before' => '誕生日は今日より前の日付で入力してください。',
            'birth_date.after' => '誕生日は20年前以降の日付で入力してください。',
            'target_stamps.required' => '目標スタンプ数は必須です。',
            'target_stamps.integer' => '目標スタンプ数は数値で入力してください。',
            'target_stamps.min' => '目標スタンプ数は1以上で入力してください。',
            'target_stamps.max' => '目標スタンプ数は50以下で入力してください。',
            'avatar.image' => 'アバターは画像ファイルを選択してください。',
            'avatar.mimes' => 'アバターはJPEG、JPG、PNG、HEIC形式のファイルを選択してください。',
            'avatar.max' => 'アバターファイルは2MB以下にしてください。',
            'avatar_path.string' => 'アバターパスは文字列で入力してください。',
            'avatar_path.max' => 'アバターパスは255文字以内で入力してください。',
        ];
    }
}
