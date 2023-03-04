<?php

namespace Modules\MobileApp\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Frontend\TenantFrontendController;
use App\Models\OrderProducts;
use App\Models\ProductOrder;
use App\Models\ProductReviews;
use App\Models\StaticOption;
use Modules\Attributes\Entities\Brand;
use Modules\Attributes\Entities\Category;
use Modules\Attributes\Entities\Color;
use Modules\Attributes\Entities\Size;
use Modules\Attributes\Entities\Unit;
use Modules\MobileApp\Http\Resources\Api\MobileFeatureProductResource;
use App\Http\Resources\ProductResource;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductAttribute;
use Modules\Product\Entities\ProductCategory;
use Modules\Product\Entities\ProductShippingReturnPolicy;
use Modules\Product\Entities\ProductTag;
use Modules\Product\Entities\ProductUnit;
use Modules\Product\Entities\ProductUom;
use Modules\Product\Entities\SaleDetails;
use Illuminate\Http\Request;
use Modules\MobileApp\Http\Services\Api\ApiProductServices;
use Modules\Product\Services\Web\FrontendProductServices;

class ProductController extends Controller
{

    public function search(Request $request){
        $all_products = ApiProductServices::productSearch($request, "api", "api");
        $products = $all_products["items"];
        unset($all_products["items"]);
        $additional = $all_products;

        return MobileFeatureProductResource::collection($products)->additional($additional);
    }
    public function productDetail($id){
        $product = Product::where('id', $id)
            ->with(
                'category',
                'tag',
                'color',
                'sizes',
                'campaign_product',
                'inventoryDetail',
                'inventoryDetail.productColor',
                'inventoryDetail.productSize',
                'inventoryDetail.attribute',
                'reviews',
                'delivery_option',
            )
            ->where("status_id", 1)
            ->firstOrFail();

        // get selected attributes in this product ( $available_attributes )
        $inventoryDetails = optional($product->inventoryDetail);
        $product_inventory_attributes = $inventoryDetails->toArray();

        $all_included_attributes = array_filter(array_column($product_inventory_attributes, 'attribute', 'id'));
        $all_included_attributes_prd_id = array_keys($all_included_attributes);

        $available_attributes = [];  // FRONTEND : All displaying attributes
        $product_inventory_set = []; // FRONTEND : attribute store
        $additional_info_store = []; // FRONTEND : $additional info_store

        foreach ($all_included_attributes as $id => $included_attributes) {
            $single_inventory_item = [];
            foreach ($included_attributes as $included_attribute_single) {
                $available_attributes[$included_attribute_single['attribute_name']][$included_attribute_single['attribute_value']] = 1;

                // individual inventory item
                $single_inventory_item[$included_attribute_single['attribute_name']] = $included_attribute_single['attribute_value'];


                if (optional($inventoryDetails->find($id))->productColor) {
                    $single_inventory_item['Color'] = optional(optional($inventoryDetails->find($id))->productColor)->name;
                    $single_inventory_item['color_code'] = optional(optional($inventoryDetails->find($id))->productColor)->color_code;
                }

                if (optional($inventoryDetails->find($id))->productSize) {
                    $single_inventory_item['Size'] = optional(optional($inventoryDetails->find($id))->productSize)->name;
                }
            }

            $item_additional_price = optional(optional($product->inventoryDetail)->find($id))->additional_price ?? 0;
            $item_additional_stock = optional(optional($product->inventoryDetail)->find($id))->stock_count ?? 0;
            $image = get_attachment_image_by_id(optional(optional($product->inventoryDetail)->find($id))->image)['img_url'] ?? '';

            $hash = md5(json_encode($single_inventory_item));
            $single_inventory_item['hash'] = $hash;
            $product_inventory_set[] = $single_inventory_item;

            $sorted_inventory_item = $single_inventory_item;
            ksort($sorted_inventory_item);

            $additional_info_store[$hash] = [
                'pid_id' => $id, //Info: ProductInventoryDetails id
                'additional_price' => $item_additional_price,
                'stock_count' => $item_additional_stock,
                'image' => $image,
            ];
        }

        $productColors = $product->color->unique();
        $productSizes = $product->sizes->unique();

        $colorAndSizes = $product?->inventoryDetail?->whereNotIn("id", $all_included_attributes_prd_id);

        if (!empty($colorAndSizes)) {
            $product_id = $product_inventory_attributes[0]['id'] ?? $product->id;

            foreach ($colorAndSizes as $inventory) {
                // if this inventory has attributes then it will fire continue statement
                if (in_array($inventory->product_id, $all_included_attributes_prd_id)) {
                    continue;
                }

                $single_inventory_item = [];

                if (optional($inventoryDetails->find($product_id))->color) {
                    $single_inventory_item['Color'] = optional($inventory->productColor)->name;
                    $single_inventory_item['color_code'] = optional($inventory->productColor)->color_code;
                }

                if (optional($inventoryDetails->find($product_id))->size) {
                    $single_inventory_item['Size'] = optional($inventory->productSize)->name;
                }

                $hash = md5(json_encode($single_inventory_item));
                $single_inventory_item['hash'] = $hash;
                $product_inventory_set[] = $single_inventory_item;

                $item_additional_price = optional($inventory)->additional_price ?? 0;
                $item_additional_stock = optional($inventory)->stock_count ?? 0;
                $image = get_attachment_image_by_id(optional($inventory)->image)['img_url'] ?? '';

                $sorted_inventory_item = $single_inventory_item;
                ksort($sorted_inventory_item);


                $additional_info_store[$hash] = [
                    'pid_id' => $product_id,
                    'additional_price' => $item_additional_price,
                    'stock_count' => $item_additional_stock,
                    'image' => $image,
                ];
            }
        }

        // todo:: write code for product category only add image path into category array
        $categoryImage = get_attachment_image_by_id($product->category->image_id);
        $product->category->categoryImage = !empty($categoryImage) ? $categoryImage['img_url'] : '';
        unset($product->category->image_id);
        unset($product->category->laravel_through_key);
        unset($product->category->image_id);

        // todo:: write code for product sub category only add image path into category array
        $subCategoryImage = get_attachment_image_by_id($product->subCategory->image_id);
        $product->subCategory->categoryImage = !empty($subCategoryImage) ? $subCategoryImage['img_url'] : '';
        unset($product->subCategory->image_id);
        unset($product->subCategory->laravel_through_key);
        unset($product->subCategory->image_id);

        // todo:: write code for product sub category only add image path into category array
        $product->childCategory->transform(function ($item){
            $image = $item->image_id;
            unset($item->image_id);
            unset($item->image_id);
            unset($item->laravel_through_key);

            $image = get_attachment_image_by_id($image);
            $item->image = !empty($image) ? $image['img_url'] : '';
            return $item;
        });

        foreach($product->gallery_images as $gallery){
            $image = get_attachment_image_by_id($gallery->id);
            $gallery->image = !empty($image) ? $image['img_url'] : '';
            unset($gallery->id);
            unset($gallery->title);
            unset($gallery->path);
            unset($gallery->alt);
            unset($gallery->size);
            unset($gallery->dimensions);
            unset($gallery->user_id);
            unset($gallery->created_at);
            unset($gallery->updated_at);
            unset($gallery->laravel_through_key);
        }

        // test
        $productImage = $product->image_id;
        unset($product->image_id);
        $productImage = get_attachment_image_by_id($productImage);
        $product->image = !empty($productImage) ? $productImage['img_url'] : '';

        $product->reviews->transform(function ($item){
            $p_image = get_attachment_image_by_id($item->user->image);
            unset($item->user->image);
            $item->user->image = !empty($p_image) ? $p_image['img_url'] : '';
            return $item;
        });

        $available_attributes = array_map(fn($i) => array_keys($i), $available_attributes);

        $sub_category_arr = json_decode($product->sub_category_id, true);
        $ratings = ProductReviews::where('product_id', $product->id)->with('user')->get();
        $ratings->transform(function ($item){
            $p_image = get_attachment_image_by_id($item->user->image);
            unset($item->user->image);
            $item->user->image = !empty($p_image) ? $p_image['img_url'] : '';
            return $item;
        });

        $avg_rating = $ratings->count() ? round($ratings->sum('rating') / $ratings->count()) : null;

        // related products
        $product_category = $product?->category?->id;
        $product_id = $product->id;
        $related_products = Product::with('campaign_product','campaign_product.campaign_sold_product','reviews','inventory','badge','uom')->where('status_id', 1)
            ->whereIn('id', function ($query) use ($product_id, $product_category) {
                $query->select('product_categories.product_id')
                    ->from(with(new ProductCategory())->getTable())
                    ->where('product_id', '!=', $product_id)
                    ->where('category_id', '=', $product_category)
                    ->get();
            })
            ->inRandomOrder()
            ->take(5)
            ->get();

        // (bool) Check logged-in user bought this item (needed for review)
        $user = getUserByGuard('sanctum');

        $user_has_item = $user
            ? !!ProductOrder::where('user_id', $user->id)
                ->where('product_id', $product->id)->count()
            : null;

        $user_rated_already = !!! ProductReviews::where('product_id', optional($product)->id)->where('user_id', optional($user)->id)->count();

        $setting_text = StaticOption::whereIn('option_name', [
            'product_in_stock_text',
            'product_out_of_stock_text',
            'details_tab_text',
            'additional_information_text',
            'reviews_text',
            'your_reviews_text',
            'write_your_feedback_text',
            'post_your_feedback_text',
        ])->get()->mapWithKeys(fn ($item) => [$item->option_name => $item->option_value])->toArray();

        $return_policy = ProductShippingReturnPolicy::where('product_id' ,$product->id)->first();

        // sidebar data
        $all_units = ProductUom::all();
        $maximum_available_price = Product::query()->with('category')->max('price');
        $min_price = request()->pr_min ? request()->pr_min : Product::query()->min('price');
        $max_price = request()->pr_max ? request()->pr_max : $maximum_available_price;
        $all_tags = ProductTag::all();

        return [
            'product' => $product,
            'product_url' => route("tenant.shop.product.details", $product->slug),
            'related_products' => $related_products,
            'user_has_item' => $user_has_item,
            'ratings' => $ratings,
            'avg_rating' => $avg_rating,
            'available_attributes' => $available_attributes,
            'product_inventory_set' => $product_inventory_set,
            'additional_info_store' => $additional_info_store,
//            'all_units' => $all_units,
            'maximum_available_price' => $maximum_available_price,
//            'min_price' => $min_price,
//            'max_price' => $max_price,
//            'all_tags' => $all_tags,
            'productColors' => $productColors,
            'productSizes' => $productSizes,
            'setting_text' => $setting_text,
            'user_rated_already' => $user_rated_already,
            'return_policy' => $return_policy
        ];
    }

