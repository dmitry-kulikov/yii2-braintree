<?php
/**
 * @author Anton Tuyakhov <atuyakhov@gmail.com>
 */

namespace tuyakhov\braintree;

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
