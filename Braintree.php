<?php
/**
 * @author Anton Tuyakhov <atuyakhov@gmail.com>
 */

namespace tuyakhov\braintree;

use Braintree\Address;
use Braintree\ClientToken;
use Braintree\CreditCard;
use Braintree\Configuration;
use Braintree\Customer;
use Braintree\MerchantAccount;
use Braintree\PaymentMethodNonce;
use Braintree\PaymentMethod;
use Braintree\Plan;
use Braintree\Subscription;
use Braintree\Transaction;
use yii\base\Component;
use yii\base\InvalidConfigException;

class Braintree extends Component
{
    public $environment = 'sandbox';
    public $merchantId;
    public $publicKey;
    public $privateKey;
    public $clientSideKey;

    public $options;

    /**
     * Sets up Braintree configuration from config file.
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        foreach (['merchantId', 'publicKey', 'privateKey', 'environment'] as $attribute) {
            if ($this->$attribute === null) {
                throw new InvalidConfigException(
                    strtr(
                        '"{class}::{attribute}" cannot be empty.',
                        [
                            '{class}' => static::className(),
                            '{attribute}' => '$' . $attribute,
                        ]
                    )
                );
            }
            Configuration::$attribute($this->$attribute);
        }
        $this->clientSideKey = ClientToken::generate();
        parent::init();
    }

    /**
     * Braintree sale function.
     * @param bool|true $submitForSettlement
     * @param bool|true $storeInVaultOnSuccess
     * @return array
     */
    public function singleCharge($submitForSettlement = true, $storeInVaultOnSuccess = true)
    {
        $this->options['options']['submitForSettlement'] = $submitForSettlement;
        $this->options['options']['storeInVaultOnSuccess'] = $storeInVaultOnSuccess;
        $result = Transaction::sale($this->options);

        if ($result->success) {
            return ['status' => true, 'result' => $result];
        } else {
            if ($result->transaction) {
                return ['status' => false, 'result' => $result];
            } else {
                return ['status' => false, 'result' => $result];
            }
        }
    }

    public function saleWithServiceFee($merchantAccountId, $amount, $paymentMethodNonce = null, $serviceFeeAmount)
    {
        $result = Transaction::sale(
            [
                'merchantAccountId' => $merchantAccountId,
                'amount' => $amount,
                'paymentMethodNonce' => $paymentMethodNonce,
                'serviceFeeAmount' => $serviceFeeAmount,
            ]
        );
        return $result;
    }

    public function saleWithPaymentNonce($amount, $paymentMethodNonce)
    {
        $result = Transaction::sale(
            [
                'amount' => $amount,
                'paymentMethodNonce' => $paymentMethodNonce,
                'options' => [
                    'submitForSettlement' => true,
                    'storeInVaultOnSuccess' => true,
                ],
            ]
        );
        return $result;
    }

    public function createPaymentMethodNonce($creditCardToken)
    {
        return PaymentMethodNonce::create($creditCardToken);
    }

    public function createPaymentMethod($customerId, $paymentNonce, $options)
    {
        return PaymentMethod::create([
            'customerId' => $customerId,
            'paymentMethodNonce' => $paymentNonce,
            'options' => $options
        ]);
    }

    /**
     * Finds transaction by id.
     */
    public function findTransaction($id)
    {
        return Transaction::find($id);
    }

    /**
     * This save customer to braintree and returns result array.
     * @return array
     */
    public function saveCustomer()
    {
        if (isset($this->options['customerId'])) {
            $this->options['customer']['id'] = $this->options['customerId'];
        }
        $result = Customer::create($this->options['customer']);

        if ($result->success) {
            return ['status' => true, 'result' => $result];
        } else {
            return ['status' => false, 'result' => $result];
        }
    }

    public function createEmptyCustomer()
    {
        return Customer::createNoValidate();
    }

    /**
     * This save credit cart to braintree.
     * @return array
     */
    public function saveCreditCard()
    {
        $sendArray = $this->options['creditCard'];
        if (isset($this->options['billing'])) {
            $sendArray['billingAddress'] = $this->options['billing'];
        }
        if (isset($this->options['customerId'])) {
            $sendArray['customerId'] = $this->options['customerId'];
        }
        $result = CreditCard::create($sendArray);

        if ($result->success) {
            return ['status' => true, 'result' => $result];
        } else {
            return ['status' => false, 'result' => $result];
        }
    }

    public function createCustomerCreditCard($params)
    {
        return CreditCard::create($params)->creditCard;
    }

    public function saveAddress()
    {
        $sendArray = $this->options['billing'];
        if (isset($this->options['customerId'])) {
            $sendArray['customerId'] = $this->options['customerId'];
        }
        $result = Address::create($sendArray);

        if ($result->success) {
            return ['status' => true, 'result' => $result];
        } else {
            return ['status' => false, 'result' => $result];
        }
    }

