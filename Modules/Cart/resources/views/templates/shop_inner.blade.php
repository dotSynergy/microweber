{{--
Cart Add Module - Shop Inner Template
Type: layout
Name: Shop Inner
Description: Template designed for use within shop product layouts
--}}

<script>


</script>

<div class="product-info-layout-1">
    @if($for_id !== false && $for !== false)
        <div class="mw-add-to-cart-holder mw-add-to-cart-{{ $params['id'] ?? '' }}">
            @if(is_array($data))
                <input type="hidden" name="for" value="{{ $for }}"/>
                <input type="hidden" name="for_id" value="{{ $for_id }}"/>
            @endif

            @if(empty($data))
                <div class="mw-open-module-settings">
                    {{ _e('Click here to edit custom fields', true) }}
                </div>
            @else
                <module type="custom_fields" data-content-id="{{ intval($for_id) }}" data-skip-type="price" id="cart_fields_{{ $params['id'] ?? '' }}"/>

                @if(is_array($data))
                    <div class="price">
                        @foreach($data as $key => $v)
                            <div class="mw-price-item">
                                <div class="price-display mb-3">
                                    <strong class="price-value">{{ currency_format($v) }}</strong>
                                    @if(is_string($key) && trim(strtolower($key)) !== 'price')
                                        <span class="price-label">{{ $key }}</span>
                                    @endif
                                </div>

                                <div class="add-to-cart-actions d-flex align-items-center">
                                    <div class="mw-qty-field">
                                        <button type="button" onclick="mw.tools.decrease_quantity(this);">
                                            <i>−</i>
                                        </button>
                                        <input type="number" name="qty" value="1" min="1"/>
                                        <button type="button" onclick="mw.tools.increase_quantity(this);">
                                            <i>+</i>
                                        </button>
                                    </div>

                                    @if(!$in_stock)
                                        <button class="btn-default button-add-to-cart"
                                                type="button"
                                                disabled="disabled"
                                                onclick="mw.alert('{{ addslashes(_e("This item is out of stock and cannot be ordered", true)) }}');">
                                            <i class="icon-shopping-cart"></i>
                                            {{ _e("Out of stock", true) }}
                                        </button>
                                    @else
                                        <button class="btn-default button-add-to-cart"
                                                type="button"
                                                onclick="mw.cart.add_and_show_modal('{{ $for_id ?? '' }}','{{ $v }}', '{{ $title }}');">
                                            <i class="icon-shopping-cart"></i>
                                            {{ _e($button_text !== false ? $button_text : "Add to cart", true) }}
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
    @endif
</div>
