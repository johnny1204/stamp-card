<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChildController;
use App\Http\Controllers\ChildViewController;
use App\Http\Controllers\StampController;
use App\Http\Controllers\StampCardController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\PokemonMediaController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StampTypeMasterController;

// 認証ルート（認証不要）
Route::prefix('auth')->name('auth.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/set-password', [AuthController::class, 'showSetPassword'])->name('set-password');
    Route::post('/set-password', [AuthController::class, 'setPassword']);
});

// ポケモンメディアAPI（認証不要）
Route::prefix('api/pokemon')->name('api.pokemon.')->group(function () {
    Route::get('{pokemonId}/image', [PokemonMediaController::class, 'getImage'])->name('image');
    Route::get('{pokemonId}/cry', [PokemonMediaController::class, 'getCry'])->name('cry');
    Route::get('cache/stats', [PokemonMediaController::class, 'getCacheStats'])->name('cache.stats');
    Route::post('cache/clean', [PokemonMediaController::class, 'cleanCache'])->name('cache.clean');
});

// 子ども用署名付きURLルート（認証不要、署名必須）
Route::prefix('child/{child}')->name('child.')->group(function () {
    Route::middleware('signed')->group(function () {
        Route::get('stamp-cards', [ChildViewController::class, 'stampCards'])->name('stamp-cards');
        Route::get('stamps', [ChildViewController::class, 'stamps'])->name('stamps');
        Route::get('today-stamps', [ChildViewController::class, 'todayStamps'])->name('today-stamps');
    });
    Route::post('stamps/{stamp}/open', [ChildViewController::class, 'openStamp'])->name('stamps.open');
});

