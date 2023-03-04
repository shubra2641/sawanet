<?php

namespace Modules\MobileApp\Http\Services\Api;

use LaravelIdea\Helper\Modules\Product\Entities\_IH_Product_C;
use Modules\MobileApp\Entities\MobileFeaturedProduct;
use Modules\Product\Entities\Product;
use Illuminate\Database\Eloquent\Collection;

class MobileFeaturedProductService
{
    public static function get_product(): Collection|array|null
    {
        $selectedProduct = MobileFeaturedProduct::first();
        $product = Product::query()->with("category","inventoryDetail");
        $ids = json_decode($selectedProduct->ids);

        if($selectedProduct->type == 'product'){
            return $product->whereIn("id",$ids)->get();
        }elseif ($selectedProduct->type == 'category'){
            return $product->whereHas("category", function ($query) use ($ids) {
                $query->whereIn("categories.id", $ids);
            })->get();
        }

        return [];
    }
}
