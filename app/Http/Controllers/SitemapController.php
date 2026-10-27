<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $products = Product::where('status', 'Active')->select('slug', 'updated_at')->get();
        $categories = Category::where('status', 'Active')->select('slug', 'updated_at')->get();

        $content = view('sitemap', compact('products', 'categories'))->render();

        return response($content, 200)
            ->header('Content-Type', 'application/xml');
    }
}
