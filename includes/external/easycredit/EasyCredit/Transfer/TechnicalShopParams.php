<?php


namespace EasyCredit\Transfer;

/**
 * Class TechnicalShopParams
 *
 * @package EasyCredit\Transfer
 */
class TechnicalShopParams extends AbstractObject
{

    /**
     * @var string
     * @apiName shopSystemHersteller
     */
    protected $shopPlatformManufacturer;


    /**
     * @var string
     * @apiName shopSystemModulversion
     */
    protected $shopPlatformModuleVersion;

    /**
     * @return string
     */
    public function getShopPlatformManufacturer()
    {
        return $this->shopPlatformManufacturer;
    }

    /**
     * @param string $shopPlatformManufacturer
     */
    public function setShopPlatformManufacturer($shopPlatformManufacturer)
    {
        $this->shopPlatformManufacturer = $shopPlatformManufacturer;
    }

    /**
     * @return string
     */
    public function getShopPlatformModuleVersion()
    {
        return $this->shopPlatformModuleVersion;
    }

    /**
     * @param string $shopPlatformModuleVersion
     */
    public function setShopPlatformModuleVersion($shopPlatformModuleVersion)
    {
        $this->shopPlatformModuleVersion = $shopPlatformModuleVersion;
    }
}
