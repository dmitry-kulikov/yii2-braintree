<?php
/**
 * @author Anton Tuyakhov <atuyakhov@gmail.com>
 */

namespace tuyakhov\braintree;

use Yii;
use yii\base\Model;

class BraintreeForm extends Model
{
    public $amount;
    public $orderId;
    public $paymentMethodToken;
    public $planId;

    public $creditCard_number;
    public $creditCard_cvv;
    public $creditCard_expirationMonth;
    public $creditCard_expirationYear;
    public $creditCard_expirationDate;
    public $creditCard_cardholderName;

    public $customer_firstName;
    public $customer_lastName;
    public $customer_company;
    public $customer_phone;
    public $customer_fax;
    public $customer_website;
    public $customer_email;

    public $billing_firstName;
    public $billing_lastName;
    public $billing_company;
    public $billing_streetAddress;
    public $billing_extendedAddress;
    public $billing_locality;
    public $billing_region;
    public $billing_postalCode;
    public $billing_countryCodeAlpha2;

    public $shipping_firstName;
    public $shipping_lastName;
    public $shipping_company;
    public $shipping_streetAddress;
    public $shipping_extendedAddress;
    public $shipping_locality;
    public $shipping_region;
    public $shipping_postalCode;
    public $shipping_countryCodeAlpha2;

    public $customerId;

    /**
     * @var \Braintree\Result\Error last error from Braintree
     */
    public $lastError;

