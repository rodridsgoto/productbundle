document.addEventListener('DOMContentLoaded', function () {
    loadBundleProducts();
    document.getElementById("product-search-input").addEventListener("keyup", searchProduct);
});
function whatProduct(check) {
    if (check.checked == true) {
        addProduct(check);
    } else {
        delProduct(check);
    }
}
function addProduct(check) {
    var bundles = document.getElementById("product-actually-bundles");
    var new_row = bundles.insertRow();
    new_row.insertCell(0).innerHTML = '<input type="checkbox" onchange="whatProduct(this)" name="bundle_products[]" value=' + check.value + ' checked>';
    new_row.insertCell(1).innerHTML = check.value;
    new_row.insertCell(2).innerHTML = "<img width='100px' src='"+check.parentNode.parentNode.children[2].firstElementChild.currentSrc+"'>";
    new_row.insertCell(3).innerHTML = check.parentNode.parentNode.children[3].innerText;
}
function delProduct(check) {
    var row = check.parentNode.parentNode;
    if (row.parentNode.id == "product-actually-bundles") {
        document.getElementById("product-actually-bundles").removeChild(row);
        var rows = document.getElementById("product-search-results").children;
        for (let i = 0; i < rows.length; i++) {
            const element = rows[i];
            if (check.value == element.children[1].innerText) {
                element.children[0].firstElementChild.checked = false;
            }
        }
    } else if (row.parentNode.id == "product-search-results") {
        var rows = document.getElementById("product-actually-bundles").children;
        var row_def = "";
        for (let i = 0; i < rows.length; i++) {
            const element = rows[i];
            if (check.value == element.children[1].innerText) {
                row_def = rows[i];
            }
        }
        document.getElementById("product-actually-bundles").removeChild(row_def);
    }
}
function loadBundleProducts() {
    var id_product = document.getElementById("id_product").value;
    var bundles = document.getElementById("product-actually-bundles");
    var res = "";
    $.ajax({
        type: "POST",
        url: document.getElementById("url_ajax").value,
        data: { 'action': 'load', 'valor': id_product },
        dataType: "json",
        success: function (response) {
            response.forEach(prod => {
                res += '<tr><td><input type="checkbox" onchange="whatProduct(this)" name="bundle_products[]" value=' + prod.id_product + ' checked></td><td>' + prod.id_product + '</td><td><img width="100px" src="' + prod.image + '"></td><td>' + prod.name + '</td></tr>';
            });
            bundles.innerHTML = res;
        },
    });
}
function searchProduct(e) {
    var res = '';
    if (e.target.value != "") {
        $.ajax({
            type: "POST",
            url: document.getElementById("url_ajax").value,
            data: { 'action': 'search', 'valor': e.target.value },
            dataType: "json",
            success: function (response) {
                response.forEach(prod => {
                    if (isBundleProduct(prod.id_product)) {
                        res += '<tr><td><input type="checkbox" onchange="whatProduct(this)" value=' + prod.id_product + ' checked></td><td>' + prod.id_product + '</td><td><img width="100px" src="' + prod.image + '"></td><td>' + prod.name + '</td></tr>';
                    } else {
                        res += '<tr><td><input type="checkbox" onchange="whatProduct(this)" value=' + prod.id_product + '></td><td>' + prod.id_product + '</td><td><img width="100px" src="' + prod.image + '"></td><td>' + prod.name + '</td></tr>';
                    }
                });
                document.getElementById("product-search-results").innerHTML = res;
            },
        });
    } else {
        document.getElementById("product-search-results").innerHTML = "";
    }
}
function isBundleProduct(id_product_bundle) {
    var es = false;
    var rows = document.getElementById("product-actually-bundles").children;
    for (let i = 0; i < rows.length; i++) {
        const element = rows[i];
        if (id_product_bundle == element.children[1].innerText) {
            es = true;
        }
    }
    return es;
}