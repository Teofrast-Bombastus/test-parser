<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%product}}".
 *
 * @property int $product_id
 * @property int $category_id
 * @property string $name
 * @property string $date_added
 * @property string $date_updated
 * @property Category $category
 */

class Product extends \yii\db\ActiveRecord
{


    public static function tableName()
    {
        return '{{%product}}';
    }


    public function rules()
    {
        return [
            [['category_id'], 'integer'],
            [['name'], 'string'],
            [['name'], 'string', 'max' => 256],
        ];
    }

    public function getCategory(){
        return self::hasOne(Category::className(), ['category_id' => 'category_id']);
    }

    public function getBudget(){
        return self::hasOne(ProductBudget::className(), ['product_id' => 'product_id']);
    }

    public function fillBudget($budgetData) {

        $prodBudget = $this->budget ?: new ProductBudget();
        $prodBudget->attributes = $budgetData;
        $prodBudget->product_id = $this->product_id;
        $prodBudget->save();

        return true;
    }

    static function getOrCreateProduct($name, $catId){
        $model = self::findOne(['name' => $name, 'category_id' => $catId]);
        if(!$model) {
            $model = new self();
            $model->name = $name;
            $model->category_id = $catId;
            $model->save();
        }
        return $model;
    }

}
