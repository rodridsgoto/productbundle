<input type="hidden" id="url_ajax" value="{$url_ajax|escape:'html':'UTF-8'}">
<input type="hidden" id="id_product" value="{$id_product}">
<div class="form-group">
    <h2>Producto Compuesto</h2>
    <div class="col-lg-9">
        <input type="checkbox" name="is_composite" value="1" {if $is_composite}checked{/if}>&ensp;Es un producto compuesto.
    </div>
</div>
<div id="bundle-products" {if !$is_composite}style="display:none"{/if}>
    <div id="product-bundles-container" style="display: block; margin-bottom: 14px;">
        <h3>Productos que ya componen</h3>
        <table class="table">
            <thead class="thead-default">
                <th style="width: auto;">Incluido</th>
                <th>ID</th>
                <th>Imagen</th>
                <th>Producto</th>
            </thead>
            <tbody id="product-actually-bundles"></tbody>
        </table>
    </div>
    <div id="product-search-container">
        <h3>Selecciona los productos para el conjunto</h3>
        <div class="searcher">
            <input type="text" id="product-search-input" class="form-control search" placeholder="Buscar productos...">
        </div>
        <table class="table">
            <thead class="thead-default">
                <th style="width: auto;">Incluir</th>
                <th>ID</th>
                <th>Imagen</th>
                <th>Producto</th>
            </thead>
            <tbody id="product-search-results"></tbody>
        </table>
    </div>
</div>