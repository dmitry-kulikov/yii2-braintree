<?php
/**
 * @author Anton Tuyakhov <atuyakhov@gmail.com>
 */

namespace tuyakhov\braintree\tests;

use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the base class for all yii framework unit tests.
 */
class TestCase extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    protected function tearDown()
    {
        $this->destroyApplication();
    }

    /**
     * Populates Yii::$app with a new application.
     * The application will be destroyed on tearDown() automatically.
     * @param array $config the application configuration, if needed
     * @param string $appClass name of the application class to create
     */
    protected function mockApplication($config = [], $appClass = '\yii\console\Application')
    {
        $localConfigFile = __DIR__ . '/config/main-local.php';
        new $appClass(
            ArrayHelper::merge(
                require(__DIR__ . '/config/main.php'),
                is_file($localConfigFile) ? require($localConfigFile) : [],
                $config
            )
        );
    }

    /**
     * Destroys application in Yii::$app by setting it to null.
     */
    protected function destroyApplication()
    {
        Yii::$app = null;
    }
}
