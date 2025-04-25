<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * カレンダーリクエストのバリデーション
 */
class CalendarRequest extends FormRequest
{
    /**
     * リクエストの認可
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーションルール
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'year' => ['sometimes', 'integer', 'between:2020,2030'],
            'month' => ['sometimes', 'integer', 'between:1,12'],
            'date' => ['sometimes', 'date_format:Y-m-d'],
        ];
    }

    /**
     * バリデーションエラーメッセージ
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'year.integer' => '年は整数で入力してください。',
            'year.between' => '年は2020年から2030年の間で入力してください。',
            'month.integer' => '月は整数で入力してください。',
            'month.between' => '月は1から12の間で入力してください。',
            'date.date_format' => '日付はY-m-d形式で入力してください。',
        ];
    }
}