<?php


namespace EasyCredit\Validator;

/**
 * Class CheckboxValidator
 * @package EasyCredit\Validator
 */
class CheckboxValidator extends AbstractValidator
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
            'error_message' => array('key' => null, 'message' => null),
        );

        $this->config = array_merge($defaultConfig, $config);
    }

    /**
     * @return bool
     */
    public function validate()
    {
        if ($this->data === 1
            || $this->data === "1"
            || $this->data === true
            || $this->data === "true"
        ) {
            return true;
        }

        $this->addMessage(
            $this->config['error_message']['message'],
            $this->config['error_message']['key']
        );

        return false;
    }
}