    public function priceRange(){
        $max_price = Product::query()->with('category')->max('price');
        $min_price = Product::query()->min('price');

        return response()->json(["min_price" => $min_price, "max_price" => $max_price]);
    }

    public function storeReview(Request $request){
        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json(['msg' => 'Login to submit rating'])->setStatusCode(422);
        }

        $request->validate([
            'id' => 'required|exists:products',
            'rating' => 'required|integer',
            'comment' => 'required|string',
        ]);

        if ($request->rating > 5) {
            $rating = 5;
        }

        // ensure rating not inserted before
        $user_rated_already = !! ProductRating::where('product_id', $request->id)->where('user_id', $user->id)->count();
        if ($user_rated_already) {
            return response()->json(['msg' => __('You have rated before')])->setStatusCode(422);
        }

        $rating = ProductRating::create([
            'product_id' => $request->id,
            'user_id' => $user->id,
            'status' => 1,
            'rating' => $request->rating,
            'review_msg' => $request->comment,
        ]);

        return response()->json(["success" => true,"data" => $rating]);
    }

    public function searchItems(){
        return FrontendProductServices::shopPageSearchContent();

        $min_price = Product::query()->min('sale_price');
        $max_price = $maximum_available_price;
        $item_style =['grid','list'];

        return view('frontend.dynamic-redirect.product', compact(
            'all_category',
            'all_attributes',
            'all_tags',
            'all_colors',
            'all_sizes',
            'all_units',
            'all_brands',
            'min_price',
            'max_price',
            'maximum_available_price',
            'item_style',
        ));
    }
}
