<?php

namespace tuyakhov\braintree\tests;

use tuyakhov\braintree\Braintree;
use Yii;

class UrlManagerTest extends TestCase
{
    /**
     * @var Braintree
     */
    protected $braintree;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->braintree = Yii::$app->get('braintree');
    }

    public function testUpdatePlan()
    {
        $this->assertEquals(
            "https://sandbox.braintreegateway.com/merchants/{$this->braintree->merchantId}/plans/test/edit",
            $this->braintree->urlManager->updatePlan('test')
        );
    }
}
