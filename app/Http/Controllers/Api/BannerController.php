<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;

class BannerController extends Controller
{
    /**
     * Display active banners for the Flutter customer app.
     */
    public function index(): JsonResponse
    {
        $banners = Banner::query()
            ->where('status', 1)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (Banner $banner) {
                return [
                    'id'         => $banner->id,
                    'title'      => $banner->title,
                    'subtitle'   => $banner->subtitle,
                    'image'      => $banner->image,
                    'image_url' => $banner->image
                        ? url('/api/home-banner-image/' . ltrim($banner->image, '/'))
                        : null,
                    'link_type'  => $banner->link_type,
                    'link_id'    => $banner->link_id,
                    'sort_order' => $banner->sort_order,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Banners retrieved successfully.',
            'data'    => $banners,
        ]);
    }
}
