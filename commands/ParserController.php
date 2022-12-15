<?php

namespace app\commands;

use app\models\Category;
use app\models\Product;
use yii\console\Controller;
use yii\helpers\Console;

class ParserController extends Controller
{

    const SHEET_ID = '10En6qNTpYNeY_YFTWJ_3txXzvmOA7UxSCrKfKCFfaRw';

    private $sheetData;

    public function actionCreateTables() {

        \Yii::$app->db->createCommand("
          CREATE TABLE `category` (
	        `category_id` INT(11) NOT NULL AUTO_INCREMENT,
	        `name` VARCHAR(256) NOT NULL DEFAULT '' COLLATE 'utf8mb4_general_ci',
          	`date_added` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          	`date_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	        PRIMARY KEY (`category_id`) USING BTREE
          ) COLLATE = 'utf8mb4_general_ci' ENGINE=InnoDB;
        ")->execute();

        \Yii::$app->db->createCommand("
          CREATE TABLE `product` (
          	`product_id` INT(11) NOT NULL AUTO_INCREMENT,
          	`category_id` INT(11) NOT NULL,
          	`name` VARCHAR(256) NOT NULL DEFAULT '' COLLATE 'utf8mb4_general_ci',
          	`date_added` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          	`date_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          	PRIMARY KEY (`product_id`) USING BTREE
          ) COLLATE='utf8mb4_general_ci' ENGINE=InnoDB;
        ")->execute();

        \Yii::$app->db->createCommand("
          CREATE TABLE `product_budget` (
        	`product_id` INT(11) NOT NULL,
        	`january` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
        	`february` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
        	`march` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
        	`april` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
        	`may` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
        	`june` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
        	`july` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
        	`august` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
        	`september` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
        	`october` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
        	`november` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
        	`december` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
        	`total` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
        	`date_added` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        	`date_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        	PRIMARY KEY (`product_id`) USING BTREE
          ) COLLATE='utf8mb4_general_ci' ENGINE=InnoDB;
        ")->execute();

        \Yii::$app->db->createCommand("
          CREATE TABLE `category_budget` (
        	`category_id` INT(11) NOT NULL,
        	`january` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
        	`february` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
        	`march` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
        	`april` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
        	`may` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
        	`june` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
        	`july` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
        	`august` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
        	`september` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
        	`october` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
        	`november` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
        	`december` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
        	`total` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
        	`date_added` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        	`date_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        	PRIMARY KEY (`category_id`) USING BTREE
          ) COLLATE='utf8mb4_general_ci' ENGINE=InnoDB;
        ")->execute();

    }

    public function actionStart() {

        try{

            $client = new \Google_Client();
            $client->setApplicationName('test Google Sheets API');
            $client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
            $client->setAccessType('offline');

            $path = \Yii::$app->basePath . DIRECTORY_SEPARATOR . 'google-key.json';
            $client->setAuthConfig($path);
            $service = new \Google_Service_Sheets($client);

            $this->parseSheet($service);
            $this->proceedData();

        }catch (\Exception $e){
            $this->stdout($e->getMessage(), Console::BG_RED);
            exit;
        }

        $this->stdout('success', Console::BG_GREEN);

    }

    private function parseSheet($service) {
        $staticNames = ['january','february','march','april','may','june','july','august','september','october','november','december','total'];
        $staticIndexes = [];

        // Assuming that months go on third line
        $sheet = 'MA!A3:Z3';
        $response = $service->spreadsheets_values->get(self::SHEET_ID, $sheet);
        $row = $response->getValues();
        foreach ($row[0] as $index => $value) {
            $value = strtolower($value);
            if(in_array($value, $staticNames)) {
                $staticIndexes[$value] = $index;
            }
        }

        $sheet = 'MA!A4:Z999';
        $response = $service->spreadsheets_values->get(self::SHEET_ID, $sheet);
        $rows = $response->getValues();

        $category = $product = '';
        foreach ($rows as $row) {

            // It's a category!
            if(count($row) === 1) {
                $category = $row[0];
                continue;
            }

            if(isset($row[0]) && $row[0]) {

                if(strtolower($row[0]) === 'co-op') {
                    break;
                }

                // It's a category total!
                if(strtolower($row[0]) === 'total') {
                    foreach($staticIndexes as $name => $index) {
                        if(isset($row[$index])) {
                            $this->sheetData[$category]['total'][$name] = (float)preg_replace('/[^.0-9]/', '', $row[$index]);
                        }
                    }
                    continue;
                }

                // It's a product!
                $product = $row[0];
                foreach($staticIndexes as $name => $index) {
                    if(isset($row[$index])) {
                        $this->sheetData[$category]['products'][$product][$name] = (float)preg_replace('/[^.0-9]/', '', $row[$index]);
                    }
                }

            }
        }
    }

    private function proceedData() {

        if(!empty($this->sheetData)) {

            $this->sheetData['Print']['products']['Washington Auto Show Program']['december'] = 150;
            $this->sheetData['Print']['products']['Washington Auto Show Program']['total'] = 150;
            $this->sheetData['Print']['total']['december'] = 150;
            $this->sheetData['Print']['total']['total'] = 150;

            $trans = \Yii::$app->db->beginTransaction();
            foreach($this->sheetData as $categoryName => $catData) {

                $categoryModel = Category::getOrCreateCategory($categoryName);

                if(isset($catData['total'])) {
                    $categoryModel->fillBudget($catData['total']);
                }

                if(isset($catData['products'])) {
                    foreach ($catData['products'] as $productName => $prodData) {

                        $productModel = Product::getOrCreateProduct($productName, $categoryModel->category_id);
                        $productModel->fillBudget($prodData);

                    }
                }

            }
            $trans->commit();
        }

    }
}
