<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\View::composer('layout.header', function ($view) {
            $notifications = [];

            // 1. Cek Green Bean Stock Menipis (< 10kg / 10000g)
            $lowGreenBeans = \App\Models\GreenBeanStock::selectRaw('green_bean_id, sum(stok_gram) as total_stok')
                ->groupBy('green_bean_id')
                ->havingRaw('total_stok < 10000') 
                ->with('greenBean')
                ->get();
            
            foreach ($lowGreenBeans as $stock) {
                if ($stock->greenBean) {
                    $notifications[] = [
                        'type' => 'stock_green',
                        'title' => 'Stok Green Bean Menipis',
                        'message' => 'Sisa stok ' . $stock->greenBean->nama_biji . ' tinggal ' . number_format($stock->total_stok / 1000, 2) . ' Kg.',
                        'time' => 'Sistem Peringatan Dini',
                        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>',
                        'color' => 'secondary'
                    ];
                }
            }

            // 2. Cek Roasted Bean Stock Menipis (< 5kg / 5000g)
            if (\Illuminate\Support\Facades\Schema::hasTable('roasted_bean_stock')) {
                $lowRoastedBeans = \App\Models\RoastedBeanStock::selectRaw('roasted_bean_id, sum(stok_gram) as total_stok')
                    ->groupBy('roasted_bean_id')
                    ->havingRaw('total_stok < 5000') 
                    ->with('roastedBean')
                    ->get();

                foreach ($lowRoastedBeans as $stock) {
                    if ($stock->roastedBean) {
                        $notifications[] = [
                            'type' => 'stock_roasted',
                            'title' => 'Stok Roasted Bean Menipis',
                            'message' => 'Sisa stok ' . $stock->roastedBean->nama_biji . ' tinggal ' . number_format($stock->total_stok / 1000, 2) . ' Kg.',
                            'time' => 'Sistem Peringatan Dini',
                            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 8c0 4.5-5 9-5 9s-5-4.5-5-9a5 5 0 0 1 10 0z"></path></svg>',
                            'color' => '[#C87A54]'
                        ];
                    }
                }
            }

            // 3. Cek Packaging Stock Menipis (< 50 pcs)
            if (\Illuminate\Support\Facades\Schema::hasTable('packagings')) {
                $lowPackagings = \App\Models\Packaging::where('stok_pcs', '<', 50)->get();
                
                foreach ($lowPackagings as $packaging) {
                    $notifications[] = [
                        'type' => 'stock_packaging',
                        'title' => 'Stok Kemasan Menipis',
                        'message' => 'Kemasan ' . $packaging->nama_kemasan . ' sisa ' . $packaging->stok_pcs . ' pcs.',
                        'time' => 'Sistem Peringatan Dini',
                        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>',
                        'color' => 'primary'
                    ];
                }
            }

            $view->with('system_notifications', collect($notifications)->take(5));
        });
    }
}