    public function rules()
    {
        return [
            [
                ['customerId', 'creditCard_number', 'creditCard_cvv', 'creditCard_expirationDate'],
                'required',
                'on' => 'creditCard',
            ],
            [['customerId'], 'required', 'on' => 'address'],
            [['customer_firstName', 'customer_lastName'], 'required', 'on' => 'customer'],
            [
                ['amount', 'creditCard_number', 'creditCard_cvv', 'creditCard_expirationDate'],
                'required',
                'on' => 'sale',
            ],
            [['amount', 'paymentMethodToken'], 'required', 'on' => 'saleFromVault'],
            [['amount'], 'double'],
            [['customer_email'], 'email'],
            [
                [
                    'creditCard_expirationMonth',
                    'creditCard_expirationYear',
                    'creditCard_expirationDate',
                    'creditCard_cardholderName',
                    'customer_firstName',
                    'customer_lastName',
                    'customer_company',
                    'customer_phone',
                    'customer_fax',
                    'customer_website',
                    'billing_firstName',
                    'billing_lastName',
                    'billing_company',
                    'billing_streetAddress',
                    'billing_extendedAddress',
                    'billing_locality',
                    'billing_region',
                    'billing_postalCode',
                    'billing_countryCodeAlpha2',
                    'shipping_firstName',
                    'shipping_lastName',
                    'shipping_company',
                    'shipping_streetAddress',
                    'shipping_extendedAddress',
                    'shipping_locality',
                    'shipping_region',
                    'shipping_postalCode',
                    'shipping_countryCodeAlpha2',
                ],
                'safe',
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'amount' => 'Amount($)',
            'orderId' => 'Order ID',
            'creditCard_number' => 'Credit Card Number',
            'creditCard_cvv' => 'Security Code',
            'creditCard_expirationMonth' => 'Expiration Month (MM)',
            'creditCard_expirationYear' => 'Expiration Year (YYYY)',
            'creditCard_expirationDate' => 'Expiration Date (MM/YYYY)',
            'creditCard_cardholderName' => 'Name on Card',
            'customer_firstName' => 'First Name',
            'customer_lastName' => 'Last Name',
            'customer_company' => 'Company Name',
            'customer_phone' => 'Phone Number',
            'customer_fax' => 'Fax Number',
            'customer_website' => 'Website',
            'customer_email' => 'Email',
            'billing_firstName' => 'First Name',
            'billing_lastName' => 'Last Name',
            'billing_company' => 'Company Name',
            'billing_streetAddress' => 'Address',
            'billing_extendedAddress' => 'Address',
            'billing_locality' => 'City/Locality',
            'billing_region' => 'State/Region',
            'billing_postalCode' => 'Zip/Postal Code',
            'billing_countryCodeAlpha2' => 'Country',
            'shipping_firstName' => 'First Name',
            'shipping_lastName' => 'Last Name',
            'shipping_company' => 'Company Name',
            'shipping_streetAddress' => 'Address',
            'shipping_extendedAddress' => 'Address',
            'shipping_locality' => 'City/Locality',
            'shipping_region' => 'State/Region',
            'shipping_postalCode' => 'Zip/Postal Code',
            'shipping_countryCodeAlpha2' => 'Country',
        ];
    }

    /**
     * @return null|\tuyakhov\braintree\Braintree
     * @throws \yii\base\InvalidConfigException
     */
    public static function getBraintree()
    {
        return Yii::$app->get('braintree');
    }

    public function getValuesFromAttributes()
    {
        $values = array();
        foreach ($this->attributes as $key => $val) {
            if (!is_object($val) && !is_null($val) && strlen($val) > 0) {
                if (strpos($key, '_') === false) {
                    $values[$key] = $val;
                } else {
                    $pieces = explode('_', $key);
                    $values[$pieces[0]][$pieces[1]] = $val;
                }
            }
        }
        return $values;
    }

    public function send()
    {
        static::getBraintree()->setOptions($this->getValuesFromAttributes());
        $scenario = $this->getScenario();
        return $this->$scenario();

    }

    public function sale()
    {
        $return = static::getBraintree()->singleCharge();
        if ($return['status'] === false) {
            $this->addErrorFromResponse($return['result']);
            return false;
        } else {
            return $return;
        }
    }

    public function saleFromVault()
    {
        $return = static::getBraintree()->singleCharge();
        if ($return['status'] === false) {
            $this->addErrorFromResponse($return['result']);
            return false;
        } else {
            return $return;
        }
    }

    public function customer()
    {
        $return = static::getBraintree()->saveCustomer();
        if ($return['status'] === false) {
            $this->addErrorFromResponse($return['result']);
            return false;
        } else {
            return $return;
        }
    }

    public function creditCard()
    {
        $return = static::getBraintree()->saveCreditCard();
        if ($return['status'] === false) {
            $this->addErrorFromResponse($return['result']);
            return false;
        } else {
            return $return;
        }
    }

    public function address()
    {
        $return = static::getBraintree()->saveAddress();
        if ($return['status'] === false) {
            $this->addErrorFromResponse($return['result']);
            return false;
        } else {
            return $return;
        }
    }

    /**
     * @param string $idMerchant
     * @return \Braintree\MerchantAccount
     */
    public function findMerchant($idMerchant)
    {
        return static::getBraintree()->findMerchant($idMerchant);
    }

    public function saleWithServiceFee($merchantAccountId, $amount, $paymentMethodNonce, $serviceFeeAmount)
    {
        $result = static::getBraintree()->saleWithServiceFee(
            $merchantAccountId,
            $amount,
            $paymentMethodNonce,
            $serviceFeeAmount
        );
        if ($result->success) {
            return $result;
        } else {
            $response = ['status' => $result->success, 'message' => $result->message];
            return $response;
        }
    }

    public function saleWithPaymentNonce($amount, $paymentMethodNonce)
    {
        $result = static::getBraintree()->saleWithPaymentNonce($amount, $paymentMethodNonce);
        if ($result->success) {
            return ['result' => $result];
        } else {
            $response = ['status' => $result->success, 'message' => $result->message];
            return $response;
        }
    }

    public function createPaymentMethod($customerId, $paymentNonce, $makeDefault = false, $options = [])
    {
        if ($makeDefault) {
            $options = array_merge($options, ['makeDefault' => $makeDefault]);
        }
        $return = static::getBraintree()->setOptions(
            [
                'paymentMethod' => [
                    'customerId' => $customerId,
                    'paymentMethodNonce' => $paymentNonce,
                    'options' => $options,
                ],
            ]
        )->savePaymentMethod();
        if ($return['status'] === false) {
            $this->addErrorFromResponse($return['result']);
        }
        return $return;
    }

    public function deletePaymentMethod($paymentMethodToken)
    {
        $return = static::getBraintree()->setOptions(
            [
                'paymentMethodToken' => $paymentMethodToken,
            ]
        )->deletePaymentMethod();
        if ($return['status'] === false) {
            $this->addErrorFromResponse($return['result']);
        }
        return $return;
    }

    /**
     * @param string $paymentMethodToken
     * @param array $params
     * example:
     * [
     *     'billingAddress' => [
     *         'firstName' => 'Drew',
     *         'lastName' => 'Smith',
     *         'company' => 'Smith Co.',
     *         'streetAddress' => '1 E Main St',
     *         'region' => 'IL',
     *         'postalCode' => '60622',
     *     ],
     * ]
     * @param array $options
     * example:
     * [
     *     'makeDefault' => true,
     *     'verifyCard' => true,
     * ]
     * @return array
     */
    public function updatePaymentMethod($paymentMethodToken, $params = [], $options = [])
    {
        $paymentMethodOptions = array_merge($params, ['options' => $options]);
        $return = static::getBraintree()->setOptions(
            [
                'paymentMethodToken' => $paymentMethodToken,
                'paymentMethod' => $paymentMethodOptions,
            ]
        )->updatePaymentMethod();
        if ($return['status'] === false) {
            $this->addErrorFromResponse($return['result']);
        }
        return $return;
    }

    /**
     * @param string $paymentNonce
     * @return array
     */
    public function createCustomerWithPaymentMethod($paymentNonce)
    {
        $result = static::getBraintree()->setOptions(
            [
                'customer' => [
                    'firstName' => $this->customer_firstName,
                    'lastName' => $this->customer_lastName,
                    'paymentMethodNonce' => $paymentNonce,
                ],
            ]
        )->saveCustomer();

        if ($result['status']) {
            $result['customerId'] = $result['result']->customer->id;
            $result['paymentMethodToken'] = $result['result']->customer->paymentMethods[0]->token;
        }
        return $result;
    }

    /**
     * @return \Braintree\Plan[]
     */
    public static function getAllPlans()
    {
        return static::getBraintree()->getAllPlans();
    }

    /**
     * @return array
     */
    public static function getPlanIds()
    {
        return static::getBraintree()->getPlanIds();
    }

    /**
     * @param string $planId
     * @return \Braintree\Plan|null
     */
    public static function getPlanById($planId)
    {
        return static::getBraintree()->getPlanById($planId);
    }

    /**
     * @param array $params
     * @return array
     */
    public function createSubscription($params = [])
    {
        $params = array_merge(['paymentMethodToken' => $this->paymentMethodToken, 'planId' => $this->planId], $params);
        $result = static::getBraintree()->createSubscription($params);
        if ($result->success) {
            return [
                'status' => true,
                'result' => $result,
            ];
        } else {
            return ['status' => false, 'result' => $result];
        }
    }

    /**
     * @param string $idSubscription
     * @return \Braintree\Subscription
     */
    public function findSubscription($idSubscription)
    {
        return static::getBraintree()->findSubscription($idSubscription);
    }

    /**
     * @param string $idCustomer
     * @return \Braintree\Customer
     */
    public function findCustomer($idCustomer)
    {
        return static::getBraintree()->findCustomer($idCustomer);
    }

    /**
     * Update subscription.
     * @param string $idSubscription
     * @param array $params
     * @return array
     */
    public function updateSubscription($idSubscription, $params)
    {
        $result = static::getBraintree()->updateSubscription($idSubscription, $params);
        if ($result->success) {
            return [
                'status' => true,
                'result' => $result,
            ];
        } else {
            return ['status' => false, 'result' => $result];
        }
    }

    /**
     * Cancel subscription.
     * @param string $idSubscription
     * @return array
     */
    public function cancelSubscription($idSubscription)
    {
        $result = static::getBraintree()->cancelSubscription($idSubscription);
        if ($result->success) {
            return [
                'status' => true,
                'result' => $result,
            ];
        } else {
            return ['status' => false, 'result' => $result];
        }
    }

    public function searchSubscription($params = [])
    {
        return static::getBraintree()->searchSubscription($params);
    }

    public function retryChargeSubscription($idSubscription, $amount)
    {
        $retryResult = static::getBraintree()->retryChargeSubscription($idSubscription, $amount);
        if (!$retryResult->success) {
            $this->addErrorFromResponse($retryResult);
            return false;
        } else {
            return $retryResult;
        }
    }

    public function parseWebhookNotification($signature, $payload)
    {
        return static::getBraintree()->parseWebhookNotification($signature, $payload);
    }

    /**
     * This add error from braintree response.
     * @param $result \Braintree\Result\Error
     */
    public function addErrorFromResponse($result)
    {
        $this->lastError = $result;
        $errors = $result->errors;
        foreach ($errors->shallowAll() as $error) {
            $this->addError('creditCard_number', $error->message);
        }
        /** @var \Braintree\Error\ValidationErrorCollection $transactionErrors */
        $transactionErrors = $errors->forKey('transaction');
        if (isset($transactionErrors)) {
            foreach ($transactionErrors->shallowAll() as $error) {
                $this->addError('creditCard_number', $error->message);
            }
            $values = $this->getValuesFromAttributes();
            foreach (array_keys($values) as $key) {
                /** @var \Braintree\Error\ValidationErrorCollection $keyErrors */
                $keyErrors = $transactionErrors->forKey($key);
                if (isset($keyErrors)) {
                    foreach ($keyErrors->shallowAll() as $error) {
                        $this->addError($key . '_' . $error->attribute, $error->message);
                    }
                }
            }
        }
        if (!$this->hasErrors()) {
            $this->addError('creditCard_number', $result->message);
        }
    }
}
