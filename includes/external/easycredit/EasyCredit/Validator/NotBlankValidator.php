<?php


namespace EasyCredit\Validator;

/**
 * Class NotBlankValidator
 * @package EasyCredit\Validator
 */
class NotBlankValidator extends AbstractValidator
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
        if (!empty($this->data)) {
            return true;
        }

        $this->addMessage(
            $this->config['error_message']['message'],
            $this->config['error_message']['key']
        );

        return false;
    }
}
