<?php


namespace EasyCredit\Transfer;

use EasyCredit\Config;

/**
 * Class VerificationSnipped
 * @package EasyCredit\Transfer
 */
class VerificationSnipped extends BaseResponse
{
    /**
     * @const
     */
    const SNIPPED_ID_PROD = 'VaVQ3w';

    /**
     * @const
     */
    const SNIPPED_ID_TEST = '2xHtCi';

    /**
     * @var string
     */
    protected $tbProcessIdentifier;

    /**
     * @var string
     */
    protected $snippedId;

    /**
     * VerificationSnipped constructor.
     * @param string $tbProcessIdentifier
     * @param string $snippedId
     */
    public function __construct($tbProcessIdentifier, $snippedId)
    {
        $this->tbProcessIdentifier = $tbProcessIdentifier;
        $this->snippedId = $snippedId;
    }

    /**
     * @return string
     */
    public function getHtml()
    {
        $url = Config::RISK_IDENT_URL_HOSTNAME;

        return <<<EOT
<script>
var di = {t:"$this->tbProcessIdentifier",v:"$this->snippedId",l:"Checkout"};
</script>
<script type="text/javascript" src="//$url/$this->snippedId/di.js"></script> <noscript>
       <link rel="stylesheet" type="text/css"
href="//$url/di.css?t=$this->tbProcessIdentifier&v=$this->snippedId&l=Checkout" />
</noscript>
<object type="application/x-shockwave-flash" data="//$url/$this->snippedId/c.swf" width="0" height="0">
       <param name="movie" value="//$url/$this->snippedId/c.swf" />
       <param name="flashvars" value="t=$this->tbProcessIdentifier&v=$this->snippedId&l=Checkout" />
       <param name="AllowScriptAccess" value="always"/>
</object>
EOT;

    }
}
