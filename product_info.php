<?php

namespace product_info;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Products extends Command
{
    protected function configure()
    {
        $this
            ->setName('product')
            ->setDescription('Product Info')
            ->addArgument(
                'run',
                InputArgument::IS_ARRAY,
                'Run Product Manager'
            )
            ->addOption(
                'createtables',
                null,
                InputOption::VALUE_NONE,
                'Create all tables'
            )
            ->addOption(
                'import',
                null,
                InputOption::VALUE_NONE,
                'Import data from file'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $data = new \product_data\ProductData();

        if ($input->getOption('createtables')) {
            $data->getDataManager()->getPDO()->exec("CREATE TABLE IF NOT EXISTS products (id INTEGER PRIMARY KEY, psku TEXT, pname TEXT, price REAL, quantity INTEGER)");
            $data->getDataManager()->getPDO()->exec("CREATE TABLE IF NOT EXISTS bundles (id INTEGER PRIMARY KEY, bsku TEXT)");
            $data->getDataManager()->getPDO()->exec("CREATE TABLE IF NOT EXISTS bundle_product (id INTEGER PRIMARY KEY, product_id INTEGER, bundle_id INTEGER)");
            $output->writeln("Tables created.");
        }

        $run = $input->getArgument('run');
        if (isset($run[0])) {
            $text = 'Operation: ' . $run[0];

            if ($run[0] == "import" && isset($run[1]) && ($handle = fopen($run[1], 'r'))) {
                $products = [];
                $bundles = [];
                $pi = 0;
                $bi = 0;

                $total_items = 0;
                $total_sellable = 0;
                $total_non_sellable = 0;
                $total_product_count = 0;

                while (($line = fgets($handle)) !== false) {
                    $row = explode("|", $line);

                    if ($row[1] == 'PRODUCT') {
                        $total_product_count++;
                        $p = array_filter(explode(",", $row[2]));
                        $product_info['psku'] = trim($row[0]);
                        $product_info['pname'] = trim($p[0]);
                        $product_info['price'] = trim($p[1]);
                        $product_info['quantity'] = trim($p[2]);

                        if(intval($product_info['quantity']) > 0) {
                            $total_sellable++;
                        }
                        else {
                            $total_non_sellable++;
                        }

                        $products[$pi] = $product_info;
                        $pi++;
                    } elseif ($row[1] == 'BUNDLE') {
                        $bundle_info['products'] = explode(",", $row[2]);
                        $bundle_info['info']['bsku'] = trim($row[0]);

                        $bundles[$bi] = $bundle_info;
                        $bi++;
                    }

                    $total_items++;
                }

                for ($i = 0; $i < count($products); $i++) {
                    $data->addProduct($data, $products[$i]);
                }

                foreach ($bundles as $bundle) {
                    $data->addBundle($data, $bundle['info'], $bundle['products']);
                }

                print("Total read items : " . $total_items . "\n");
                print("Total sellable products : " . $total_sellable . "\n");
                print("Total non-sellable products : " . $total_non_sellable . "\n");
                print("Total PRODUCT type items : " . $total_product_count . "\n");
            }
            elseif ($run[0] == "showProductWithSKU" && isset($run[1])) {
                $psku = $run[1];
                $product_details = $data->getProductBySKU($data, $psku);

                print("------------+----------------------+--------\n");
                print("        SKU |                 Name |  Price\n");
                print("------------+----------------------+--------\n");
                if(!$product_details) {
                    print("        INVALID PRODUCT SELECTED\n");
                }
                else {
                    printf("%11s | %20s | %7s\n", $product_details['psku'], $product_details['pname'], $product_details['price']);
                }
                print("------------+----------------------+--------\n");
            }
            elseif ($run[0] == "showBundleWithSKU" && isset($run[1])) {
                $bsku = $run[1];
                $bundle_details = $data->getBundleBySKU($data, $bsku);

                $product_details = $data->getProductsByBundleId($data, $bundle_details['id']);

                print("------------+----------------------+--------\n");
                print("        SKU |                 Name |  Price\n");
                print("------------+----------------------+--------\n");
                if(!$product_details) {
                    print("        INVALID PRODUCT SELECTED\n");
                }
                else {
                    printf("%11s | %20s | %7s\n", $bundle_details['bsku'], " ", " ");
                    $psum = 0;
                    foreach($product_details as $pd) {
                        $pdetails = $data->getProductById($data, $pd['product_id']);
                        printf("%11s | %20s | %7s\n", " ", $pdetails['pname'], " ");
                        $psum += $pdetails['price'];
                    }
                    printf("%11s | %20s | %7s\n", " ", " ", $psum);
                }
                print("------------+----------------------+--------\n");
            }

        } else {
            $text = 'Add arguments';
        }

        $output->writeln($text);
    }
}
