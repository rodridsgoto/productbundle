document.addEventListener('DOMContentLoaded', function () {
    document.querySelector(".wrapper.it_WEHFXCSSZDPT").style="width: 100% !important;";
    eliminarArray();
});
function eliminarArray() {
    var contenido = document.getElementById("array-de-las-narices").innerHTML;
    var new_content = new Array(contenido.split("\n").length);
    for (let i = 0; i < contenido.split("\n").length; i++) {
        const element = contenido.split("\n")[i];
        if(element.includes("Array")) {
            new_content[i] = element.replace("Array","");
        } else {
            new_content[i] = element;
        }
    }
    document.getElementById("array-de-las-narices").innerHTML = new_content.join("\n");
}
function calcularPrecio() {
    const form = document.getElementById('bundle-products');
    const totalPriceElement = document.getElementById('bundle-total-price');
    let total = 0;
    form.querySelectorAll('.bundle-item').forEach(function (item) {
        const price = parseFloat(item.querySelector('.bundle-item-price').innerText);
        const quantities = item.querySelectorAll('.bundle-quantity');
        var quantity = 0;
        for(let i = 0; i < quantities.length; i++) {
            if(quantities[i].value == "") {
                quantity += 0;    
            } else {
                quantity += parseInt(quantities[i].value);
            }
        }
        total += price * quantity;
    });
    totalPriceElement.textContent = total.toFixed(2) + " €";
}
function addCart() {
    const bundle_products = document.querySelectorAll(".bundle-item");
    var products = [];
    for(let i = 0; i < bundle_products.length; i++){
        const prod = bundle_products[i];
        var cantidades = [];
        var id_attributes = [];
        for (let j = 0; j < prod.children[3].children.length; j++) {
            const talla = prod.children[3].children[j];
            cantidades[j] = talla.children[1].value;
            id_attributes[j] = talla.children[1].id;
        }
        products[i] = {
            "id_product" : prod.id,
            "quantities" : cantidades,
            "id_attributes" : id_attributes
        };
    }
    $.ajax({
        type: "POST",
        url: document.getElementById("url_ajax").value,
        data: {'action' : 'addToCart', 'valor' : products},
        dataType: "json",
        success: function(response){
            console.log(response);
            if(response.success) {
                location.reload(true);
            }
        }
    });
}