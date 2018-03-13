<?php


namespace EasyCredit\Validator;

/**
 * Class NumberValidator
 * @package EasyCredit\Validator
 */
class NumberValidator extends AbstractValidator
{
    /**
     * @var array
     */
    protected $config;

    /**
     * NumberValidator constructor.
     * @param mixed|null $data
     * @param array      $config
     */
    public function __construct($data, $config = array())
    {
        parent::__construct($data);

        $defaultConfig = array(
            'min_length' => null,
            'max_length' => null,
            'min_value' => null,
            'max_value' => null,
            'error_message' => array('default' => array('key' => null, 'message' => null)),
        );

        $this->config = array_merge($defaultConfig, $config);
    }

    /**
     * @return bool
     */
    public function validate()
    {
        $valid = true;

        if ((string)intval($this->data) !== $this->data) {
            return $this->invalidate('default');
        }

        $this->data = intval($this->data);

        if ($this->config['min_length'] !== null && strlen($this->data) < $this->config['min_length']) {
            return $this->invalidate('min_length');
        }

        if ($this->config['max_length'] !== null && strlen($this->data) > $this->config['max_length']) {
            return $this->invalidate('max_length');
        }

        if ($this->config['min_value'] !== null && $this->data < $this->config['min_value']) {
            return $this->invalidate('min_value');
        }

        if ($this->config['max_value'] !== null && $this->data > $this->config['max_value']) {
            return $this->invalidate('max_value');
        }

        return true;
    }

    /**
     * @param string $type
     * @return bool
     */
    protected function invalidate($type)
    {
        $this->addMessage($this->getErrorMessage($type), $this->getErrorKey($type));

        return false;
    }

    /**
     * @param string $type
     * @return string
     */
    protected function getErrorMessage($type)
    {
        if (isset($this->config['error_message'][$type]['message'])) {
            return $this->config['error_message'][$type]['message'];
        }

        return $this->config['error_message']['default']['message'];
    }

    /**
     * @param string $type
     * @return string
     */
    protected function getErrorKey($type)
    {
        if (isset($this->config['error_message'][$type]['key'])) {
            return $this->config['error_message'][$type]['key'];
        }

        return $this->config['error_message']['default']['key'];
    }
}
