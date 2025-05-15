import React, { useState } from 'react';
import { Head, useForm, Link } from '@inertiajs/react';

interface LoginData {
    password: string;
}

interface LoginProps {
    adminExists: boolean;
}

const Login: React.FC<LoginProps> = ({ adminExists }) => {
    const { data, setData, post, processing, errors } = useForm<LoginData>({
        password: '',
    });

    const [showPassword, setShowPassword] = useState(false);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/auth/login');
    };

    // 管理者が存在しない場合はパスワード設定画面へリダイレクト
    if (!adminExists) {
        return (
            <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
                <Head title="初期設定が必要です" />
                
                <div className="max-w-md w-full space-y-8 text-center">
                    <div className="mx-auto h-24 w-24 bg-orange-500 rounded-full flex items-center justify-center mb-6">
                        <span className="text-4xl">⚙️</span>
                    </div>
                    <h2 className="text-3xl font-bold text-gray-900 mb-4">
                        初期設定が必要です
                    </h2>
                    <p className="text-gray-600 mb-8">
                        スタンプ帳を使用するために、まず管理用パスワードを設定してください。
                    </p>
                    <Link
                        href="/auth/set-password"
                        className="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                    >
                        パスワードを設定する
                    </Link>
                </div>
            </div>
        );
    }

    return (
        <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            <Head title="ログイン" />
            
            <div className="max-w-md w-full space-y-8">
                <div className="text-center">
                    <div className="mx-auto h-24 w-24 bg-blue-500 rounded-full flex items-center justify-center mb-6">
                        <span className="text-4xl">🏠</span>
                    </div>
                    <h2 className="text-3xl font-bold text-gray-900 mb-2">
                        スタンプ帳 管理画面
                    </h2>
                    <p className="text-gray-600">
                        管理用パスワードを入力してください
                    </p>
                </div>

                <form className="mt-8 space-y-6" onSubmit={handleSubmit}>
                    <div>
                        <label htmlFor="password" className="block text-sm font-medium text-gray-700 mb-2">
                            パスワード
                        </label>
                        <div className="relative">
                            <input
                                id="password"
                                name="password"
                                type={showPassword ? 'text' : 'password'}
                                required
                                className="appearance-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                                placeholder="パスワードを入力"
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                            />
                            <button
                                type="button"
                                className="absolute inset-y-0 right-0 pr-3 flex items-center"
                                onClick={() => setShowPassword(!showPassword)}
                            >
                                {showPassword ? (
                                    <svg className="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029M6.343 6.343C7.659 5.476 9.278 5 11 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-1.563 3.029M6.343 6.343L19.657 19.657M6.343 6.343A10.05 10.05 0 004.182 9" />
                                    </svg>
                                ) : (
                                    <svg className="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                )}
                            </button>
                        </div>
                        {errors.password && (
                            <p className="mt-1 text-sm text-red-600">{errors.password}</p>
                        )}
                    </div>

                    <div>
                        <button
                            type="submit"
                            disabled={processing}
                            className="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            {processing ? (
                                <>
                                    <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    ログイン中...
                                </>
                            ) : (
                                'ログイン'
                            )}
                        </button>
                    </div>

                    <div className="text-center">
                        <p className="text-sm text-gray-600">
                            子どもがスタンプを見るだけの場合は、特別なリンクからアクセスできます。
                        </p>
                    </div>
                </form>
            </div>
        </div>
    );
};

export default Login;