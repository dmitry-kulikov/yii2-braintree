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
        $view = $this->getView();
        BraintreeAsset::register($view);
        $id = $this->options['id'];
        /** @var Braintree $braintree */
        $braintree = Yii::$app->get('braintree');
        $clientToken = $braintree->getClientToken();
        $view->registerJs("braintree.setup('$clientToken', 'custom', {id: '$id'});");
        $this->fieldClass = ActiveField::className();
    }
}
