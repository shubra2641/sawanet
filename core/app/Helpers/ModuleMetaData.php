<?php

namespace App\Helpers;

class ModuleMetaData
{
    public function __construct(public ?string $moduleName = null)
    {

    }

    public function paymentGatewayData()
    {
        $allMetaData = $this->getMetaData();
        if (property_exists($allMetaData, 'nazmartMetadata')) {
            //todo: check payment meta is available or not
            $metaInstance = $allMetaData->nazmartMetadata;
            return $this->getPaymentMetaInfo($metaInstance);
        }
        return null;
    }

    private function getMetaData()
    {
        if (moduleExists($this->moduleName)) {
            return $this->getIndividualModuleMetaData($this->moduleName);
        }

        return null;
    }

    public function renderAllPaymentGatewaySettingsBlade()
    {
        //todo return blade partials to render it in
        $outputMarkup = '';
        $allMetaInformation = $this->getAllMetaData();
        foreach ($allMetaInformation as $metaInfo) {
            $paymentMeta = $this->getPaymentMetaInfo($metaInfo);
            foreach ($paymentMeta as $inPay) {
                if (property_exists($inPay, 'settingsBlade')) {
                    if (view()->exists($inPay->settingsBlade)) {
                        $outputMarkup .= view($inPay->settingsBlade)->render();
                    }
                }
            }
        }
        return $outputMarkup;
    }

    public function getChargeCustomerMethodNameByPaymentGatewayNameSpace($gateway)
    {
        //todo return blade partials to render it in
        $allMetaInformation = $this->getAllMetaData();
        foreach ($allMetaInformation as $metaInfo) {
            $paymentMeta = $this->getPaymentMetaInfo($metaInfo);
            if ($gateway !== strtolower(current($paymentMeta)->slug)) {
                continue;
            }
            return current($paymentMeta)->chargeCustomerMethodNameSpace;
        }
        return '';
    }

    public function getChargeCustomerMethodNameByPaymentGatewayName($gateway)
    {
        //todo return blade partials to render it in
        $allMetaInformation = $this->getAllMetaData();
        foreach ($allMetaInformation as $metaInfo) {
            $paymentMeta = $this->getPaymentMetaInfo($metaInfo);
            if ($gateway !== strtolower(current($paymentMeta)->slug)) {
                continue;
            }
            return current($paymentMeta)->chargeCustomerMethodName;
        }
        return '';
    }

    public function renderAllPaymentGatewayExtraInfoBlade()
    {
        //todo return blade partials to render it in
        $outputMarkup = '';
        $allMetaInformation = $this->getAllMetaData();
        foreach ($allMetaInformation as $index => $metaInfo) {
            $paymentMeta = $this->getPaymentMetaInfo($metaInfo);
            if (!empty($paymentMeta))
            {
                foreach ($paymentMeta as $inPay) {
                    $view_file = get_module_view($index, $inPay->extraInfoMarkupBlade);
                    if (property_exists($inPay, 'extraInfoMarkupBlade')) {
                        if (view()->exists($view_file)) {
                            $outputMarkup .= '<div id="'.$inPay->slug.'-parent-wrapper">'.view($view_file)->render().'</div>';
                        }
                    }
                }
            }
        }
        return $outputMarkup;
    }

    public function saveAllPaymentGatewaySettings()
    {
        $outputMarkup = [];
        $allMetaInformation = $this->getAllMetaData();
        foreach ($allMetaInformation as $metaInfo) {
            $paymentMeta = $this->getPaymentMetaInfo($metaInfo);
            foreach ($paymentMeta as $inPay) {
                if (property_exists($inPay, 'settingsData')) {
                    $outputMarkup[] = $inPay->settingsData;
                }
            }
        }
        return $outputMarkup;
    }

    public function getAllPaymentGatewayList()
    {
        $outputMarkup = [];
        $allMetaInformation = $this->getAllMetaData();
        foreach ($allMetaInformation as $metaInfo) {
            $paymentMeta = $this->getPaymentMetaInfo($metaInfo);
            if (!empty($paymentMeta))
            {
                foreach ($paymentMeta as $meta)
                {
                    if (property_exists($meta, 'slug')) {
                        $outputMarkup[] = $meta->slug;
                    }
                }
            }
        }

        return $outputMarkup;
    }

    public function getAllPaymentGatewayListWithImage()
    {
        $eachIndex=0;
        $outputMarkup = [];
        $allMetaInformation = $this->getAllMetaData();
        foreach ($allMetaInformation as $index => $metaInfo) {
            $paymentMeta = $this->getPaymentMetaInfo($metaInfo);
            if (!empty($paymentMeta))
            {
                foreach ($paymentMeta as $key => $meta)
                {
                    if (property_exists($meta, 'slug')) {
                        $outputMarkup[$eachIndex]['name'] = $meta->slug;
                        $outputMarkup[$eachIndex]['image'] = $this->getPaymentGatewayImagePath($meta->slug);
                        $outputMarkup[$eachIndex]['module'] = $index;
                    }
                    $eachIndex++;
                }
            }
        }

        return $outputMarkup;
    }

    private function getPaymentMetaInfo($metaInstance)
    {
        $paymentGateway = [];
        if (property_exists($metaInstance, 'paymentGateway')) {
            $paymentGateway[] = $metaInstance->paymentGateway;
        }

        return $paymentGateway;
    }

