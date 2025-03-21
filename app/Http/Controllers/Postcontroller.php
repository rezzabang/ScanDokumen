<?php

namespace App\Http\Controllers;

use App\Exports\LaporanExport;
use App\Models\Images;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Intervention\Image\Facades\Image;


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

    public function store(Request $request)
    {
    $rules = [
        'nocm' => 'required|string|size:8',
        'nama' => 'required',
        'pelayanan' => 'required|not_in:Silahkan pilih..',
        'diagnosa' => 'required',
        'kunjungan' => 'required|string|size:10',
        'images' => 'array',
        'images.*' => 'image',

    ];

    $messages = [
        'nocm.required' => 'Nomor CM harus diisi.',
        'nocm.string' => 'Nomor CM harus berupa karakter.',
        'nocm.size' => 'Nomor CM harus terdiri dari 8 karakter.',
        'nama.required' => 'Nama harus diisi.',
        'pelayanan.required' => 'Jenis Pelayanan harus dipilih.',
        'pelayanan.not_in' => 'Jenis Pelayanan harus dipilih.',
        'diagnosa.required' => 'Silahkan masukkan diagnosa.',
        'kunjungan.required' => 'Tanggal kunjungan harus diisi.',
        'kunjungan.string' => 'Tanggal kunjungan harus diisi.',
        'kunjungan.size' => 'Tanggal kunjungan harus sesuai (dd/mm/yyyy).',
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
            "diagnosa" => $request->diagnosa,
            "sctid" => $request->sctid,
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

                Images::create($validatedData);
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
            'diagnosa' => 'required',
            'kunjungan' => 'required|string|size:10',
        ];

        $messages = [
            'nocm.required' => 'Nomor CM harus diisi.',
            'nocm.string' => 'Nomor CM harus berupa karakter.',
            'nocm.size' => 'Nomor CM harus terdiri dari 8 karakter.',
            'nama.required' => 'Nama harus diisi.',
            'pelayanan.required' => 'Jenis Pelayanan harus dipilih.',
            'pelayanan.not_in' => 'Jenis Pelayanan harus dipilih.',
            'diagnosa.required' => 'Masukkan diagnosa.',
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
            "diagnosa" => $request->diagnosa,
            "sctid" => $request->sctid,
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

                Images::create($validatedData);
            }
        }

        return redirect("/");
    }

    public function deleteimage($id) {
        $image = Images::findOrFail($id);
        $imagePath = 'public/post-img/' . $image->image;

        if (Storage::exists($imagePath)) {
            Storage::delete($imagePath);
        }

        Images::find($id)->delete();
        return back();
    }

    public function destroy($id) {
        $post = Post::findOrFail($id);

        $images = Images::where("post_id", $post->id)->get();

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
            ->orWhere('sctid','like',"%$search%")
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

    public function rotate(Request $request)
    {
        $imagePath = 'public/post-img/' . $request->image;
        $fullImagePath = storage_path('app/' . $imagePath);

        if (!Storage::exists($imagePath)) {
            abort(404, 'Image not found');
        }

        $image = Image::make($fullImagePath);
        $image->rotate(-90);

        $image->save($fullImagePath);

        return back()->with('success', 'Image rotated successfully');
    }

    public function apiSnomed($searchTerm)
    {
        $apiUrl = config('services.apiSnomed.url');
        $urlTerm = $apiUrl."MAIN/concepts?activeFilter=true&term=".$searchTerm."&termActive=true&includeLeafFlag=false&form=inferred&offset=0&limit=7";
        $responseTerm = Http::get($urlTerm);

        if ($responseTerm->successful() && !empty($responseTerm->json()['items'])) {
            return response()->json([
                'source' => 'concepts',
                'items' => collect($responseTerm->json()['items'])->map(function ($item) {
                    return [
                        'fsn_term' => $item['fsn']['term'],
                        'sctid' => $item['conceptId'],
                    ];
                }),
            ]);
        }

        $urlIcd = $apiUrl."MAIN/members?referenceSet=447562003&active=true&mapTarget=".$searchTerm."&limit=7";
        $responseIcd = Http::get($urlIcd);

        if ($responseIcd->successful() && !empty($responseIcd->json()['items'])) {
            return response()->json([
                'source' => 'members',
                'items' => collect($responseIcd->json()['items'])->map(function ($item) {
                    return [
                        'fsn_term' => $item['referencedComponent']['fsn']['term'],
                        'sctid' => $item['referencedComponent']['conceptId'],
                    ];
                }),
            ]);
        }

        return response()->json(['items' => []]);
    }
}
