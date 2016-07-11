<?php
/**
 * @author Anton Tuyakhov <atuyakhov@gmail.com>
 */

namespace tuyakhov\braintree;

use Yii;

class ActiveForm extends \yii\bootstrap\ActiveForm
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        BraintreeAsset::register($this->getView());
        $this->fieldClass = ActiveField::className();
    }
}
