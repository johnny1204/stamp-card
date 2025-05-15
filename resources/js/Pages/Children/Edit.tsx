import { Head, useForm, router } from '@inertiajs/react'
import { useMemo, useState, useRef, useCallback, useEffect } from 'react'
import ReactCrop, { Crop, PixelCrop } from 'react-image-crop'
import 'react-image-crop/dist/ReactCrop.css'
import heic2any from 'heic2any'
import AppLayout from '../../Layouts/AppLayout'

export default function Edit({ child }) {
    const childData = child.data
    const { data, setData, post, processing, errors } = useForm({
        name: childData.name,
        birth_date: childData.birth_date || '',
        target_stamps: childData.target_stamps || 10,
        avatar: null,
        _method: 'PUT'
    })
    
    const [previewUrl, setPreviewUrl] = useState(childData.avatar_path)
    const [originalImageUrl, setOriginalImageUrl] = useState('')
    const [crop, setCrop] = useState<Crop>({
        unit: '%',
        width: 100,
        height: 100,
        x: 0,
        y: 0
    })
    const [showCropper, setShowCropper] = useState(false)
    const [isConverting, setIsConverting] = useState(false)
    const imgRef = useRef<HTMLImageElement>(null)
    const fileInputRef = useRef<HTMLInputElement>(null)

    // 生年月日から年齢を計算
    const currentAge = useMemo(() => {
        if (!data.birth_date) return null;
        
        const birthDate = new Date(data.birth_date);
        const today = new Date();
        
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        
        return age >= 0 ? age : null;
    }, [data.birth_date])

    // プレビューURLのクリーンアップ
    useEffect(() => {
        return () => {
            if (previewUrl && previewUrl.startsWith('blob:')) {
                URL.revokeObjectURL(previewUrl)
            }
        }
    }, [previewUrl])

    const convertHeicToJpeg = useCallback(async (file: File): Promise<File> => {
        try {
            const convertedBlob = await heic2any({
                blob: file,
                toType: 'image/jpeg',
                quality: 0.9
            }) as Blob
            
            return new File([convertedBlob], file.name.replace(/\.(heic|heif)$/i, '.jpg'), {
                type: 'image/jpeg',
                lastModified: Date.now(),
            })
        } catch (error) {
            console.error('HEIC conversion error:', error)
            throw new Error('HEIC画像の変換に失敗しました')
        }
    }, [])

    const handleFileChange = async (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0]
        if (file) {
            try {
                let processedFile = file
                
                // ファイルタイプチェック
                const isHeic = /\.(heic|heif)$/i.test(file.name) || file.type === 'image/heic' || file.type === 'image/heif'
                const isImage = file.type.startsWith('image/') || isHeic
                
                if (!isImage) {
                    alert('画像ファイルを選択してください')
                    return
                }
                
                // HEICファイルの場合はJPEGに変換
                if (isHeic) {
                    setIsConverting(true)
                    console.log('Converting HEIC file to JPEG...')
                    processedFile = await convertHeicToJpeg(file)
                    console.log('HEIC conversion completed')
                    setIsConverting(false)
                }
                
                const reader = new FileReader()
                reader.onload = (e: ProgressEvent<FileReader>) => {
                    const imageUrl = e.target?.result as string
                    if (imageUrl) {
                        console.log('FileReader loaded image, length:', imageUrl.length)
                        setOriginalImageUrl(imageUrl)
                        setShowCropper(true)
                        // 初期クロップはonLoadで設定するため、ここでは設定しない
                    } else {
                        console.error('Failed to read file as data URL')
                        alert('画像の読み込みに失敗しました')
                    }
                }
                reader.onerror = (error) => {
                    console.error('FileReader error:', error)
                    alert('ファイルの読み込みでエラーが発生しました')
                }
                reader.readAsDataURL(processedFile)
            } catch (error) {
                console.error('File processing error:', error)
                alert(error instanceof Error ? error.message : 'ファイルの処理でエラーが発生しました')
                setIsConverting(false)
            }
        }
    }

    const getCroppedImg = useCallback(async (image: HTMLImageElement, crop: PixelCrop): Promise<Blob> => {
        const canvas = document.createElement('canvas')
        const ctx = canvas.getContext('2d')

        if (!ctx) {
            throw new Error('No 2d context')
        }

        // 出力サイズを固定（アバター用）
        const outputSize = 200
        canvas.width = outputSize
        canvas.height = outputSize

        // 元画像のスケールを計算
        const scaleX = image.naturalWidth / image.width
        const scaleY = image.naturalHeight / image.height

        // クロップ領域を元画像サイズに変換
        const sourceX = crop.x * scaleX
        const sourceY = crop.y * scaleY
        const sourceWidth = crop.width * scaleX
        const sourceHeight = crop.height * scaleY

        console.log('Crop details:', {
            crop,
            scaleX,
            scaleY,
            sourceX,
            sourceY,
            sourceWidth,
            sourceHeight,
            naturalSize: { width: image.naturalWidth, height: image.naturalHeight },
            displaySize: { width: image.width, height: image.height }
        })

        ctx.drawImage(
            image,
            sourceX,
            sourceY,
            sourceWidth,
            sourceHeight,
            0,
            0,
            outputSize,
            outputSize
        )

        return new Promise<Blob>((resolve, reject) => {
            canvas.toBlob((blob) => {
                if (blob) {
                    resolve(blob)
                } else {
                    reject(new Error('Canvas toBlob failed'))
                }
            }, 'image/jpeg', 0.9)
        })
    }, [])

    const handleCropComplete = useCallback(async () => {
        if (imgRef.current && crop.width && crop.height) {
            try {
                // パーセント値をピクセル値に変換
                const pixelCrop: PixelCrop = {
                    x: crop.unit === '%' ? (crop.x / 100) * imgRef.current.width : crop.x,
                    y: crop.unit === '%' ? (crop.y / 100) * imgRef.current.height : crop.y,
                    width: crop.unit === '%' ? (crop.width / 100) * imgRef.current.width : crop.width,
                    height: crop.unit === '%' ? (crop.height / 100) * imgRef.current.height : crop.height,
                    unit: 'px'
                }
                
                console.log('Converting crop to pixels:', { crop, pixelCrop, imageSize: { width: imgRef.current.width, height: imgRef.current.height } })
                
                const croppedBlob = await getCroppedImg(imgRef.current, pixelCrop)

                // BlobをFileオブジェクトに変換
                const croppedFile = new File([croppedBlob], 'avatar.jpg', {
                    type: 'image/jpeg',
                    lastModified: Date.now(),
                })

                setData('avatar', croppedFile)
                
                // プレビュー用のURLを作成
                const previewUrl = URL.createObjectURL(croppedBlob)
                setPreviewUrl(previewUrl)
                setShowCropper(false)
            } catch (error) {
                console.error('クロップエラー:', error)
            }
        }
    }, [crop, getCroppedImg, setData])

    const handleCropCancel = () => {
        setShowCropper(false)
        setOriginalImageUrl('')
        if (fileInputRef.current) {
            fileInputRef.current.value = ''
        }
    }

    const handleSubmit = (e) => {
        e.preventDefault()
        // ファイルアップロードがある場合はPOSTメソッドを使用（_methodでPUTを指定）
        post(`/children/${childData.id}`)
    }

    const handleDelete = () => {
        if (confirm(`${childData.name}を削除してもよろしいですか？この操作は取り消せません。`)) {
            router.delete(`/children/${childData.id}`)
        }
    }

    return (
        <AppLayout>
            <Head title={`${childData.name}の編集`} />
            
            <div className="container mx-auto px-4 py-8">
                <div className="max-w-md mx-auto">
                    <h1 className="text-3xl font-bold text-gray-800 mb-8 text-center">{childData.name}の編集</h1>
                    
                    <form onSubmit={handleSubmit} className="bg-white rounded-lg shadow-md p-6">
                        {/* アバター設定 */}
                        <div className="mb-6">
                            <label className="block text-sm font-medium text-gray-700 mb-3">
                                アバター画像
                            </label>
                            <div className="flex flex-col items-center space-y-4">
                                {/* プレビュー */}
                                <div className="relative">
                                    {previewUrl ? (
                                        <img
                                            src={previewUrl}
                                            alt="アバタープレビュー"
                                            className="w-24 h-24 rounded-full object-cover border-4 border-gray-200"
                                        />
                                    ) : (
                                        <div className="w-24 h-24 rounded-full bg-gray-200 flex items-center justify-center border-4 border-gray-200">
                                            <span className="text-gray-500 text-sm">画像なし</span>
                                        </div>
                                    )}
                                </div>
                                
                                {/* ファイル選択 */}
                                <div className="flex flex-col items-center space-y-2">
                                    <input
                                        type="file"
                                        id="avatar"
                                        ref={fileInputRef}
                                        accept="image/*,.heic,.heif"
                                        onChange={handleFileChange}
                                        disabled={isConverting}
                                        className="hidden"
                                    />
                                    <label
                                        htmlFor="avatar"
                                        className={`cursor-pointer px-4 py-2 rounded-lg text-sm transition-colors ${
                                            isConverting 
                                                ? 'bg-gray-400 cursor-not-allowed' 
                                                : 'bg-blue-500 hover:bg-blue-600'
                                        } text-white`}
                                    >
                                        {isConverting ? '🔄 変換中...' : '📷 画像を選択'}
                                    </label>
                                    <p className="text-xs text-gray-500 text-center">
                                        JPG、PNG、HEIC形式（最大2MB）<br />
                                        <span className="text-gray-400">※HEIC形式は自動でJPEGに変換されます</span>
                                    </p>
                                </div>
                            </div>
                            {errors.avatar && (
                                <p className="mt-2 text-sm text-red-500 text-center">{errors.avatar}</p>
                            )}
                        </div>

                        {/* 画像クロッピングモーダル */}
                        {showCropper && (
                            <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                                <div className="bg-white rounded-lg p-6 max-w-lg w-full mx-4">
                                    <h3 className="text-lg font-semibold mb-4">画像を切り取る</h3>
                                    <div className="mb-4">
                                        <ReactCrop
                                            crop={crop}
                                            onChange={(newCrop) => setCrop(newCrop)}
                                            aspect={1}
                                            circularCrop
                                        >
                                            <img
                                                ref={imgRef}
                                                src={originalImageUrl}
                                                alt="クロップ用画像"
                                                className="max-w-full h-auto"
                                                onLoad={(e) => {
                                                    const { naturalWidth, naturalHeight } = e.currentTarget
                                                    console.log('Image loaded:', { naturalWidth, naturalHeight })
                                                    
                                                    // 画像読み込み後にセンター配置のクロップを設定
                                                    setCrop({
                                                        unit: '%',
                                                        width: 60,
                                                        height: 60,
                                                        x: 20,
                                                        y: 20
                                                    })
                                                }}
                                            />
                                        </ReactCrop>
                                    </div>
                                    <div className="flex space-x-3">
                                        <button
                                            type="button"
                                            onClick={handleCropComplete}
                                            className="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg transition-colors"
                                        >
                                            切り取り完了
                                        </button>
                                        <button
                                            type="button"
                                            onClick={handleCropCancel}
                                            className="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg transition-colors"
                                        >
                                            キャンセル
                                        </button>
                                    </div>
                                </div>
                            </div>
                        )}

                        <div className="mb-6">
                            <label htmlFor="name" className="block text-sm font-medium text-gray-700 mb-2">
                                名前 <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="name"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                className={`w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                                    errors.name ? 'border-red-500' : 'border-gray-300'
                                }`}
                                placeholder="例: 太郎"
                            />
                            {errors.name && (
                                <p className="mt-1 text-sm text-red-500">{errors.name}</p>
                            )}
                        </div>

                        <div className="mb-6">
                            <label htmlFor="birth_date" className="block text-sm font-medium text-gray-700 mb-2">
                                誕生日 <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="date"
                                id="birth_date"
                                value={data.birth_date}
                                onChange={(e) => setData('birth_date', e.target.value)}
                                className={`w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                                    errors.birth_date ? 'border-red-500' : 'border-gray-300'
                                }`}
                                max={new Date().toISOString().split('T')[0]}
                                min={new Date(Date.now() - 20 * 365 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]}
                            />
                            <p className="mt-1 text-sm text-gray-500">
                                現在の年齢: {currentAge !== null ? `${currentAge}歳` : '未設定'}
                            </p>
                            {errors.birth_date && (
                                <p className="mt-1 text-sm text-red-500">{errors.birth_date}</p>
                            )}
                        </div>

                        <div className="mb-6">
                            <label htmlFor="target_stamps" className="block text-sm font-medium text-gray-700 mb-2">
                                目標スタンプ数 <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="number"
                                id="target_stamps"
                                min="1"
                                max="50"
                                value={data.target_stamps}
                                onChange={(e) => setData('target_stamps', parseInt(e.target.value))}
                                className={`w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                                    errors.target_stamps ? 'border-red-500' : 'border-gray-300'
                                }`}
                                placeholder="例: 10"
                            />
                            <p className="mt-1 text-sm text-gray-500">
                                スタンプカード1枚を完成させるのに必要なスタンプ数（1-50個）
                            </p>
                            {errors.target_stamps && (
                                <p className="mt-1 text-sm text-red-500">{errors.target_stamps}</p>
                            )}
                        </div>

                        <div className="flex space-x-4 mb-6">
                            <button
                                type="submit"
                                disabled={processing}
                                className="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg transition-colors disabled:opacity-50"
                            >
                                {processing ? '更新中...' : '更新する'}
                            </button>
                            <a
                                href={`/children/${childData.id}`}
                                className="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg text-center transition-colors"
                            >
                                キャンセル
                            </a>
                        </div>
                        
                        <div className="border-t pt-6">
                            <button
                                type="button"
                                onClick={handleDelete}
                                className="w-full bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-lg transition-colors"
                            >
                                削除する
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </AppLayout>
    )
}