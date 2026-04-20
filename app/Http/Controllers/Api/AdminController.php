<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    private function ensureAdmin(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return null;
    }

    public function users(Request $request)
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $perPage = $request->integer('per_page', 20);
        $users = User::with('vendor')->orderBy('name')->paginate($perPage);

        return response()->json([
            'data' => $users->getCollection()->map(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status,
                    'vendorId' => $user->vendor?->id,
                    'vendorApproved' => $user->vendor?->is_approved,
                ];
            }),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function categories(Request $request)
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        return response()->json(
            Category::query()->orderBy('name')->get(['id', 'name', 'slug'])
        );
    }

    public function storeCategory(Request $request)
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $data = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
        ]);

        $category = Category::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
        ]);

        return response()->json($category, 201);
    }

    public function updateCategory(Request $request, $id)
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $category = Category::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name,' . $category->id,
        ]);

        $category->update([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
        ]);

        return response()->json($category);
    }

    public function destroyCategory(Request $request, $id)
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json(['message' => 'Category deleted successfully.']);
    }

    public function approve(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $vendor = Vendor::findOrFail($id);
        $vendor->update(['is_approved' => true]);
        Cache::increment('vendors.cache.version');

        return response()->json(['message' => 'Vendor approved successfully.']);
    }

    public function reject(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $vendor = Vendor::findOrFail($id);
        $vendor->update(['is_approved' => false]);
        Cache::increment('vendors.cache.version');

        return response()->json(['message' => 'Vendor rejected successfully.']);
    }
}