// 親用保護されたルート（認証必須）
Route::middleware('admin.auth')->group(function () {
    // ホーム画面
    Route::get('/', [HomeController::class, 'index'])->name('home');
    
    // スマートリダイレクトルート
    Route::get('/go/stamp-cards', [HomeController::class, 'redirectToStampCards'])->name('go.stamp-cards');
    Route::get('/go/today-stamps', [HomeController::class, 'redirectToTodayStamps'])->name('go.today-stamps');
    Route::get('/go/calendar', [HomeController::class, 'redirectToCalendar'])->name('go.calendar');
    Route::get('/go/statistics', [HomeController::class, 'redirectToStatistics'])->name('go.statistics');
    Route::get('/go/goals', [HomeController::class, 'redirectToGoals'])->name('go.goals');
    Route::get('/go/reports', [HomeController::class, 'redirectToReports'])->name('go.reports');

    // 子ども管理ルート
    Route::resource('children', ChildController::class);
    Route::get('children/{child}/qr-code', [ChildController::class, 'qrCode'])->name('children.qr-code');
    Route::get('children/{child}/qr-code/{type}', [ChildController::class, 'singleQrCode'])->name('children.qr-code.single');
    Route::get('children/{child}/qr-code/{type}/data', [ChildController::class, 'getQrCodeData'])->name('children.qr-code.data');
    Route::get('children/{child}/qr-code/{type}/svg', [ChildController::class, 'getQrCodeSvg'])->name('children.qr-code.svg');

    // スタンプ管理ルート
    Route::prefix('children/{child}')->name('children.')->group(function () {
        Route::get('stamps', [StampController::class, 'index'])->name('stamps.index');
        Route::get('stamps/create', [StampController::class, 'create'])->name('stamps.create');
        Route::post('stamps', [StampController::class, 'store'])->name('stamps.store');
        Route::get('stamps/{stamp}', [StampController::class, 'show'])->name('stamps.show');
        
        // スタンプカードルート
        Route::get('stamp-cards', [StampCardController::class, 'index'])->name('stamp-cards.index');
        Route::patch('stamp-cards/target', [StampCardController::class, 'updateTarget'])->name('stamp-cards.update-target');
        
        // カレンダールート
        Route::prefix('calendar')->name('calendar.')->group(function () {
            Route::get('monthly', [CalendarController::class, 'monthly'])->name('monthly');
            Route::get('weekly', [CalendarController::class, 'weekly'])->name('weekly');
            Route::get('daily', [CalendarController::class, 'daily'])->name('daily');
            
            // カレンダーAPI
            Route::prefix('api')->name('api.')->group(function () {
                Route::get('monthly', [CalendarController::class, 'apiMonthly'])->name('monthly');
                Route::get('weekly', [CalendarController::class, 'apiWeekly'])->name('weekly');
                Route::get('daily', [CalendarController::class, 'apiDaily'])->name('daily');
            });
        });
        
        // 統計ルート
        Route::prefix('statistics')->name('statistics.')->group(function () {
            Route::get('dashboard', [StatisticsController::class, 'dashboard'])->name('dashboard');
            Route::get('detailed', [StatisticsController::class, 'detailed'])->name('detailed');
            Route::get('monthly-report', [StatisticsController::class, 'monthlyReport'])->name('monthly-report');
            
            // 統計API
            Route::prefix('api')->name('api.')->group(function () {
                Route::get('basic', [StatisticsController::class, 'apiBasicStatistics'])->name('basic');
                Route::get('period', [StatisticsController::class, 'apiPeriodStatistics'])->name('period');
                Route::get('stamp-types', [StatisticsController::class, 'apiStampTypeStatistics'])->name('stamp-types');
                Route::get('pokemon', [StatisticsController::class, 'apiPokemonStatistics'])->name('pokemon');
                Route::get('growth-chart', [StatisticsController::class, 'apiGrowthChartData'])->name('growth-chart');
                Route::get('monthly-report', [StatisticsController::class, 'apiMonthlyReport'])->name('monthly-report');
            });
        });
        
        // 目標管理ルート
        Route::prefix('goals')->name('goals.')->group(function () {
            Route::get('/', [GoalController::class, 'index'])->name('index');
            Route::get('create', [GoalController::class, 'create'])->name('create');
            Route::post('/', [GoalController::class, 'store'])->name('store');
            Route::get('{goal}', [GoalController::class, 'show'])->name('show');
            Route::get('{goal}/edit', [GoalController::class, 'edit'])->name('edit');
            Route::patch('{goal}', [GoalController::class, 'update'])->name('update');
            Route::delete('{goal}', [GoalController::class, 'destroy'])->name('destroy');
            
            // 目標作成API
            Route::post('weekly', [GoalController::class, 'createWeeklyGoal'])->name('weekly');
            Route::post('monthly', [GoalController::class, 'createMonthlyGoal'])->name('monthly');
            Route::post('check-achievements', [GoalController::class, 'checkAchievements'])->name('check-achievements');
        });
        
        // レポートルート
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('dashboard', [ReportController::class, 'dashboard'])->name('dashboard');
            Route::get('monthly', [ReportController::class, 'monthly'])->name('monthly');
            Route::get('export/pdf', [ReportController::class, 'exportPdf'])->name('export.pdf');
            
            // レポートAPI
            Route::prefix('api')->name('api.')->group(function () {
                Route::get('data', [ReportController::class, 'getData'])->name('data');
                Route::get('activity-trend', [ReportController::class, 'getActivityTrend'])->name('activity-trend');
                Route::get('stamps-by-type', [ReportController::class, 'getStampsByType'])->name('stamps-by-type');
            });
        });
    });
    
    // マスタ管理ルート
    Route::prefix('master')->name('master.')->group(function () {
        // スタンプ種類マスタ管理
        Route::prefix('stamp-types')->name('stamp-types.')->group(function () {
            Route::get('/', [StampTypeMasterController::class, 'index'])->name('index');
            Route::get('create', [StampTypeMasterController::class, 'create'])->name('create');
            Route::post('/', [StampTypeMasterController::class, 'store'])->name('store');
            Route::get('{id}/edit', [StampTypeMasterController::class, 'edit'])->name('edit');
            Route::patch('{id}', [StampTypeMasterController::class, 'update'])->name('update');
            Route::delete('{id}', [StampTypeMasterController::class, 'destroy'])->name('destroy');
        });
    });
});
