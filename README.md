# Product Info Manager

CLI tool for product information management

## Requirement

PHP 5.6

## Usage

For simplicity, SQLite3 connection is set. To Creating database tables,

```
$ php app.php product --createtables
```

To import product information from files,

```
$ php app.php product import <file_path>
```

To view product details,

```
$ php app.php product showProductWithSKU <product_SKU>
```

To view bundle details,

```
$ php app.php product showBundleWithSKU <bundle_SKU>
```

## Examples

```
$ php app.php product --createtables
Tables created.
No procedure.
```


```
$ php app.php product import ../producto/product_list_2.dat
Total read items : 10000
Total sellable products : 7629
Total non-sellable products : 388
Total PRODUCT type items : 8017
Total BUNDLE type items : 1983
Total Invalid Bundles : 146
Operation: import
```


```
$ php app.php product showProductWithSKU PRD0002
------------+----------------------+--------
        SKU |                 Name |  Price
------------+----------------------+--------
        INVALID PRODUCT SELECTED
------------+----------------------+--------
Operation: showProductWithSKU
```


```
$ php app.php product showBundleWithSKU PRD00261
------------+----------------------+--------
        SKU |                 Name |  Price
------------+----------------------+--------
   PRD00261 |                      |
            |         White stairs |
            |       Plastic wrench |
            |      So plastic book |
            |                      |
            |                      |  142.97
------------+----------------------+--------
Operation: showBundleWithSKU
```

```
$ php app.php product showProductWithSKU PRD00009
------------+----------------------+--------
        SKU |                 Name |  Price
------------+----------------------+--------
   PRD00009 |          Windy shirt |   20.99
------------+----------------------+--------
Operation: showProductWithSKU
```
