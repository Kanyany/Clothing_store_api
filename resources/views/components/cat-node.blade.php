<div class="cat-node">
    <div class="cat-node-head">
        <div class="cat-node-icon">C</div>
        <div class="cat-node-main"><strong>{{ $node['name'] }}</strong><small>{{ $node['status'] ? 'Active' : 'Inactive' }} · {{ number_format($node['products_count'] ?? 0) }} products · {{ count($node['children']) }} child{{ count($node['children']) === 1 ? '' : 'ren' }}</small></div>
        <div class="cat-node-actions"><button onclick="openCategoryModal('edit',{{ $node['id'] }})">Edit</button><button class="danger" onclick="openCategoryModal('delete',{{ $node['id'] }})">Delete</button></div>
    </div>
    @if(count($node['children']))<div class="cat-children">@foreach($node['children'] as $child)<x-cat-node :node="$child" />@endforeach</div>@endif
</div>
