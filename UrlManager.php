<?php

namespace tuyakhov\braintree;

use Yii;

/**
 * Class UrlManager.
 * Provides methods for creating of urls to Braintree.
 * Usage:
 * Yii::$app->braintree->urlManager->viewPlans();
 * @package tuyakhov\braintree
 */
class UrlManager
{
    /**
     * @var string base url
     */
    protected $baseUrl;

    /**
     * Get base url.
     * @return string base url.
     */
    public function getBaseUrl()
    {
        if (!isset($this->baseUrl)) {
            /** @var Braintree $braintree */
            $braintree = Yii::$app->get('braintree');
            $domain = 'braintreegateway.com';
            if ($braintree->environment == 'sandbox') {
                $domain = "sandbox.$domain";
            } else {
                $domain = "www.$domain";
            }
            $this->baseUrl = "https://$domain/merchants/$braintree->merchantId/";
        }

        return $this->baseUrl;
    }

    /**
     * Get url of page on which user can view all plans.
     * @return string url of page on which user can view all plans.
     */
    public function viewPlans()
    {
        return $this->getBaseUrl() . 'plans';
    }

    /**
     * Get url of page on which user can view specified plan.
     * @param string $planId plan id
     * @return string url of page on which user can view specified plan.
     */
    public function viewPlan($planId)
    {
        return $this->getBaseUrl() . "plans/$planId";
    }

    /**
     * Get url of page on which user can create plan.
     * @return string url of page on which user can create plan.
     */
    public function createPlan()
    {
        return $this->getBaseUrl() . 'plans/new';
    }

    /**
     * Get url of page on which user can update plan.
     * @param string $planId plan id
     * @return string url of page on which user can update plan.
     */
    public function updatePlan($planId)
    {
        return $this->getBaseUrl() . "plans/$planId/edit";
    }
}