    public function getAllMetaData()
    {
        $allModuleMeta = [];
        $allDirectories = glob(base_path() . '/Modules/*', GLOB_ONLYDIR);
        foreach ($allDirectories as $dire) {
            //todo scan all the json file
            $currFolderName = pathinfo($dire, PATHINFO_BASENAME);
            $metaInformation = $this->getIndividualModuleMetaData($currFolderName);
            if (property_exists($metaInformation, 'nazmartMetaData')) {
                $allModuleMeta[$currFolderName] = $metaInformation->nazmartMetaData;
            }
        }

        return $allModuleMeta;
    }

    private function getIndividualModuleMetaData(string $moduleName, bool $returnType = false)
    {
        $filePath = module_path($moduleName) . '/module.json';
        if (file_exists($filePath) && !is_dir($filePath)) {
            return json_decode(file_get_contents($filePath), $returnType);
        }
    }

    public function getIndividualModulePath(string $moduleName)
    {
        $name = '';
        $moduleMeta = $this->getIndividualModuleMetaData($moduleName);
        if (!empty($moduleMeta) && property_exists($moduleMeta, 'name')) {
            $name = $moduleMeta->name;
        }
        return $name;
    }

    public function getPageBuilderAddonList()
    {
        $addonNames = [];
        $allModuleMeta = $this->getAllMetaData();
        if (!empty($allModuleMeta)) {
            $addonNames = $this->getOnlyPageBuilder($allModuleMeta);
        }

        return $addonNames;
    }

    private function getOnlyPageBuilder($allModuleMeta)
    {
        $classList = [];
        foreach ($allModuleMeta as $eachModuleMeta) {
            if (property_exists($eachModuleMeta, 'pageBuilderAddon')) {
                $pageBuilderAddon = $eachModuleMeta->pageBuilderAddon;
                if (!empty($pageBuilderAddon)) {
                    $classList = $this->addonPath($pageBuilderAddon);
                }
            }
        }

        return $classList;
    }

    public function getWidgetBuilderAddonList()
    {
        $addonNames = [];
        $allModuleMeta = $this->getAllMetaData();

        if (!empty($allModuleMeta)) {
            $addonNames = $this->getOnlyWidgetBuilder($allModuleMeta);
        }

        return $addonNames;
    }

    private function getOnlyWidgetBuilder($allModuleMeta)
    {
        $classList = [];
        foreach ($allModuleMeta as $eachModuleMeta) {
            if (property_exists($eachModuleMeta, 'widgetBuilderAddon')) {
                $widgetBuilderAddon = $eachModuleMeta->widgetBuilderAddon;
                if (!empty($widgetBuilderAddon)) {
                    $classList = $this->addonPath($widgetBuilderAddon);
                }
            }
        }

        return $classList;
    }

    private function addonPath($widgetBuilderAddon)
    {
        $addonNames = [];
        foreach ($widgetBuilderAddon as $addon) {
            if (file_exists(str_replace('\\', '/', base_path($addon)) . '.php')) {
                $addonNames[] = $addon;
            }
        }

        return $addonNames;
    }

    public function getAllExternalMenu()
    {
        $allModuleMeta = $this->getAllMetaData();
        return $this->getEachMenu($allModuleMeta);
    }

    private function getEachMenu($allModuleMeta)
    {
        $menuList = [];
        if (!empty($allModuleMeta)) {
            foreach ($allModuleMeta ?? [] as $metaData)
            {
                $adminSettings = $this->getAdminSettings($metaData);
                $menuItem = $this->getAdminMenuSettings($adminSettings);
                if (!empty((array)$menuItem))
                {
                    $menuList[] = $menuItem;
                }
            }
        }

        return $menuList;
    }

    public function getAdminSettings($metaData)
    {
        $adminSettings = [];
        if (property_exists($metaData, 'admin_settings'))
        {
            $adminSettings = $metaData->admin_settings;
        }

        return $adminSettings;
    }

    public function getAdminMenuSettings($adminSettings)
    {
        $menuItem = [];
        if (property_exists($adminSettings, 'menu_item') && !empty($adminSettings->menu_item))
        {
            $menuItem = $adminSettings->menu_item;
        }

        return $menuItem;
    }

    public function getExternalPaymentGateway()
    {
        $allModuleMeta = $this->getAllMetaData();
        return $this->getEachPaymentMetaData($allModuleMeta);
    }

    private function getEachPaymentMetaData($allModuleMeta)
    {
        $paymentMeta = [];
        if (!empty($allModuleMeta))
        {
            foreach ($allModuleMeta as $metaItem)
            {
                if (property_exists($metaItem, 'paymentGateway'))
                {
                    $paymentMeta[] = $metaItem->paymentGateway;
                }
            }
        }

        return $paymentMeta;
    }

    public function getPaymentGatewayImagePath($paymentGatewaySlug)
    {
        $file_name = '';
        $allMetaData = $this->getExternalPaymentGateway();
        if (!empty($allMetaData))
        {
            foreach ($allMetaData as $eachMeta)
            {
                if (property_exists($eachMeta, 'slug'))
                {
                    if ($eachMeta->slug == $paymentGatewaySlug)
                    {
                        if (property_exists($eachMeta, 'logo_file'))
                        {
                            $file_name = $eachMeta->logo_file;
                        }
                    }
                }
            }
        }

        return $file_name;
    }

    public function renderPaymentGatewayImage($imageName, $moduleName)
    {
        $moduleDir = '';
        if (!empty($imageName) && !empty($moduleName))
        {
            $moduleDir = '<img src="' . global_asset(module_dir($moduleName).'assets/payment-gateway-image/'.$imageName) . '"/>';
        }

        return $moduleDir;
    }
}
