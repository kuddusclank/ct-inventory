<tr data-id="{{ $product['id'] }}"
    data-name="{{ $product['name'] }}"
    data-quantity="{{ $product['quantity'] }}"
    data-price="{{ number_format((float)$product['price'], 2, '.', '') }}">
    <td class="cell-name">{{ $product['name'] }}</td>
    <td class="cell-quantity text-end">{{ $product['quantity'] }}</td>
    <td class="cell-price text-end">${{ number_format((float)$product['price'], 2) }}</td>
    <td>{{ $product['created_at'] }}</td>
    <td class="text-end">${{ number_format($product['quantity'] * $product['price'], 2) }}</td>
    <td class="text-center">
        <button class="btn btn-sm btn-outline-primary btn-edit">
            <i class="bi bi-pencil me-1"></i>Edit
        </button>
    </td>
</tr>
