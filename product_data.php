<?php

namespace product_data;

class ProductData
{
    private $data_manager;

    private $message;

    function __construct()
    {
        $this->message = "";

        try {
            $data = new \PDO('sqlite:products.db');
            $this->data_manager = new \FluentPDO($data);
            $this->data_manager->debug = false;
        } catch (\Exception $ex) {
            $this->message = "Database connection failed : " . $ex->getMessage();
        }
    }

    function addProduct($app, $product)
    {
        if (empty($product)) {
            return false;
        }

        try {
            $query = $app->getDataManager()->insertInto('products')->values($product);
            $executed = $query->execute(true);
        } catch (\PDOException $ex) {
            print("Error : " . $ex->getMessage());
            return false;
        }

        return $executed;
    }

    function getProductBySKU($app, $psku)
    {
        if (!isset($psku)) {
            return false;
        }

        try {
            $query = $app->getDataManager()->from("products")
                ->where(array("psku" => $psku))
                ->fetch();
        } catch (\PDOException $ex) {
            print("Error : " . $ex->getMessage());
            return false;
        }

        return $query;
    }

    function getProductById($app, $pid) {
        if (!isset($pid)) {
            return false;
        }

        try {
            $query = $app->getDataManager()->from("products")
                ->where(array("id" => $pid))
                ->fetch();
        } catch (\PDOException $ex) {
            print("Error : " . $ex->getMessage());
            return false;
        }

        return $query;
    }

    function getBundleBySKU($app, $bsku)
    {
        if (!isset($bsku)) {
            return false;
        }

        try {
            $query = $app->getDataManager()->from("bundles")
                ->where(array("bsku" => $bsku))
                ->fetch();
        } catch (\PDOException $ex) {
            print("Error : " . $ex->getMessage());
            return false;
        }

        return $query;
    }

    function countInvalidBundles($app) {
        try {
            $query = $app->getDataManager()->from("products")
                ->leftJoin('bundle_product ON products.id = bundle_product.product_id')
                ->where(array("products.quantity" => 0))
                ->select(null)
                ->select("count(bundle_product.bundle_id) as count_invalid_bundle")
                ->fetchAll();
        } catch (\PDOException $ex) {
            print("Error : " . $ex->getMessage());
            return false;
        }

        return $query[0]["count_invalid_bundle"];
    }

    function addBundle($app, $bundle, $pskus)
    {
        if (empty($bundle)) {
            return false;
        }

        try {
            $query = $app->getDataManager()->insertInto('bundles')->values($bundle);
            $executed = $query->execute(true);

        } catch (\PDOException $ex) {
            print("Error : " . $ex->getMessage());
            return false;
        }

        try {
            foreach ($pskus as $p) {
                $bundle_product['product_id'] = $this->getProductBySKU($app, $p)['id'];
                $bundle_product['bundle_id'] = $this->getBundleBySKU($app, $bundle['bsku'])['id'];

                $query = $app->getDataManager()->insertInto('bundle_product')->values($bundle_product);
                $executed = $query->execute(true);
            }
        } catch (\PDOException $ex) {
            print("Error : " . $ex->getMessage());
            return false;
        }

        return $executed;
    }

    function getProductsByBundleId($app, $bundle_id)
    {
        if (!isset($bundle_id)) {
            return false;
        }

        try {
            $query = $app->getDataManager()->from("bundle_product")
                ->leftJoin('products ON products.id = bundle_product.product_id')
                ->where(array("bundle_product.bundle_id" => $bundle_id))
                ->fetchAll();
        } catch (\PDOException $ex) {
            print("Error : " . $ex->getMessage());
            return false;
        }

        return $query;
    }

    /**
     * @return FluentPDO
     */
    public function getDataManager()
    {
        return $this->data_manager;
    }

    /**
     * @param FluentPDO $data_manager
     * @return product_data
     */
    public function setDataManager($data_manager)
    {
        $this->data_manager = $data_manager;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return product_data
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

}