<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Image;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function index()
    {
        $images = Image::where('user_id', Auth::id())->get();
        return response()->json($images);
    }

    public function show(Image $image)
    {
        return response()->json($image);
    }

    public function store(Request $request)
    {
        // Validasi request
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
            'produk_name' => 'required|string',
            'produk_price' => 'required|string',
            'description' => 'nullable|string',
        ]);

        // Proses upload file
        if ($request->file('image')) {
            // Menyimpan gambar ke folder 'uploads' di penyimpanan public
            $imagePath = $request->file('image')->store('uploads', 'public');
            $imageName = $request->file('image')->getClientOriginalName();

            // Mengambil data lain dari request
            $produk_name = $request->input('produk_name');
            $produk_price = $request->input('produk_price');
            $description = $request->input('description');

            // Membuat instance baru untuk model Image
            $image = new Image();
            $image->user_id = Auth::id(); // Menetapkan ID user yang sedang login
            $image->image_name = $imageName;
            $image->image_path = '/storage/' . $imagePath; // Menyimpan path gambar
            $image->produk_name = $produk_name;
            $image->produk_price = $produk_price;
            $image->description = $description;
            $image->save();

            // Mengembalikan response JSON dengan data yang berhasil disimpan
            return response()->json([
                'message' => 'Image and text uploaded successfully',
                'data' => $image
            ], 201);
        }

        // Jika upload gagal
        return response()->json([
            'message' => 'Image upload failed'
        ], 500);
    }

    public function update(Request $request, Image $image)
    {
        // Validasi form
        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'produk_name' => 'required|string',
            'produk_price' => 'required|string',
            'description' => 'required|string',
        ]);

        // Periksa jika ada file gambar yang diunggah
        if ($request->hasFile('image')) {

            // Unggah gambar baru
            $newImage = $request->file('image');
            $newImage->storeAs('public/uploads', $newImage->hashName());

            // Hapus gambar lama dari penyimpanan
            if ($image->image_path) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $image->image_path));
            }

            // Update data produk dengan gambar baru
            $image->update([
                'image_name' => $newImage->getClientOriginalName(),
                'image_path' => '/storage/uploads/' . $newImage->hashName(),
                'produk_name' => $request->input('produk_name'),
                'produk_price' => $request->input('produk_price'),
                'description' => $request->input('description')
            ]);

        } else {

            // Update data produk tanpa mengubah gambar
            $image->update([
                'produk_name' => $request->input('produk_name'),
                'produk_price' => $request->input('produk_price'),
                'description' => $request->input('description')
            ]);
        }

        // Kembalikan respons JSON dengan data yang diperbarui
        return response()->json([
            'success' => true,
            'message' => 'Data produk berhasil diperbarui',
            'data' => [
                'id' => $image->id,
                'produk_name' => $image->produk_name,
                'produk_price' => $image->produk_price,
                'description' => $image->description,
                'image_name' => $image->image_name,
                'image_path' => $image->image_path,
            ],
        ], 200);
    }

    public function destroy(Image $image)
    {
        $image->delete();
        return response()->json(null, 204);
    }
}
