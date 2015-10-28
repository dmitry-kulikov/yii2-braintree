<?php
/**
 * @author Anton Tuyakhov <atuyakhov@gmail.com>
 */

namespace tuyakhov\braintree\tests;

use Braintree\Customer;
use tuyakhov\braintree\BraintreeForm;

class BraintreeFormTest extends TestCase
{
    public static $customer;

    /**
     * @param string $ccNumber
     * @param string $cvv
     * @param string $exp
     * @param string $cardholderName
     * @dataProvider validCreditCardProvider
     */
    public function testSingleCharge($ccNumber, $cvv, $exp, $cardholderName)
    {
        $model = new BraintreeForm();
        $model->setScenario('sale');
        $this->assertTrue(
            $model->load(
                [
                    'creditCard_number' => $ccNumber,
                    'creditCard_cvv' => $cvv,
                    'creditCard_expirationDate' => $exp,
                    'creditCard_cardholderName' => $cardholderName,
                ],
                ''
            )
        );
        $model->amount = rand(1, 200);
        $this->assertNotFalse($model->send());
    }

    /**
     * @param string $ccNumber
     * @param string $cvv
     * @param string $exp
     * @dataProvider invalidCreditCardProvider
     */
    public function testSingleChargeFail($ccNumber, $cvv, $exp)
    {
        $model = new BraintreeForm();
        $model->setScenario('sale');
        $this->assertTrue(
            $model->load(
                [
                    'creditCard_number' => $ccNumber,
                    'creditCard_cvv' => $cvv,
                    'creditCard_expirationDate' => $exp,
                ],
                ''
            )
        );
        $model->amount = 1;
        $this->assertFalse($model->send());
        $this->assertInstanceOf('\Braintree\Result\Error', $model->lastError);
        $this->assertInternalType('string', $model->lastError->message);
    }

    /**
     * @param string $firstName
     * @param string $lastName
     * @dataProvider customerProvider
     */
    public function testCustomerCreate($firstName, $lastName)
    {
        $model = new BraintreeForm();
        $model->setScenario('customer');
        $this->assertTrue(
            $model->load(
                [
                    'customer_firstName' => $firstName,
                    'customer_lastName' => $lastName,
                ],
                ''
            )
        );
        $result = $model->send();
        $this->assertNotFalse($result);
        $this->assertArrayHasKey('result', $result);
        $this->assertObjectHasAttribute('customer', $result['result']);
        self::$customer = $result['result']->customer;
        $this->assertInstanceOf('\Braintree\Customer', self::$customer);
    }

    /**
     * @param string $ccNumber
     * @param string $cvv
     * @param string $exp
     * @param string $cardholderName
     * @depends      testCustomerCreate
     * @dataProvider validCreditCardProvider
     */
    public function testCreditCardCreate($ccNumber, $cvv, $exp, $cardholderName)
    {
        $model = new BraintreeForm();
        $model->setScenario('creditCard');
        $this->assertTrue(
            $model->load(
                [
                    'creditCard_number' => $ccNumber,
                    'creditCard_cvv' => $cvv,
                    'creditCard_expirationDate' => $exp,
                    'creditCard_cardholderName' => $cardholderName,
                ],
                ''
            )
        );
        $model->customerId = self::$customer->id;
        $this->assertNotFalse($model->send());
    }

    /**
     * @depends testCustomerCreate
     */
    public function testTokenPayment()
    {
        $customer = Customer::find(self::$customer->id);
        $this->assertInstanceOf('\Braintree\Customer', $customer);
        $this->assertArrayHasKey(0, $customer->paymentMethods());
        $model = new BraintreeForm();
        $model->setScenario('saleFromVault');
        $this->assertTrue(
            $model->load(
                [
                    'amount' => rand(1, 200),
                    'paymentMethodToken' => $customer->paymentMethods()[0]->token,
                ],
                ''
            )
        );
        $this->assertNotFalse($model->send());
    }

    public function validCreditCardProvider()
    {
        return [
            [
                '5555555555554444',
                '123',
                '12/2020',
                'BRAD PITT'
            ],
        ];
    }

    public function customerProvider()
    {
        return [
            [
                'Brad',
                'Pitt',
            ],
        ];
    }

    public function invalidCreditCardProvider()
    {
        return [
            'invalid card number' => ['0', '123', '12/2020'],
        ];
    }
}
