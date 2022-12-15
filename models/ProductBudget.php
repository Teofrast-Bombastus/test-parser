<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%product_budget}}".
 *
 * @property int $product_id
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
 * @property Product $product
 */

class ProductBudget extends \yii\db\ActiveRecord
{


    public static function tableName()
    {
        return '{{%product_budget}}';
    }


    public function rules()
    {
        return [
            [['product_id'], 'integer'],
            [['january','february','march','april','may','june','july','august','september','october','november','december','total'], 'double'],
        ];
    }

    public function getProduct(){
        return self::hasOne(Product::className(), ['product_id' => 'product_id']);
    }

}
