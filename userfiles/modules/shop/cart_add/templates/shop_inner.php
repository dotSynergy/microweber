<?php

/*

type: layout

name: Shop Inner

description: Shop Inner

*/
?>
<?php
$for_id = $for_id ?? false;
if (!isset($params['content-id'])) {
    if(content_id()){
        $for_id = content_id();
    }
} else {
    $for_id = $params['content-id'];
}

if ($for_id) {
    $product = get_content_by_id($for_id);
    $title = $product['title'];
} else {
    $title = _e("Product", true);
}

?>

<br class="mw-add-to-cart-spacer"/>

<module type="custom_fields" template="bootstrap5_flex" data-content-id="<?php print intval($for_id); ?>" data-skip-type="price" id="cart_fields_<?php print $params['id'] ?>"/>
<?php if (is_array($data)): ?>
    <div class="price">
        <?php $i = 1;

        foreach ($data as $key => $v): ?>
            <div class="mw-price-item d-flex align-items-center justify-content-between ">


                <?php

                $keyslug_class = str_slug(strtolower($key));


                // $key = $price_offers[$key]['offer_price'];

                ?>


                <div class="price-holder">

                    <h5 class="mb-0 price"><?php print currency_format($v); ?></h5>
                </div>


                <?php if (!isset($in_stock) or $in_stock == false) : ?>
                    <button class="btn btn-default pull-right" type="button" disabled="disabled"
                            onclick="mw.alert('<?php print addslashes(_e("This item is out of stock and cannot be ordered", true)); ?>');">
                        <i class="icon-shopping-cart glyphicon glyphicon-shopping-cart"></i>
                        <?php _e("Out of stock"); ?>
                    </button>
                <?php else: ?>


                    <button class="btn btn-primary pull-right" type="button"
                            onclick="mw.cart.add('.mw-add-to-cart-<?php print $params['id'] ?>','<?php print $v ?>', '<?php print $title; ?>');">
                        <i class="icon-shopping-cart glyphicon glyphicon-shopping-cart"></i>
                        <?php _e($button_text !== false ? $button_text : "Add to cart"); ?>
                    </button>


                    <?php $i++; endif; ?>


            </div>
            <?php if ($i > 1) : ?>
                <br/>
            <?php endif; ?>
            <?php $i++; endforeach; ?>
    </div>
<?php endif; ?>

<?php
$digitalLinks = [];
    dd(is_logged());
if (is_logged() && isset($for_id) && $for_id) {
    $downloads = \MicroweberPackages\Digital\Models\DigitalDownload::query()
        ->where('product_id', (int) $for_id)
        ->where('user_id', user_id())
        ->whereHas('order', function ($query) {
            $query->where('is_paid', 1);
        })
        ->orderBy('id', 'desc')
        ->get();

    foreach ($downloads as $download) {
        if ($download->isAvailable()) {
            $digitalLinks[] = route('digital.download', ['token' => $download->token]);
        }
    }
}
?>

<?php if (!empty($digitalLinks)): ?>
    <div class="mw-digital-downloads mt-3">
        <strong><?php _e("Your download"); ?></strong>
        <ul class="mb-0 mt-2">
            <?php foreach ($digitalLinks as $digitalLink): ?>
                <li><a href="<?php print $digitalLink; ?>"><?php _e("Download file"); ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
