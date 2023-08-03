<?php

namespace App\Http\Controllers;

use App\Exports\LaporanExport;
use App\Models\Image;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class Postcontroller extends Controller
{
    public function index()
    {
        return view('index');
    }

    public function create()
    {
        return view('create');
    }

    // public function store(Request $request)
    // {
    //     $post =new Post([
    //         "nocm" =>$request->nocm,
    //         "user" =>$request->user,
    //         "nama" =>$request->nama,
    //         "pelayanan" => $request->pelayanan,
    //         "kunjungan" =>$request->kunjungan,
    //     ]);
    //    $post->save();

    //     if ($request->hasFile("images")) {
    //         $files = $request->file("images");

    //         foreach ($files as $file) {
    //             $extension = $file->getClientOriginalExtension();
    //             $imageName = $request->kunjungan . $request->nocm . '.' . $extension;
    //             $imageName = str_replace(['/','-'],'', $imageName);
    //             $imageName = $this->makeUniqueImageName($imageName);
    //             $file->storeAs('public/post-img/', $imageName);
    //             $validatedData['image'] = $imageName;
    //             $validatedData['post_id'] = $post->id;

    //             Image::create($validatedData);
    //         }
    //     }

    //     return redirect("/");
    // }

    public function store(Request $request)
    {
    $rules = [
        'nocm' => 'required|string|size:8',
        'nama' => 'required',
        'pelayanan' => 'required|not_in:Silahkan pilih..',
        'kunjungan' => 'required|string|size:10',
        'images' => 'required|image',
    ];

    $messages = [
        'nocm.required' => 'Nomor CM harus diisi.',
        'nocm.string' => 'Nomor CM harus berupa karakter.',
        'nocm.size' => 'Nomor CM harus terdiri dari 8 karakter.',
        'nama.required' => 'Nama harus diisi.',
        'pelayanan.required' => 'Jenis Pelayanan harus dipilih.',
        'pelayanan.not_in' => 'Jenis Pelayanan harus dipilih.',
        'kunjungan.required' => 'Tanggal kunjungan harus diisi.',
        'kunjungan.string' => 'Tanggal kunjungan harus diisi.',
        'kunjungan.size' => 'Tanggal kunjungan harus sesuai (dd/mm/yyyy).',
        'images.required' => 'Harus melampirkan gambar.',
        'images.image' => 'File harus berupa gambar.',
    ];

    $validator = Validator::make($request->all(), $rules, $messages);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }
        $post = new Post([
            "nocm" => $request->nocm,
            "user" => $request->user,
            "nama" => $request->nama,
            "pelayanan" => $request->pelayanan,
            "kunjungan" => $request->kunjungan,
        ]);

        $post->save();

        if ($request->hasFile("images")) {
            $files = $request->file("images");

            foreach ($files as $file) {
                $extension = $file->getClientOriginalExtension();
                $imageName = $request->kunjungan . $request->nocm . '.' . $extension;
                $imageName = str_replace(['/','-'],'', $imageName);
                $imageName = $this->makeUniqueImageName($imageName);
                $file->storeAs('public/post-img/', $imageName);
                $validatedData['image'] = $imageName;
                $validatedData['post_id'] = $post->id;

                Image::create($validatedData);
            }
        }

        return back();
    }

    private function makeUniqueImageName($imageName)
    {
        $name = pathinfo($imageName, PATHINFO_FILENAME);
        $extension = pathinfo($imageName, PATHINFO_EXTENSION);
        $number = 1;

        while (Storage::exists('public/post-img/' . $imageName)) {
            $imageName = $name . '-' . $number . '.' . $extension;
            $number++;
        }

        return $imageName;
    }

    public function edit($id)
    {
        $posts=Post::findOrFail($id);
        return view('edit')->with('posts',$posts);
    }

    public function view($id)
    {
        $posts=Post::findOrFail($id);
        return view('view')->with('posts',$posts);
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'nocm' => 'required|string|size:8',
            'nama' => 'required',
            'pelayanan' => 'required|not_in:Silahkan pilih..',
            'kunjungan' => 'required|string|size:10',
        ];
    
        $messages = [
            'nocm.required' => 'Nomor CM harus diisi.',
            'nocm.string' => 'Nomor CM harus berupa karakter.',
            'nocm.size' => 'Nomor CM harus terdiri dari 8 karakter.',
            'nama.required' => 'Nama harus diisi.',
            'pelayanan.required' => 'Jenis Pelayanan harus dipilih.',
            'pelayanan.not_in' => 'Jenis Pelayanan harus dipilih.',
            'kunjungan.required' => 'Tanggal kunjungan harus diisi.',
            'kunjungan.string' => 'Tanggal kunjungan harus diisi.',
            'kunjungan.size' => 'Tanggal kunjungan harus sesuai (dd/mm/yyyy).',
        ];
    
        $validator = Validator::make($request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $post = Post::findOrFail($id);

        $post->update([
            "nocm" => $request->nocm,
            "user" => $request->user,
            "nama" => $request->nama,
            "pelayanan" => $request->pelayanan,
            "kunjungan" => $request->kunjungan,
        ]);

        if ($request->hasFile("images")) {
            $files = $request->file("images");
            foreach ($files as $file) {
                $imageName = str_replace(['', '-','/' ], '', $request->kunjungan) . $request->nocm . '.' . $file->getClientOriginalExtension();
                $imageName = $this->makeUniqueImageName($imageName);
                $file->storeAs('public/post-img/', $imageName);
                $validatedData['image'] = $imageName;
                $validatedData['post_id'] = $post->id;

                Image::create($validatedData);
            }
        }

        return redirect("/");
    }

    public function deleteimage($id) {
        $image = Image::findOrFail($id);
        $imagePath = 'public/post-img/' . $image->image;
        
        if (Storage::exists($imagePath)) {
            Storage::delete($imagePath);
        }
    
        Image::find($id)->delete();
        return back();
    }

    public function destroy($id) {
        $post = Post::findOrFail($id);
        
        $images = Image::where("post_id", $post->id)->get();
        
        foreach ($images as $image) {
            $imagePath = 'public/post-img/' . $image->image;
            
            if (Storage::exists($imagePath)) {
                Storage::delete($imagePath);
            }
            $image->delete();
        }
        
        $post->delete();
        
        return back();
    }

    public function search(Request $request){
        
        $search = $request->search;
        session(['search' => $search]);
        $posts = Post::where(function($query) use ($search){

            $query->where('nocm','like',"%$search%")
            ->orWhere('nama','like',"%$search%")
            ->orWhere('user','like',"%$search%")
            ->orWhere('pelayanan','like',"%$search%")
            ->orWhere('kunjungan','like',"%$search%");
            })->paginate(10);
            
            return view('search',compact('posts','search'));
    }



    public function exportLaporan(Request $request)
    {
        $search = session('search');
        $request->session()->forget('search');
        $fileName = 'Laporan_' . Carbon::now()->format('Ymd') . '.xlsx';
        return (new LaporanExport($search))->download($fileName);
    }
}