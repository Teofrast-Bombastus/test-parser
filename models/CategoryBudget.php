<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%category_budget}}".
 *
 * @property int $category_id
 * @property float $january
 * @property float $february
 * @property float $march
 * @property float $april
 * @property float $may
 * @property float $june
 * @property float $july
 * @property float $august
 * @property float $september
 * @property float $october
 * @property float $november
 * @property float $december
 * @property float $total
 * @property string $date_added
 * @property string $date_updated
 * @property Category $category
 */

class CategoryBudget extends \yii\db\ActiveRecord
{


    public static function tableName()
    {
        return '{{%category_budget}}';
    }


    public function rules()
    {
        return [
            [['category_id'], 'integer'],
            [['january','february','march','april','may','june','july','august','september','october','november','december','total'], 'double'],
        ];
    }

    public function getCategory(){
        return self::hasOne(Category::className(), ['category_id' => 'category_id']);
    }

}
