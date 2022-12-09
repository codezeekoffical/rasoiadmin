<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Food;
use App\Models\Translation;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use App\CentralLogics\Helpers;
use App\CentralLogics\ProductLogic;

class FoodController extends Controller
{

 public function upload_images(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required',
        ]);

        if ($validator->errors()->count() > 0) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

      //  $path = $request['type'] == 'product' ? '' : $request['type'] . '/';
        $image = Helpers::upload('product/', 'png', $request->file('image'));
        
        return response()->json(['image_name' => $image], 200);
    }


    public function store(Request $request)
    {
        if(!$request->vendor->restaurants[0]->food_section)
        {
            return response()->json([
                'errors'=>[
                    ['code'=>'unauthorized', 'message'=>trans('messages.permission_denied')]
                ]
            ],403);
        }
        
        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
            'images' => 'required',
            'price' => 'required|numeric|min:0.01',
            'discount' => 'required|numeric|min:0',
            'veg' => 'required|boolean',
            'translations'=>'required',
           
            
        ], [
            'category_id.required' => trans('messages.category_required'),
        ]);

        if ($request['discount_type'] == 'percent') {
            $dis = ($request['price'] / 100) * $request['discount'];
        } else {
            $dis = $request['discount'];
        }

        if ($request['price'] <= $dis) {
            $validator->getMessageBag()->add('unit_price', trans('messages.discount_can_not_be_more_than_or_equal'));
        }

        $data = json_decode($request->translations, true);

        if (count($data) < 1) {
            $validator->getMessageBag()->add('translations', trans('messages.Name and description in english is required'));
        }

        if ($request['price'] <= $dis || count($data) < 1 || $validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }
        
        $food = new Food;
        $food->name = $data[0]['value'];

        $category = [];
        if ($request->category_id != null) {
            array_push($category, [
                'id' => $request->category_id,
                'position' => 1,
            ]);
        }
        if ($request->sub_category_id != null) {
            array_push($category, [
                'id' => $request->sub_category_id,
                'position' => 2,
            ]);
        }
        if ($request->sub_sub_category_id != null) {
            array_push($category, [
                'id' => $request->sub_sub_category_id,
                'position' => 3,
            ]);
        }
        $food->category_id = $request->sub_category_id?$request->sub_category_id:$request->category_id;
       
        $food->category_ids = json_encode($category);
        $food->description = $data[1]['value'];
         $food->available_date = $request->available_date;
        $food->delivery_date = $request->delivery_date;

        $choice_options = [];
        if ($request->has('choice')) {
            foreach (json_decode($request->choice_no) as $key => $no) {
                $str = 'choice_options_' . $no;
                if ($request[$str][0] == null) {
                    $validator->getMessageBag()->add('name', trans('messages.attribute_choice_option_value_can_not_be_null'));
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                $item['name'] = 'choice_' . $no;
                $item['title'] = json_decode($request->choice)[$key];
                $item['options'] = explode(',', implode('|', preg_replace('/\s+/', ' ', json_decode($request[$str]))));
                array_push($choice_options, $item);
            }
        }
        $food->choice_options = json_encode($choice_options);
        $variations = [];
        $options = [];
        if ($request->has('choice_no')) {
            foreach (json_decode($request->choice_no) as $key => $no) {
                $name = 'choice_options_' . $no;
                $my_str = implode('|', json_decode($request[$name]));
                array_push($options, explode(',', $my_str));
            }
        }
        //Generates the combinations of customer choice options
        $combinations = Helpers::combinations($options);
        if (count($combinations[0]) > 0) {
            foreach ($combinations as $key => $combination) {
                $str = '';
                foreach ($combination as $k => $item) {
                    if ($k > 0) {
                        $str .= '-' . str_replace(' ', '', $item);
                    } else {
                        $str .= str_replace(' ', '', $item);
                    }
                }
                $item = [];
                $item['type'] = $str;
                $item['price'] = abs($request['price_' . str_replace('.', '_', $str)]);
                array_push($variations, $item);
            }
        }
        //combinations end
        
        
     /*    $images =[];
        if ($request->image != null) {
        foreach(json_decode($request->file('image')) as $image ){
            
            $imagename = Helpers::upload('product/', 'png', $image);
                  array_push($images, [
                      'image' =>$imagename]);

            
        } 
         }
         
       $image_count = $request->image_count;
        
        
        for($i = 0 ; $i < $image_count ; $i++){
            $imagename = Helpers::upload('product/', 'png', $request->file('image'));
                  array_push($images, [
                      'image' =>$imagename]);
        }*/
        
     //   $food->image = json_encode($request->images);
     
     
       $images =[];
        if ($request->images != null) {
            
            
        foreach(json_decode($request->images) as $image ){
            
                  array_push($images, [
                      'image' =>$image]);

            
        } 
         }
        $food->image =json_encode($images);

        if ($request->video != null) {
        $food->video = Helpers::videoupload('product/video/', 'mp4', $request->file('video'));
        }
        if($request->has('special_food')){
              $food->special_food = $request->special_food;
        $food->upcoming = 0;
        $food->today = 0;
        }
         if($request->has('upcoming')){
              $food->special_food =0;
        $food->upcoming = 1;
        $food->today = 0;
        }
        if($request->has('today')){
              $food->special_food =0;
        $food->upcoming = 0;
        $food->today = 1;
        
        
        }
        
        $food->variations = json_encode($variations);
        $food->price = $request->price;
        
       // $food->image = json_encode($images);
       // $food->image = Helpers::upload('product/', 'png', $request->file('image'));
        $food->available_time_starts = $request->available_time_starts;
        $food->available_time_ends = $request->available_time_ends;
        $food->discount = $request->discount_type == 'amount' ? $request->discount : $request->discount;
        $food->discount_type = $request->discount_type;
        $food->attributes = $request->has('attribute_id') ? $request->attribute_id : json_encode([]);
        $food->add_ons = $request->has('addon_ids') ? json_encode(explode(',',$request->addon_ids)) : json_encode([]);
        $food->restaurant_id = $request['vendor']->restaurants[0]->id;
        $food->veg = $request->veg;
        $food->save();

        unset($data[1]);        
        unset($data[0]);        
        foreach ($data as $key=>$item) {
            $data[$key]['translationable_type'] = 'App\Models\Food';
            $data[$key]['translationable_id'] = $food->id;
        }
        Translation::insert($data);

        return response()->json(['message'=>trans('messages.product_added_successfully')], 200);
    }

    public function status(Request $request)
    {
        if(!$request->vendor->restaurants[0]->food_section)
        {
            return response()->json([
                'errors'=>[
                    ['code'=>'unauthorized', 'message'=>trans('messages.permission_denied')]
                ]
            ],403);
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $product = Food::find($request->id);
        $product->status = $request->status;
        $product->save();

        return response()->json(['message' => trans('messages.product_status_updated')], 200);
    }

    public function update(Request $request)
    {
        if(!$request->vendor->restaurants[0]->food_section)
        {
            return response()->json([
                'errors'=>[
                    ['code'=>'unauthorized', 'message'=>trans('messages.permission_denied')]
                ]
            ],403);
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'category_id' => 'required',
            'price' => 'required|numeric|min:0.01',
            'discount' => 'required|numeric|min:0',
            'veg' => 'required|boolean',

        ], [
            'category_id.required' => trans('messages.category_required'),
        ]);

        if ($request['discount_type'] == 'percent') {
            $dis = ($request['price'] / 100) * $request['discount'];
        } else {
            $dis = $request['discount'];
        }

        if ($request['price'] <= $dis) {
            $validator->getMessageBag()->add('unit_price', trans('messages.discount_can_not_be_more_than_or_equal'));
        }
        $data = json_decode($request->translations, true);

        if (count($data) < 1) {
            $validator->getMessageBag()->add('translations', trans('messages.Name and description in english is required'));
        }

        if ($request['price'] <= $dis || count($data) < 1 || $validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        $p = Food::findOrFail($request->id);
        
        $remove_images_count = $request->remove_count;
        
      /*  if($remove_images_count != 0){
             $product_images = json_decode($p->image);
        
        /*    if (sizeof(array_keys($product_images)) < 2) {
            Toastr::warning('You cannot delete all images!');
            return back();
        } 
        
        
        function RemoveSpecialChar($str) {
  
      // Using str_replace() function 
      // to replace the word 
      $res = str_replace( array( '\'', '"',
      ',' , ';', '<', '>' ), ' ', $str);
  
      // Returning the result 
      return $res;
      }
      
      
      // Given string
  $str = $request->remove1; 
  
  // Function calling
  $str1 = RemoveSpecialChar($str); 
        
      
       $array = [];
       $size = sizeof(array_keys($product_images));
       
        for( $i = 0; $i < $size; $i++){
            
          //  for($j=1; $j < $remove_images_count+1; $j++){
                
           //     Helpers::delete('/product/', $str1);
                
                 if ($product_images[$i]->image != $str1) {
                array_push($array, [
                      'image' =>$product_images[$i]->image]);
                    }
                  
     //       }
           
        }
          
Food::where('id', $request->id)->update([
            'image' => json_encode($array) , 'video' => $product_images[0]->image .'nnnnn'. $str1,
        ]); 
        
       
        }*/
        
        
         $images =[];
        if ($request->images != null) {
            
            
        foreach(json_decode($request->images) as $image ){
            
                  array_push($images, [
                      'image' =>$image]);

            
        } 
         }
        $p->image =json_encode($images);

        $p->name = $data[0]['value'];

        $category = [];
        if ($request->category_id != null) {
            array_push($category, [
                'id' => $request->category_id,
                'position' => 1,
            ]);
        }
        if ($request->sub_category_id != null) {
            array_push($category, [
                'id' => $request->sub_category_id,
                'position' => 2,
            ]);
        }
        if ($request->sub_sub_category_id != null) {
            array_push($category, [
                'id' => $request->sub_sub_category_id,
                'position' => 3,
            ]);
        }

        $p->category_id = $request->sub_category_id?$request->sub_category_id:$request->category_id;
        $p->category_ids = json_encode($category);
        $p->description = $data[1]['value'];

        $choice_options = [];
        if ($request->has('choice')) {
            foreach (json_decode($request->choice_no) as $key => $no) {
                $str = 'choice_options_' . $no;
                if (json_decode($request[$str])[0] == null) {
                    $validator->getMessageBag()->add('name', trans('messages.attribute_choice_option_value_can_not_be_null'));
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                $item['name'] = 'choice_' . $no;
                $item['title'] = json_decode($request->choice)[$key];
                $item['options'] = explode(',', implode('|', preg_replace('/\s+/', ' ', json_decode($request[$str]))));
                array_push($choice_options, $item);
            }
        }
        
        
        $p->choice_options = json_encode($choice_options);
        $variations = [];
        $options = [];
        if ($request->has('choice_no')) {
            foreach (json_decode($request->choice_no) as $key => $no) {
                $name = 'choice_options_' . $no;
                $my_str = implode('|', json_decode($request[$name]));
                array_push($options, explode(',', $my_str));
            }
        }
        //Generates the combinations of customer choice options
        $combinations = Helpers::combinations($options);
        if (count($combinations[0]) > 0) {
            foreach ($combinations as $key => $combination) {
                $str = '';
                foreach ($combination as $k => $item) {
                    if ($k > 0) {
                        $str .= '-' . str_replace(' ', '', $item);
                    } else {
                        $str .= str_replace(' ', '', $item);
                    }
                }
                $item = [];
                $item['type'] = $str;
                $item['price'] = abs($request['price_' . str_replace('.', '_', $str)]);
                array_push($variations, $item);
            }
        }
        //combinations end
        
    //     $food_images = json_decode($p->image);
         
         
       /*  $image_count = $request->image_count;
        
        
        for($i = 0 ; $i < $image_count ; $i++){
            $imagename = Helpers::upload('product/', 'png', $request->file('image'));
                  array_push($food_images, [
                      'image' =>$imagename]);
        }*/
        
         
       //  $p->image = $food_images;
       
       

         
      /*   if ($request->file('image')) {
                 foreach($request->file('image') as $image ){
            
            $imagename = Helpers::upload('product/', 'png', $image);
                  array_push($food_images, [
                      'image' =>$imagename]);
        }
            } */
        
        
        if ($request->video != null) {
        $p->video = Helpers::videoupload('product/video/', 'mp4', $request->file('video'));
        }
        
        $p->available_date = $request->available_date;
        $p->delivery_date = $request->delivery_date;
        $p->variations = json_encode($variations);
        $p->price = $request->price;
      //  $p->image = $request->has('image') ? Helpers::update('product/', $p->image, 'png', $request->file('image')) : $p->image;
        $p->available_time_starts = $request->available_time_starts;
        $p->available_time_ends = $request->available_time_ends;
        $p->discount = $request->discount_type == 'amount' ? $request->discount : $request->discount;
        $p->discount_type = $request->discount_type;
        $p->attributes = $request->has('attribute_id') ? $request->attribute_id : json_encode([]);
        $p->add_ons = $request->has('addon_ids') ? json_encode(explode(',',$request->addon_ids)) : json_encode([]);
        $p->veg = $request->veg;
        $p->save();

        unset($data[1]);        
        unset($data[0]);   
        foreach ($data as $key=>$item) {
            Translation::updateOrInsert(
                ['translationable_type' => 'App\Models\Food',
                    'translationable_id' => $p->id,
                    'locale' => $item['locale'],
                    'key' => $item['key']],
                ['value' => $item['value']]
            );
        }

        return response()->json(['message'=>trans('messages.product_updated_successfully')], 200);
    }
    
    public function remove_image(Request $request)
    {
        
        if(!$request->vendor->restaurants[0]->food_section)
        {
            return response()->json([
                'errors'=>[
                    ['code'=>'unauthorized', 'message'=>trans('messages.permission_denied')]
                ]
            ],403);
        }
        Helpers::delete('/product/', $request['image']);
        $food = Food::find($request['id']);
        $array = [];
        
        $product_images = json_decode($food->image);
        
        if (sizeof(array_keys($product_images)) < 2) {
           // Toastr::warning('You cannot delete all images!');
        return response()->json(['message'=>'You cannot delete all images!'], 200);
        }
        
       
       $size = sizeof(array_keys($product_images));
       
        for( $i = 0; $i < $size; $i++){
            if ($product_images[$i]->image != $request['name']) {
                array_push($array, [
                      'image' =>$product_images[$i]->image]);
            }
        }
        
    /*    foreach (json_decode($product['images']) as $image) {
            if ($image != $request['name']) {
                array_push($array, $image);
            }
        }*/
        
        Food::where('id', $request['id'])->update([
            'image' => json_encode($array),
        ]);
       // Toastr::success('Product image removed successfully!');
        return response()->json(['message'=>'Product image removed successfully!'], 200);
    }

    public function delete(Request $request)
    {
        if(!$request->vendor->restaurants[0]->food_section)
        {
            return response()->json([
                'errors'=>[
                    ['code'=>'unauthorized', 'message'=>trans('messages.permission_denied')]
                ]
            ],403);
        }
        $product = Food::findOrFail($request->id);

        if($product->image)
        {
            if (Storage::disk('public')->exists('product/' . $product['image'])) {
                Storage::disk('public')->delete('product/' . $product['image']);
            }
        }
        $product->translations()->delete();
        $product->delete();

        return response()->json(['message'=>trans('messages.product_deleted_successfully')], 200);
    }

    public function search(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $key = explode(' ', $request['name']);

        $products = Food::active()
        ->with(['rating'])
        ->where('restaurant_id', $request['vendor']->restaurants[0]->id)
        ->when($request->category_id, function($query)use($request){
            $query->whereHas('category',function($q)use($request){
                return $q->whereId($request->category_id)->orWhere('parent_id', $request->category_id);
            });
        })
        ->when($request->restaurant_id, function($query) use($request){
            return $query->where('restaurant_id', $request->restaurant_id);
        })
        ->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('name', 'like', "%{$value}%");
            }
        })
        ->limit(50)
        ->get();

        $data = Helpers::product_data_formatting($products, true, false, app()->getLocale());
        return response()->json($data, 200);
    }

    public function reviews(Request $request)
    {
        $id = $request['vendor']->restaurants[0]->id;;

        $reviews = Review::with(['customer', 'food'])
        ->whereHas('food', function($query)use($id){
            return $query->where('restaurant_id', $id);
        })
        ->latest()->get();

        $storage = [];
        foreach ($reviews as $item) {
            $item['attachment'] = json_decode($item['attachment']);
            $item['food_name'] = null;
            $item['food_image'] = null;
            $item['customer_name'] = null;
            if($item->food)
            {
                $item['food_name'] = $item->food->name;
                $item['food_image'] = $item->food->image;
                if(count($item->food->translations)>0)
                {
                    $translate = array_column($item->food->translations->toArray(), 'value', 'key');
                    $item['food_name'] = $translate['name'];
                }
            }
            
            if($item->customer)
            {
                $item['customer_name'] = $item->customer->f_name.' '.$item->customer->l_name;
            }
            
            unset($item['food']);
            unset($item['customer']);
            array_push($storage, $item);
        }

        return response()->json($storage, 200);
    }
}
