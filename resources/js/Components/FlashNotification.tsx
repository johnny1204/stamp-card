import { useState, useEffect } from 'react'

export default function FlashNotification({ flash }) {
    const [showSuccess, setShowSuccess] = useState(false)
    const [showError, setShowError] = useState(false)

    useEffect(() => {
        if (flash?.success) {
            setShowSuccess(true)
            const timer = setTimeout(() => {
                setShowSuccess(false)
            }, 5000)
            return () => clearTimeout(timer)
        }
    }, [flash?.success])

    useEffect(() => {
        if (flash?.error) {
            setShowError(true)
            const timer = setTimeout(() => {
                setShowError(false)
            }, 5000)
            return () => clearTimeout(timer)
        }
    }, [flash?.error])

    return (
        <>
            {showSuccess && flash.success && (
                <div className="fixed top-4 right-4 z-50 max-w-sm">
                    <div className="bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center justify-between">
                        <div className="flex items-center">
                            <span className="text-lg mr-2">✅</span>
                            <span>{flash.success}</span>
                        </div>
                        <button
                            onClick={() => setShowSuccess(false)}
                            className="ml-4 text-white hover:text-gray-200"
                        >
                            ✕
                        </button>
                    </div>
                </div>
            )}

            {showError && flash.error && (
                <div className="fixed top-4 right-4 z-50 max-w-sm">
                    <div className="bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center justify-between">
                        <div className="flex items-center">
                            <span className="text-lg mr-2">❌</span>
                            <span>{flash.error}</span>
                        </div>
                        <button
                            onClick={() => setShowError(false)}
                            className="ml-4 text-white hover:text-gray-200"
                        >
                            ✕
                        </button>
                    </div>
                </div>
            )}
        </>
    )
}