    /**
     * Constructs the Credit Card array for payment.
     * @param integer $number Credit Card Number
     * @param integer $cvv (optional) Credit Card Security code
     * @param integer $expirationMonth format: MM (use expirationMonth and expirationYear or expirationDate not both)
     * @param integer $expirationYear format: YYYY (use expirationMonth and expirationYear or expirationDate not both)
     * @param string $expirationDate format: MM/YYYY (use expirationMonth and expirationYear or expirationDate not both)
     * @param string $cardholderName the cardholder name associated with the credit card
     */
    public function setCreditCard(
        $number,
        $cvv = null,
        $expirationMonth = null,
        $expirationYear = null,
        $expirationDate = null,
        $cardholderName = null
    ) {
        $this->options['creditCard'] = [];
        $this->options['creditCard']['number'] = $number;
        if (isset($cvv)) {
            $this->options['creditCard']['cvv'] = $cvv;
        }
        if (isset($expirationMonth)) {
            $this->options['creditCard']['expirationMonth'] = $expirationMonth;
        }
        if (isset($expirationYear)) {
            $this->options['creditCard']['expirationYear'] = $expirationYear;
        }
        if (isset($expirationDate)) {
            $this->options['creditCard']['expirationDate'] = $expirationDate;
        }
        if (isset($cardholderName)) {
            $this->options['creditCard']['cardholderName'] = $cardholderName;
        }
    }

    public function getCreditCard($inputValues)
    {
        $default = [
            'cvv' => null,
            'expirationMonth' => null,
            'expirationYear' => null,
            'expirationDate' => null,
            'cardholderName' => null,
        ];
        $values = array_merge($default, $inputValues);
        $this->setCreditCard(
            $values['number'],
            $values['cvv'],
            $values['expirationMonth'],
            $values['expirationYear'],
            $values['expirationDate'],
            $values['cardholderName']
        );
    }

    public function getOptions($values)
    {
        if (!empty($values)) {
            foreach ($values as $key => $value) {
                if ($key == 'amount') {
                    $this->setAmount($values['amount']);
                } elseif ($key == 'creditCard') {
                    $this->getCreditCard($values['creditCard']);
                } else {
                    $this->options[$key] = $value;
                }
            }
        }
    }

    /**
     * Set the amount to charge.
     * @param float $amount no dollar sign needed
     */
    public function setAmount($amount)
    {
        $this->options['amount'] = round($amount, 2);
    }

    public function createMerchant($individualParams, $businessParams, $fundingParams, $tosAccepted, $id = null)
    {
        $params = [
            'individual' => $individualParams,
            'business' => $businessParams,
            'funding' => $fundingParams,
            'tosAccepted' => $tosAccepted,
            'masterMerchantAccountId' => "masterMerchantAccount",
            'id' => $id,
        ];

        return MerchantAccount::create($params);
    }

    public static function getAllPlans()
    {
        return Plan::all();
    }

    public static function getPlanIds()
    {
        $plans = static::getAllPlans();
        $planIds = [];
        foreach ($plans as $plan) {
            $planIds[] = $plan->id;
        }
        return $planIds;
    }

    public static function getPlanById($planId)
    {
        $plans = static::getAllPlans();
        foreach ($plans as $key => $plan) {
            if ($plan->id == $planId) {
                return $plans[$key];
            }
        }
        return null;
    }

    /**
     * Create subscription.
     * @param array $params
     */
    public function createSubscription($params)
    {
        return Subscription::create($params);
    }

    /**
     * Create customer with payment method.
     * @param string $firstName
     * @param string $lastName
     * @param string $paymentNonce
     */
    public static function createCustomerWithPaymentMethod($firstName, $lastName, $paymentNonce)
    {
        return Customer::create([
            'firstName' => $firstName,
            'lastName' => $lastName,
            'paymentMethodNonce' => $paymentNonce
        ]);
    }

    public function findMerchant($idMerchant)
    {
        return MerchantAccount::find($idMerchant);
    }

    public function findSubscription($idSubscription)
    {
        return Subscription::find($idSubscription);
    }

    public function findCustomer($idCustomer)
    {
        return Customer::find($idCustomer);
    }

    /**
     * Update subscription.
     * @param string $idSubscription required
     * @param array $params
     */
    public function updateSubscription($idSubscription, $params)
    {
        return Subscription::update($idSubscription, $params);
    }

    /**
     * Cancel subscription.
     * @param string $idSubscription required
     */
    public function cancelSubscription($idSubscription)
    {
        return Subscription::cancel($idSubscription);
    }
}
