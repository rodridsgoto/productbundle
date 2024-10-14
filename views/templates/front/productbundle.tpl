<input id="url_ajax" type="hidden" value="{$url_ajax}">
<div class="product-bundle">
    <div class="bundle-div">
        <div id="bundle-products">
            {foreach from=$bundle_products item=product}
                <div class="bundle-item" id="{$product.id_product}">
                    <div style="width: 100%; margin-bottom: 5px;">
                        <img width="300px" height="100%" src="{$product.image}"/>
                    </div>
                    <h5><strong>{$product.name}</strong></h5>
                    <div><span class="bundle-item-price">{$product.price} €</span><span></span></div>
                    <div class="bundle-tallas">
                    {foreach from=$product.sizes item=size}
                        <div class="bundle-talla">
                            <label>Talla {$size.name}</label>
                            <input id="{$size.id_attribute}" placeholder="Cantidad por talla" class="bundle-quantity" type="number" oninput="calcularPrecio(this)">
                        </div>
                    {/foreach}
                    </div>
                </div>
            {/foreach}
        </div>
    </div>
    <div class="row bundle-add-cart">
        <button class="btn btn-primary" onclick="addCart()" style="font-size: 1.100rem !important;"><strong>Añadir al carro</strong></button>
        <div style="display: flex; justify-content: center; align-items: center;">
            <span id="bundle-total-price" itemprop="price">{$product.price}</span>
        </div>
    </div>
</div>