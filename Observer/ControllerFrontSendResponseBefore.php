<?php

namespace Inkl\GoogleTagManager\Observer;

use Inkl\GoogleTagManager\Helper\Config\GeneralConfig;
use Inkl\GoogleTagManager\Model\DataLayer\Catalog\CartProducts;
use Inkl\GoogleTagManager\Model\DataLayer\Catalog\CategoryId;
use Inkl\GoogleTagManager\Model\DataLayer\Catalog\CategoryName;
use Inkl\GoogleTagManager\Model\DataLayer\Catalog\CategoryProducts;
use Inkl\GoogleTagManager\Model\DataLayer\Catalog\SearchKeyword;
use Inkl\GoogleTagManager\Model\DataLayer\Catalog\SearchNumResults;
use Inkl\GoogleTagManager\Model\DataLayer\Catalog\SearchProducts;
use Inkl\GoogleTagManager\Model\DataLayer\Customer\Email;
use Inkl\GoogleTagManager\Model\DataLayer\Customer\EmailSha1;
use Inkl\GoogleTagManager\Model\DataLayer\Ecommerce\Cart;
use Inkl\GoogleTagManager\Model\DataLayer\Ecommerce\Detail;
use Inkl\GoogleTagManager\Model\DataLayer\Ecommerce\Purchase;
use Inkl\GoogleTagManager\Model\DataLayer\General\CurrencyCode;
use Inkl\GoogleTagManager\Model\DataLayer\General\LocaleCode;
use Inkl\GoogleTagManager\Model\DataLayer\General\PageType;
use Inkl\GoogleTagManager\Model\DataLayer\General\PageTypeEx;
use Inkl\GoogleTagManagerLib\GoogleTagManager;
use Inkl\GoogleTagManagerLib\Schema\ContainerId;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\ObjectManagerInterface;

class ControllerFrontSendResponseBefore implements ObserverInterface
{
	/** @var ObjectManagerInterface */
	private $objectManager;
	/** @var State */
	private $state;
	/** @var GeneralConfig */
	private $generalConfig;
	/** @var GoogleTagManager */
	private $googleTagManager;

	/**
	 * @param ObjectManagerInterface $objectManager
	 * @param State $state
	 * @param GeneralConfig $generalConfig
	 */
	public function __construct(ObjectManagerInterface $objectManager,
	                            State $state,
	                            GeneralConfig $generalConfig,
	                            GoogleTagManager $googleTagManager)
	{
		$this->objectManager = $objectManager;
		$this->state = $state;
		$this->generalConfig = $generalConfig;
		$this->googleTagManager = $googleTagManager;
	}

	/**
	 * @param Observer $observer
	 * @return void
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function execute(Observer $observer)
	{
		$request = $observer->getRequest();
		if (!$this->isEnabled() || $request->isPost())
		{
			return;
		}

		$this->objectManager->get(PageType::class)->handle();
		$this->objectManager->get(PageTypeEx::class)->handle();
		$this->objectManager->get(LocaleCode::class)->handle();
		$this->objectManager->get(CurrencyCode::class)->handle();

		$this->objectManager->get(CategoryId::class)->handle();
		$this->objectManager->get(CategoryName::class)->handle();

		$this->objectManager->get(CategoryProducts::class)->handle();

		$this->objectManager->get(SearchKeyword::class)->handle();
		$this->objectManager->get(SearchProducts::class)->handle();
		$this->objectManager->get(SearchNumResults::class)->handle();

		$this->objectManager->get(CartProducts::class)->handle();

		$this->objectManager->get(Detail::class)->handle();
		$this->objectManager->get(Cart::class)->handle();
		$this->objectManager->get(Purchase::class)->handle();

		$this->objectManager->get(Email::class)->handle();
		$this->objectManager->get(EmailSha1::class)->handle();

		$response = $observer->getResponse();

		$dataLayerHtml = $this->googleTagManager->renderTag(new ContainerId($this->generalConfig->getContainerId()));
		$response->setContent(str_replace('<!-- GTM_PLACEHOLDER -->', $dataLayerHtml, $response->getContent()));
	}

	/**
	 * @return bool
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	private function isEnabled()
	{
		return $this->generalConfig->isEnabled();
	}
}