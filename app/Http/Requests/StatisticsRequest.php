<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 統計リクエストのバリデーション
 */
class StatisticsRequest extends FormRequest
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
            'period' => ['sometimes', 'string', 'in:daily,weekly,monthly'],
            'limit' => ['sometimes', 'integer', 'between:1,365'],
            'days' => ['sometimes', 'integer', 'between:1,365'],
            'year' => ['sometimes', 'integer', 'between:2020,2030'],
            'month' => ['sometimes', 'integer', 'between:1,12'],
            'start_date' => ['sometimes', 'date_format:Y-m-d'],
            'end_date' => ['sometimes', 'date_format:Y-m-d', 'after_or_equal:start_date'],
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
            'period.in' => '期間は daily, weekly, monthly のいずれかを指定してください。',
            'limit.integer' => '制限数は整数で入力してください。',
            'limit.between' => '制限数は1から365の間で入力してください。',
            'days.integer' => '日数は整数で入力してください。',
            'days.between' => '日数は1から365の間で入力してください。',
            'year.integer' => '年は整数で入力してください。',
            'year.between' => '年は2020年から2030年の間で入力してください。',
            'month.integer' => '月は整数で入力してください。',
            'month.between' => '月は1から12の間で入力してください。',
            'start_date.date_format' => '開始日はY-m-d形式で入力してください。',
            'end_date.date_format' => '終了日はY-m-d形式で入力してください。',
            'end_date.after_or_equal' => '終了日は開始日以降の日付を入力してください。',
        ];
    }
}