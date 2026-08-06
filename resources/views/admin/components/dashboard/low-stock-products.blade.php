<div class="card h-100 border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0">Low Stock Products</h5>
    </div>
    <div class="card-body p-0">
        @if($lowStockProducts->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <tbody>
                        @foreach($lowStockProducts as $product)
                            <tr>
                                <td class="ps-3">
                                    <a href="{{ route('admin.products.edit', $product) }}" class="fw-bold text-dark text-decoration-none">{{ Str::limit($product->name, 35) }}</a>
                                    <div class="small text-muted">SKU: {{ $product->sku }}</div>
                                </td>
                                <td class="text-end pe-3">
                                    <span class="badge bg-danger">Stock: {{ $product->stock }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-4 text-center text-muted">No products are low on stock.</div>
        @endif
    </div>
</div>
