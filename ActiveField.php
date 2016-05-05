<?php
/**
 * @author Anton Tuyakhov <atuyakhov@gmail.com>
 */

namespace tuyakhov\braintree;

use yii\helpers\Inflector;

class ActiveField extends \yii\bootstrap\ActiveField
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $inputName = explode('_', $this->attribute);
        if (count($inputName) > 1) {
            $inputName[0] = $inputName[1];
        }
        $this->inputOptions = array_merge(
            [
                'data-braintree-name' => Inflector::underscore($inputName[0]),
                'autocomplete' => 'off',
            ],
            $this->inputOptions
        );
    }

    /**
     * @inheritdoc
     */
    public function widget($class, $config = [])
    {
        $config = array_merge(['options' => $this->inputOptions], $config);
        return parent::widget($class, $config);
    }
}
