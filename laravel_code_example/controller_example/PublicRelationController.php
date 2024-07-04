<?php


use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\ImageGalery;
use App\Models\PublicRelation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class PublicRelationController extends Controller
{
    function index(){
        $publicRelation = PublicRelation::first();

        return \App\Http\Controllers\Dashboard\view('dashboard.pages.publicRelation.index', compact('publicRelation'));
    }

    function updateOrCreatePublicRelation(Request $request){

        $publicRelation = PublicRelation::all();

        if ($publicRelation->count() > 0){
            $request->validate([
                'title' => 'required|max:255',
                'description' => 'required',
                'lat' => 'required',
                'lng' => 'required',
                'address' => 'required|max:255',
                'phone' => 'required',
                'coverImg' => 'image',
                'image_path_url.*' => 'image',

            ],
                [
                    'image_path_url.required' => 'İçerik Fotoğraf alanı boş bırakılamaz.',
                    'image_path_url.*.image' => 'İçerik Fotoğrafı Yalnızca jpg, jpeg, png olabilir',
                    'coverImg' => 'Kapak Fotoğrafı Seçiniz',
                    'coverImg.image' => 'Kapak fotoğrafı Yalnızca jpg, jpeg, png olabilir',
                    'coverImg.required' => 'Kapak Fotoğrafı Boş Bırakılamaz',
                    'title.required' => 'Başlık Alanı Gerekli',
                    'title.max' => 'Başlık Alanı  255 karakter değerinden küçük olmalıdır',
                    'description.required' => 'Açıklama Alanı Gerekli',
                    'lat.required' => 'Enlem Alanı Gerekli',
                    'lng.required' => 'Boylam Alanı Gerekli',
                    'address.required' => 'Adres Alanı Gerekli',
                    'address.max' => 'Adres Alanı 255 karakter değerinden küçük olmalıdır',
                    'phone.required' => 'Telefon Alanı Gerekli',
                ]);

            $relation = PublicRelation::first();

            $relation->title = $request->title;
            $relation->description = $request->description;
            $relation->lat = $request->lat;
            $relation->lng = $request->lng;
            $relation->address = $request->address;
            $relation->phone = $request->phone;

            if ($request->hasFile('coverImg')){
                $coverPhoto = $request->file('coverImg');
                $name = time().$coverPhoto->getClientOriginalName();
                $path = '/uploads/publicRelationImages/';
                $coverPhoto->move(\App\Http\Controllers\Dashboard\public_path($path), $name);
                $relation->cover_img_path = $path.$name;
            }

            if ($request->hasFile('image_path_url')){
                $contentImages = $request->file('image_path_url');

                foreach ($contentImages as $key => $image) {
                    $img = new Image();
                    $img->image_gallery_id = $relation->img_gallery_id;
                    $name = time().$image->getClientOriginalName();
                    $imagePath = '/uploads/publicRelationImages/';
                    $image->move(\App\Http\Controllers\Dashboard\public_path($imagePath), $name);
                    $img->image_path_url = $imagePath.$name;
                    $img->save();
                }
            }


            $relation->save();

            return \App\Http\Controllers\Dashboard\back()->with('message','İçerik Başarıyla Güncellendi.');
        }
        else {
            $request->validate([
                'title' => 'required|max:255',
                'description' => 'required',
                'lat' => 'required',
                'lng' => 'required',
                'address' => 'required|max:255',
                'phone' => 'required',
                'coverImg' => 'image|required',
                'image_path_url.*' => 'image',
                'image_path_url' => 'required',

            ],
                [
                    'image_path_url.*.image' => 'İçerik Fotoğrafı Yalnızca jpg, jpeg, png olabilir',
                    'coverImg.image' => 'Kapak Fotoğrafı Yalnızca jpg, jpeg, png olabilir',
                    'title.required' => 'Başlık Alanı Gerekli',
                    'image_path_url.required' => 'İçerik Fotoğrafı Boş Bırakılamaz',
                    'coverImg.required' => 'Kapak Fotoğrafı Boş Bırakılamaz',
                    'description.required' => 'Açıklama Alanı Gerekli',
                    'lat.required' => 'Enlem Alanı Gerekli',
                    'lng.required' => 'Boylam Alanı Gerekli',
                    'address.required' => 'Adres Alanı Gerekli',
                    'phone.required' => 'Telefon Alanı Gerekli',
                ]);

            $relation = new PublicRelation();

            $relation->title = $request->title;
            $relation->description = $request->description;
            $relation->lat = $request->lat;
            $relation->lng = $request->lng;
            $relation->address = $request->address;
            $relation->phone = $request->phone;

            $coverPhoto = $request->file('coverImg');
            $name = time().$coverPhoto->getClientOriginalName();
            $path = '/uploads/publicRelationImages/';
            if (!is_dir(public_path($path))){
                File::makeDirectory($path, 775,true,true);
            }
            $coverPhoto->move(\App\Http\Controllers\Dashboard\public_path($path), $name);
            $relation->cover_img_path = $path.$name;

            $contentImages = $request->file('image_path_url');
            $gallery = new ImageGalery();
            $gallery->name = $relation->title;
            $gallery->is_report = 1;
            $gallery->save();

            $relation->img_gallery_id = $gallery->id;

            foreach ($contentImages as $key => $image) {
                $img = new Image();
                $img->image_gallery_id = $gallery->id;
                $name = time().rand(0,1000).$image->getClientOriginalName();
                $imagePath = '/uploads/publicRelationImages/';
                $image->move(\App\Http\Controllers\Dashboard\public_path($path), $name);
                $img->image_path_url = $imagePath.$name;
                $img->save();
            }

            $relation->save();

            return \App\Http\Controllers\Dashboard\back()->with('message','İçerik Başarıyla Oluşturuldu.');
        }
    }

    function checkImageCount(Request $request)
    {
        $publicRelation = PublicRelation::first();
        $counter = 0;
        foreach ($publicRelation->getImage->getImage as $item) {
            $counter++;
        }

        return \App\Http\Controllers\Dashboard\response()->json($counter);
    }
}
