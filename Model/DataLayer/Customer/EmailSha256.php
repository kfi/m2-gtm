<?php

namespace Inkl\GoogleTagManager\Model\DataLayer\Customer;

use Inkl\GoogleTagManager\Helper\Config\DataLayerCustomerConfig;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Stdlib\CookieManagerInterface;

class EmailSha256
{
    /** @var DataLayerCustomerConfig */
    private $dataLayerCustomerConfig;
    /** @var CustomerSession */
    private $customerSession;
    /** @var CookieManagerInterface */
    private $cookieManager;

    /**
     * @param DataLayerCustomerConfig $dataLayerCustomerConfig
     * @param CustomerSession $customerSession
     * @param CookieManagerInterface $cookieManager
     */
    public function __construct(DataLayerCustomerConfig $dataLayerCustomerConfig,
                                CustomerSession $customerSession,
                                CookieManagerInterface $cookieManager)
    {
        $this->dataLayerCustomerConfig = $dataLayerCustomerConfig;
        $this->customerSession = $customerSession;
        $this->cookieManager = $cookieManager;
    }

    public function handle()
    {
        if (!$this->isEnabled())
        {
            return;
        }

        $customerEmailSha256 = '';
        if ($this->customerSession->isLoggedIn())
        {
            $customerEmailSha256 = hash("sha256", strtolower($this->customerSession->getCustomer()->getEmail()));
        }

        $this->cookieManager->setPublicCookie('dataLayerCustomerEmailSha256', $customerEmailSha256);
    }

    private function isEnabled()
    {
        return $this->dataLayerCustomerConfig->isEmailSha256Enabled();
    }
}
