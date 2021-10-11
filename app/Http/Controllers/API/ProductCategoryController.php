<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function all(Request $request){
        $id = $request->input('id');
        $limit = $request->input('limit');
        $name = $request->input('name');

        $show_product = $request->input('show_product');

        if($id){
            $productcategory = ProductCategory::with(['products'])->find($id);

            if($productcategory){
                return ResponseFormatter::success(
                    $productcategory, 
                    'Data Kategori berhasil diambil'
                );
            }else{
                return ResponseFormatter::error(
                    null,'Data Kategori tidak ada',404
                );
            }

        }

        $productcategory = ProductCategory::with(['products']);

        if($name){
            $productcategory->where('name','like','%' . $name .'%');   
        }

        if($show_product){
            $productcategory->with('products');
        }

        return ResponseFormatter::success(
            $productcategory->paginate($limit),
            'Data Kategori berhasil diambil'
        );
    }
}
