<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function create(Event $event): View
    {
        return view('admin.products.create', [
            'event' => $event,
        ]);
    }

    public function store(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);

        $path = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('product-images', 'public');
        }

        $event->products()->create([
            'name' => $validated['name'],
            'image_path' => $path,
        ]);

        return redirect()->route('admin.events.show', $event)->with('status', 'Product created.');
    }

    public function edit(Event $event, Product $product): View
    {
        return view('admin.products.edit', [
            'event' => $event,
            'product' => $product,
        ]);
    }

    public function update(Request $request, Event $event, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);

        $product->name = $validated['name'];

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $product->image_path = $request->file('image')->store('product-images', 'public');
        }

        $product->save();

        return redirect()->route('admin.events.show', $event)->with('status', 'Product updated.');
    }

    public function destroy(Event $event, Product $product): RedirectResponse
    {
        if ($product->vouchers()->exists()) {
            return back()->withErrors(['product' => 'Cannot delete product because it has associated vouchers.']);
        }

        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return redirect()->route('admin.events.show', $event)->with('status', 'Product deleted.');
    }
}
