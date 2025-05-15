import React, { useState } from 'react';
import { useForm } from '@inertiajs/react';

interface CreateStampModalProps {
    isOpen: boolean;
    childId: number;
    childName: string;
    onClose: () => void;
    onSuccess: () => void;
}

const CreateStampModal: React.FC<CreateStampModalProps> = ({
    isOpen,
    childId,
    childName,
    onClose,
    onSuccess
}) => {
    const { data, setData, post, processing, errors, reset } = useForm({
        comment: '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        
        post(`/children/${childId}/stamps`, {
            onSuccess: () => {
                reset();
                onClose();
                onSuccess();
            },
            onError: (errors) => {
                console.error('スタンプ発行エラー:', errors);
                // エラーはInertia.jsが自動で処理
            }
        });
    };

    const handleClose = () => {
        if (!processing) {
            reset();
            onClose();
        }
    };

    if (!isOpen) {
        return null;
    }

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center">
            {/* バックドロップ */}
            <div 
                className="absolute inset-0 bg-black bg-opacity-50 backdrop-blur-sm"
                onClick={(e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('CreateStampModal backdrop clicked');
                    handleClose();
                }}
            />
            
            {/* モーダルコンテンツ */}
            <div className="relative max-w-md w-full mx-4 bg-white rounded-2xl shadow-2xl transform transition-all duration-300 scale-100">
                {/* ヘッダー */}
                <div className="bg-gradient-to-r from-blue-500 to-purple-600 p-6 rounded-t-2xl text-white text-center relative overflow-hidden">
                    <button
                        onClick={(e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            console.log('CreateStampModal close button clicked');
                            handleClose();
                        }}
                        disabled={processing}
                        className="absolute top-4 right-4 text-white hover:text-gray-200 transition-colors disabled:opacity-50 z-10"
                        type="button"
                    >
                        <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    
                    <div className="relative z-10">
                        <h2 className="text-2xl font-bold mb-2">🌟 新しいスタンプ</h2>
                        <p className="text-lg opacity-90">{childName}にスタンプを押そう！</p>
                    </div>
                </div>

                {/* フォーム */}
                <form onSubmit={handleSubmit} className="p-6">
                    <div className="mb-6">
                        <label className="block text-sm font-medium text-gray-700 mb-2">
                            コメント（任意）
                        </label>
                        <textarea
                            value={data.comment}
                            onChange={(e) => setData('comment', e.target.value)}
                            placeholder="頑張ったことやメッセージを入力してね！"
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                            rows={3}
                            maxLength={500}
                            disabled={processing}
                        />
                        <div className="text-xs text-gray-500 mt-1">
                            {data.comment.length}/500文字
                        </div>
                        {errors.comment && (
                            <div className="text-red-500 text-xs mt-1">{errors.comment}</div>
                        )}
                    </div>

                    {/* アクションボタン */}
                    <div className="flex space-x-3">
                        <button
                            type="submit"
                            disabled={processing}
                            className="flex-1 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white py-3 px-6 rounded-lg font-semibold transition-all transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none flex items-center justify-center"
                        >
                            {processing ? (
                                <>
                                    <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></div>
                                    スタンプ中...
                                </>
                            ) : (
                                <>
                                    <span className="mr-2">🎯</span>
                                    スタンプを押す！
                                </>
                            )}
                        </button>
                        
                        <button
                            type="button"
                            onClick={(e) => {
                                e.preventDefault();
                                e.stopPropagation();
                                console.log('CreateStampModal cancel button clicked');
                                handleClose();
                            }}
                            disabled={processing}
                            className="bg-gray-500 hover:bg-gray-600 text-white py-3 px-6 rounded-lg font-semibold transition-colors disabled:opacity-50"
                        >
                            キャンセル
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
};

export default CreateStampModal;