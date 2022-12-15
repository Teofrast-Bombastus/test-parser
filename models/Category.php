<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%category}}".
 *
 * @property int $category_id
 * @property string $name
 * @property string $date_added
 * @property string $date_updated
 * @property Product[] $product
 */

class Category extends \yii\db\ActiveRecord
{


    public static function tableName()
    {
        return '{{%category}}';
    }


    public function rules()
    {
        return [
            [['name'], 'string'],
            [['name'], 'string', 'max' => 256],
        ];
    }


    public function getProducts(){
        return self::hasMany(Product::className(), ['category_id' => 'category_id']);
    }

    public function getBudget(){
        return self::hasOne(CategoryBudget::className(), ['category_id' => 'category_id']);
    }

    public function fillBudget($budgetData) {

        $catBudget = $this->budget ?: new CategoryBudget();
        $catBudget->attributes = $budgetData;
        $catBudget->category_id = $this->category_id;
        $catBudget->save();

        return true;
    }

    static function getOrCreateCategory($name){
        $model = self::findOne(['name' => $name]);
        if(!$model) {
            $model = new self();
            $model->name = $name;
            $model->save();
        }
        return $model;
    }

}
