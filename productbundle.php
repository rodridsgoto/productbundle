<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class ProductBundle extends Module
{
    public function __construct()
    {
        $this->name = 'productbundle';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Rodrigo';
        $this->need_instance = 0;
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('Product Bundle');
        $this->description = $this->l('Permite crear productos compuestos a partir de otros productos.');
    }

    public function install()
    {
        return parent::install() &&
            $this->registerHook('displayProductExtraContent') &&
            $this->registerHook('displayAdminProductsMainStepLeftColumnBottom') &&
            $this->registerHook('actionProductSave') &&
            $this->registerHook('displayHeader') &&
            $this->registerHook('displayBackOfficeHeader') &&
            $this->createDatabaseTables();
    }

    public function uninstall()
    {
        return parent::uninstall() && $this->dropDatabaseTables();
    }

    private function createDatabaseTables()
    {
        $sql = "ALTER TABLE `" . _DB_PREFIX_ . "product` ADD `is_composite` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0";
        $sql .= "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "product_bundle` (
            `id_product` INT(11) NOT NULL,
            `id_bundle_product` INT(11) NOT NULL,
            PRIMARY KEY (`id_product`, `id_bundle_product`)
        ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8;";

        return Db::getInstance()->execute($sql);
    }

    private function dropDatabaseTables()
    {
        $sql = "ALTER TABLE `" . _DB_PREFIX_ . "product` DROP COLUMN `is_composite`";
        $sql .= "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "product_bundle`";

        return Db::getInstance()->execute($sql);
    }

    public function hookDisplayAdminProductsMainStepLeftColumnBottom($params)
    {
        $id_product = (int)$params['id_product'];
        $isCompositeProduct = Db::getInstance()->getValue('SELECT is_composite FROM '._DB_PREFIX_.'product WHERE id_product = ' . (int)$id_product);
        $bundleProducts = Db::getInstance()->executeS('SELECT id_bundle_product FROM '._DB_PREFIX_.'product_bundle WHERE id_product = ' . (int)$id_product);
        $ajax = 'https://disfrazdisfraz.com/modules/'.$this->name.'/ajax.php?token='.Tools::encrypt($this->name.'/ajax.php');
        $this->context->smarty->assign([
            'is_composite' => $isCompositeProduct,
            'bundle_products' => $bundleProducts,
            'id_product' => $id_product,
            'products' => Product::getProducts($this->context->language->id, 0, 0, 'name', 'ASC'),
            'url_ajax' => $ajax
        ]);
        return $this->display(__FILE__, 'views/templates/admin/productbundle_admin.tpl');
    }

    public function hookActionProductSave($params)
    {
        $id_product = (int)$params['id_product'];
        $isComposite = Tools::getValue('is_composite') ? 1 : 0;
        $bundleProducts = Tools::getValue('bundle_products');

        Db::getInstance()->update('product', ['is_composite' => (int)$isComposite], 'id_product = ' . (int)$id_product);
        Db::getInstance()->delete('product_bundle', 'id_product = ' . (int)$id_product);

        if ($isComposite && !empty($bundleProducts)) {
            foreach ($bundleProducts as $bundleProduct) {
                Db::getInstance()->insert('product_bundle', [
                    'id_product' => (int)$id_product,
                    'id_bundle_product' => (int)$bundleProduct,
                ]);
            }
        }
    }

    public function hookDisplayProductExtraContent($params)
    {
        $id_product = (int)Tools::getValue('id_product');
        $ajax = 'https://disfrazdisfraz.com/modules/'.$this->name.'/ajax.php?token='.Tools::encrypt($this->name.'/ajax.php');
        if ($this->isCompositeProduct($id_product) == 1) {
            $bundleProducts = $this->getBundleProducts($id_product);
            if (!empty($bundleProducts)) {
                $this->context->smarty->assign([
                    'bundle_products' => $this->getBundleProducts($id_product),
                    'url_ajax' => $ajax
                ]);
                return $this->fetch('module:productbundle/views/templates/front/productbundle.tpl');
            }
        }
        return '';
    }
    public function hookDisplayHeader($params)
    {
        $this->context->controller->addJS($this->_path.'views/js/productbundle.js');
        $this->context->controller->addCSS($this->_path.'views/css/productbundle.css');
    }
    public function hookDisplayBackOfficeHeader()
    {
        $this->context->controller->addJquery();
        $this->context->controller->addJS($this->_path.'views/js/productbundle_admin.js');
        $this->context->controller->addCSS($this->_path.'views/css/productbundle_admin.css');
    }
    private function isCompositeProduct($id_product)
    {
        return Db::getInstance()->getValue('SELECT is_composite FROM '._DB_PREFIX_.'product WHERE id_product = ' . (int)$id_product);
    }
    public function getBundleProducts($id_product)
    {
        $sql = Db::getInstance()->executeS('SELECT p.id_product, pl.name, p.price
                FROM '._DB_PREFIX_.'product p
                INNER JOIN '._DB_PREFIX_.'product_bundle pb ON p.id_product = pb.id_bundle_product
                INNER JOIN '._DB_PREFIX_.'product_lang pl ON p.id_product = pl.id_product
                WHERE pb.id_product = ' . (int)$id_product);
        $result = [];
        foreach($sql as $row) {
            $consulta = Db::getInstance()->executeS('SELECT pa.id_product_attribute,al.name AS attribute_name
                                                    FROM '._DB_PREFIX_.'product_attribute pa
                                                    JOIN '._DB_PREFIX_.'product_attribute_combination pac ON pa.id_product_attribute = pac.id_product_attribute
                                                    JOIN '._DB_PREFIX_.'attribute a ON pac.id_attribute = a.id_attribute
                                                    JOIN '._DB_PREFIX_.'attribute_lang al ON a.id_attribute = al.id_attribute
                                                    JOIN '._DB_PREFIX_.'attribute_group ag ON a.id_attribute_group = ag.id_attribute_group
                                                    JOIN '._DB_PREFIX_.'attribute_group_lang agl ON ag.id_attribute_group = agl.id_attribute_group
                                                    WHERE pa.id_product = '.(int)$row['id_product'].' AND al.id_lang = 4 AND agl.id_lang = 4;');
            $price = 0.00;
            if((float)$row['price'] == 0) {
                $price = ((float) Db::getInstance()->getValue('select pa.price from '._DB_PREFIX_.'product_attribute pa where pa.id_product = '.(int)$row['id_product'].';')) * 1.21;
            } else {
                $price = (float)$row['price'];
            }
            $sizes = [];
            foreach($consulta as $size) {
                $sizes[] = [
                    'id_attribute' => (int)$size['id_product_attribute'],
                    'name' => pSQL($size['attribute_name'])
                ];
            }
            $ids_image = Db::getInstance()->executeS('SELECT pi.id_image
                                                        FROM '._DB_PREFIX_.'image pi
                                                        WHERE pi.id_product = '.(int)$row['id_product'].' and pi.cover = 1;');
            $ruta = "https://disfrazdisfraz.com/" . $ids_image[0]['id_image'] . "-medium_default/".implode("-", explode(" ",strtolower($row['name']))).".jpg";
            $result[] = [
                'id_product' => (int)$row['id_product'],
                'name' => $row['name'],
                'price' => number_format(round($price,2), 2),
                'stock' => (int)Db::getInstance()->getValue('SELECT sa.quantity
                                                            FROM '._DB_PREFIX_.'stock_available sa
                                                            JOIN '._DB_PREFIX_.'product p ON sa.id_product = p.id_product
                                                            WHERE p.id_product = '.(int)$row['id_product'].';'),
                'sizes' => $sizes,
                'image' => $ruta
            ];
        }
        return $result;
    }
    public function searchProducts($search)
    {
        $search = str_replace(' ', '%', $search);
        $result = [];
        $products = Db::getInstance()->executeS('SELECT id_product, `name`
                                                    FROM '._DB_PREFIX_.'product_lang
                                                    WHERE id_lang = '.(int)$this->context->language->id.'
                                                    AND `name` LIKE "%'.$search.'%" LIMIT 12;');
        foreach ($products as $prod) {
            $ids_image = Db::getInstance()->executeS('SELECT pi.id_image
                                                    FROM '._DB_PREFIX_.'image pi
                                                    WHERE pi.id_product = '.(int)$prod['id_product'].' and pi.cover = 1;');
            $ruta = "https://disfrazdisfraz.com/" . $ids_image[0]['id_image'] . "-medium_default/".implode("-", explode(" ",strtolower($prod['name']))).".jpg";
            $result[] = [
                'id_product' => (int)$prod['id_product'],
                'name' => pSQL($prod['name']),
                'image' => $ruta
            ];
        }
        return $result;
    }
    public function addBundleProductsToCart($products) {
        $context = Context::getContext();
        $cart = $context->cart;
        if (!isset($cart->id) || !$cart->id) {
            $cart = new Cart();
            $cart->id_shop = $context->shop->id;
            $cart->id_lang = $context->language->id;
            $cart->id_currency = $context->currency->id;
            $cart->id_guest = $context->customer->isLogged() ? 0 : $context->cookie->id_guest;
            $cart->id_customer = $context->customer->isLogged() ? $context->customer->id : 0;
            $cart->id_carrier = 0;
            $cart->recyclable = 0;
            $cart->gift = 0;
            $cart->add();
            $context->cart = $cart;
            $context->cookie->id_cart = $cart->id;
            $context->cookie->write();
        }
        foreach ($products as $product) {
            $id_product = (int)$product['id_product'];
            for ($i=0; $i < sizeof($product["id_attributes"]); $i++) { 
                $cart->updateQty(
                    $product["quantities"][$i],
                    $id_product,
                    $product["id_attributes"][$i],
                    'up'
                );
            }
        }
        $cart->save();
        $response = [
            'success' => true,
            'cart_total' => $this->context->cart->getOrderTotal(false),
            'products' => $this->context->cart->getProducts()
        ];
        return $response;
    }
}
?>