<?php
/**
 * @author Anton Tuyakhov <atuyakhov@gmail.com>
 */

namespace tuyakhov\braintree;

use Braintree\Address;
use Braintree\ClientToken;
use Braintree\Configuration;
use Braintree\CreditCard;
use Braintree\Customer;
use Braintree\MerchantAccount;
use Braintree\PaymentMethod;
use Braintree\Plan;
use Braintree\Subscription;
use Braintree\Transaction;
use Braintree\WebhookNotification;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * Class Braintree.
 * @package tuyakhov\braintree
 *
 * @property UrlManager $urlManager provides methods for creating of urls to Braintree; this property is read-only
 */
class Braintree extends Component
{
    public $environment = 'sandbox';
    public $merchantId;
    public $publicKey;
    public $privateKey;

    protected $clientToken;
    protected $options;

    /**
     * @var UrlManager
     */
    protected $urlManager;

    /**
     * @var Plan[] cached plans from Braintree
     */
    protected $plans;

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
        parent::init();
    }

    /**
     * Returns the url manager object.
     * @return UrlManager
     */
    public function getUrlManager()
    {
        if (!isset($this->urlManager)) {
            $this->urlManager = new UrlManager;
        }

        return $this->urlManager;
    }

    public function getClientToken($params = [])
    {
        if (!isset($this->clientToken)) {
            $this->clientToken = ClientToken::generate($params);
        }
        return $this->clientToken;
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

    public function savePaymentMethod()
    {
        $result = PaymentMethod::create($this->options['paymentMethod']);

        if ($result->success) {
            return ['status' => true, 'result' => $result];
        } else {
            return ['status' => false, 'result' => $result];
        }
    }

    public function updatePaymentMethod()
    {
        $result = PaymentMethod::update($this->options['paymentMethodToken'], $this->options['paymentMethod']);

        if ($result->success) {
            return ['status' => true, 'result' => $result];
        } else {
            return ['status' => false, 'result' => $result];
        }
    }

    public function deletePaymentMethod()
    {
        $result = PaymentMethod::delete($this->options['paymentMethodToken']);

        if ($result->success) {
            return ['status' => true, 'result' => $result];
        } else {
            return ['status' => false, 'result' => $result];
        }
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
     * @param array $values array containing Credit Card values, the following keys are expected:
     *     integer 'number' (required) Credit Card Number
     *     integer 'cvv' (optional) Credit Card Security code
     *     integer 'expirationMonth' (optional) format: MM
     *         (use expirationMonth and expirationYear or expirationDate, not both)
     *     integer 'expirationYear' (optional) format: YYYY
     *         (use expirationMonth and expirationYear or expirationDate, not both)
     *     string 'expirationDate' (optional) format: MM/YYYY
     *         (use expirationMonth and expirationYear or expirationDate, not both)
     *     string 'cardholderName' (optional) the cardholder name associated with the credit card
     */
    public function setCreditCard($values)
    {
        $creditCard = ['number' => $values['number']];
        $optionalParamNames = ['cvv', 'expirationMonth', 'expirationYear', 'expirationDate', 'cardholderName'];
        foreach ($optionalParamNames as $optionalParamName) {
            $optionalValue = ArrayHelper::getValue($values, $optionalParamName);
            if (isset($optionalValue)) {
                $creditCard[$optionalParamName] = $optionalValue;
            }
        }
        $this->options['creditCard'] = $creditCard;
    }

    /**
     * @param array $values
     * @return $this
     */
    public function setOptions($values)
    {
        if (!empty($values)) {
            foreach ($values as $key => $value) {
                if ($key == 'amount') {
                    $this->setAmount($values['amount']);
                } elseif ($key == 'creditCard') {
                    $this->setCreditCard($values['creditCard']);
                } else {
                    $this->options[$key] = $value;
                }
            }
        }
        return $this;
    }

    /**
     * Set the amount to charge.
     * @param float $amount no dollar sign needed
     */
    public function setAmount($amount)
    {
        $this->options['amount'] = round($amount, 2);
    }

    /**
     * @param boolean $allowCaching whether to allow caching the result of retrieving of data from Braintree;
     * when this parameter is true (default), if data was retrieved before,
     * result will be directly returned when calling this method;
     * if this parameter is false, this method will always perform request to Braintree to obtain the up-to-date data;
     * note that this caching is effective only within the same HTTP request
     * @return Plan[]
     */
    public function getAllPlans($allowCaching = true)
    {
        if (!$allowCaching) {
            return Plan::all();
        }

        if (!isset($this->plans)) {
            $this->plans = Plan::all();
        }
        return $this->plans;
    }

    /**
     * @param boolean $allowCaching whether to allow caching the result of retrieving of data from Braintree;
     * when this parameter is true (default), if data was retrieved before,
     * result will be directly returned when calling this method;
     * if this parameter is false, this method will always perform request to Braintree to obtain the up-to-date data;
     * note that this caching is effective only within the same HTTP request
     * @return array
     */
    public function getPlanIds($allowCaching = true)
    {
        $plans = $this->getAllPlans($allowCaching);
        $planIds = [];
        foreach ($plans as $plan) {
            $planIds[] = $plan->id;
        }
        return $planIds;
    }

    /**
     * @param string $planId
     * @param boolean $allowCaching whether to allow caching the result of retrieving of data from Braintree;
     * when this parameter is true (default), if data was retrieved before,
     * result will be directly returned when calling this method;
     * if this parameter is false, this method will always perform request to Braintree to obtain the up-to-date data;
     * note that this caching is effective only within the same HTTP request
     * @return Plan|null
     */
    public function getPlanById($planId, $allowCaching = true)
    {
        $plans = $this->getAllPlans($allowCaching);
        foreach ($plans as $plan) {
            if ($plan->id == $planId) {
                return $plan;
            }
        }
        return null;
    }

    /**
     * Finds transaction by id.
     * @param string $id
     * @return Transaction
     */
    public function findTransaction($id)
    {
        return Transaction::find($id);
    }

    /**
     * @param string $merchantId
     * @return MerchantAccount
     */
    public function findMerchant($merchantId)
    {
        return MerchantAccount::find($merchantId);
    }

    /**
     * @param string $idCustomer
     * @return Customer
     */
    public function findCustomer($idCustomer)
    {
        return Customer::find($idCustomer);
    }

    /**
     * Create subscription.
     * @param array $params
     * @return \Braintree\Result\Error|\Braintree\Result\Successful
     */
    public function createSubscription($params)
    {
        return Subscription::create($params);
    }

    public function findSubscription($subscriptionId)
    {
        return Subscription::find($subscriptionId);
    }

    public function searchSubscription($params = [])
    {
        return Subscription::search($params);
    }

    public function searchTransaction($params = [])
    {
        return Transaction::search($params);
    }

    /**
     * Update subscription.
     * @param string $subscriptionId
     * @param array $params
     * @return \Braintree\Result\Error|\Braintree\Result\Successful
     */
    public function updateSubscription($subscriptionId, $params)
    {
        return Subscription::update($subscriptionId, $params);
    }

    /**
     * Cancel subscription.
     * @param string $subscriptionId
     * @return \Braintree\Result\Error|\Braintree\Result\Successful
     */
    public function cancelSubscription($subscriptionId)
    {
        return Subscription::cancel($subscriptionId);
    }

    public function retryChargeSubscription($subscriptionId, $amount)
    {
        $retryResult = Subscription::retryCharge($subscriptionId, $amount);

        if ($retryResult->success) {
            $result = Transaction::submitForSettlement($retryResult->transaction->id);
            return $result;
        }

        return $retryResult;
    }

    public function parseWebhookNotification($signature, $payload)
    {
        return WebhookNotification::parse($signature, $payload);
    }
}
