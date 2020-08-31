<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class MakeShopWebhook
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $path   = storage_path('logs/webhook_order.log');
        config(['logging.channels.daily.path' => $path]);
        Log::debug('Webhook data: ' . json_encode($request->all()));
        $shopId = $request->request->get('shopid');
        if ($shopId === config('makeshop.api.shopid')) {
            return $next($request);
        }
        Log::error('Webhook Makeshop ID not valid. Shopid = ' . $shopId);
        abort(404);
    }
}
