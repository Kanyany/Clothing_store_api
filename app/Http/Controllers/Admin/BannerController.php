<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Collection as ProductCollection;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(10);

        return view('admin.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.banners.create', $this->formOptions());
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules(true));

        // Title is optional.
        $validated['title'] = trim($validated['title'] ?? '');

        if (in_array(
            $validated['link_type'] ?? 'none',
            ['none', 'discount'],
            true
        )) {
            $validated['link_id'] = null;
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')
                ->store('banners', 'public');
        }

        Banner::create($validated);

        return redirect()
            ->route('admin.banners.index')
            ->with('success', 'Banner created successfully.');
    }

    public function edit(Banner $banner)
    {
        return view(
            'admin.banners.edit',
            array_merge(
                ['banner' => $banner],
                $this->formOptions()
            )
        );
    }

    public function update(Request $request, Banner $banner)
    {
        $validated = $request->validate(
            $this->validationRules(false)
        );

        // Title is optional.
        $validated['title'] = trim($validated['title'] ?? '');

        if (in_array(
            $validated['link_type'] ?? 'none',
            ['none', 'discount'],
            true
        )) {
            $validated['link_id'] = null;
        }

        if ($request->hasFile('image')) {
            $newImage = $request->file('image')
                ->store('banners', 'public');

            if ($banner->image) {
                Storage::disk('public')->delete($banner->image);
            }

            $validated['image'] = $newImage;
        }

        $banner->update($validated);

        return redirect()
            ->route('admin.banners.index')
            ->with('success', 'Banner updated successfully.');
    }

    public function destroy(Banner $banner)
    {
        if ($banner->image) {
            Storage::disk('public')->delete($banner->image);
        }

        $banner->delete();

        return redirect()
            ->route('admin.banners.index')
            ->with('success', 'Banner deleted successfully.');
    }

    public function toggleStatus(Banner $banner)
    {
        $banner->status = ! $banner->status;
        $banner->save();

        return back()->with(
            'success',
            'Banner status updated successfully.'
        );
    }

    private function formOptions(): array
    {
        return [
            'products' => Product::query()
                ->orderBy('name')
                ->get(['id', 'name']),

            'categories' => Category::query()
                ->orderBy('name')
                ->get(['id', 'name']),

            'collections' => ProductCollection::query()
                ->orderBy('name')
                ->get(['id', 'name']),
        ];
    }

    private function validationRules(bool $isCreate): array
    {
        $linkType = request()->input('link_type', 'none');

        $linkTable = match ($linkType) {
            'product'    => 'products',
            'category'   => 'categories',
            'collection' => 'collections',
            'discount',
            'none'       => null,
            default      => null,
        };

        $linkIdRules = ['nullable', 'integer'];

        if ($linkType !== 'none' && $linkType !== 'discount'
            && $linkTable !== null) {
            $linkIdRules = [
                'required',
                'integer',
                Rule::exists($linkTable, 'id'),
            ];
        }

        return [
            'title' => ['nullable', 'string', 'max:255'],

            'subtitle' => ['nullable', 'string', 'max:255'],

            'image' => $isCreate
                ? ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120']
                : ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],

            'link_type' => [
                'required',
                Rule::in([
                    'none',
                    'product',
                    'category',
                    'collection',
                    'discount',
                ]),
            ],

            'link_id' => $linkIdRules,

            'sort_order' => ['nullable', 'integer', 'min:0'],

            'status' => ['nullable', 'boolean'],
        ];
    }
}
