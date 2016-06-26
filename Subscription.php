<?php
/**
 * @author Anton Tuyakhov <atuyakhov@gmail.com>
 */

namespace tuyakhov\braintree;

use Braintree\Subscription as BraintreeSubscription;
use yii\base\Component;

// todo test
class Subscription extends Component
{
    /**
     * Create subscription.
     * @param array $params
     * @return \Braintree\Result\Error|\Braintree\Result\Successful
     */
    public static function create($params)
    {
        return BraintreeSubscription::create($params);
    }

    /**
     * Find subscription.
     * @param string $idSubscription
     * @return Subscription
     */
    public static function find($idSubscription)
    {
        return BraintreeSubscription::find($idSubscription);
    }

    /**
     * Update subscription.
     * @param string $idSubscription
     * @param array $params
     * @return \Braintree\Result\Error|\Braintree\Result\Successful
     */
    public static function update($idSubscription, $params)
    {
        return BraintreeSubscription::update($idSubscription, $params);
    }

    /**
     * Cancel subscription.
     * @param string $idSubscription
     * @return \Braintree\Result\Error|\Braintree\Result\Successful
     */
    public static function cancel($idSubscription)
    {
        return BraintreeSubscription::cancel($idSubscription);
    }
}
