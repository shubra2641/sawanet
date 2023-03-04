<?php

use App\Facades\ModuleDataFacade;

function getAllExternalMenu()
{
    return ModuleDataFacade::getAllExternalMenu();
}

function getExternalPaymentGateway()
{
    return ModuleDataFacade::getExternalPaymentGateway();
}

function getAllPaymentGatewayList()
{
    return ModuleDataFacade::getAllPaymentGatewayList();
}

function getAllPaymentGatewayListWithImage()
{
    return ModuleDataFacade::getAllPaymentGatewayListWithImage();
}

/**
 * @param $imageName
 * @param $moduleName
 * @return string
 */
function renderPaymentGatewayImage($imageName, $moduleName): string
{
    return ModuleDataFacade::renderPaymentGatewayImage($imageName, $moduleName);
}

function renderAllPaymentGatewayExtraInfoBlade()
{
    return ModuleDataFacade::renderAllPaymentGatewayExtraInfoBlade();
}

/**
 * @param $payment_gateway_name
 * @return mixed
 */
function getChargeCustomerMethodNameByPaymentGatewayNameSpace($payment_gateway_name): mixed
{
    return ModuleDataFacade::getChargeCustomerMethodNameByPaymentGatewayNameSpace($payment_gateway_name);
}

/**
 * @param $payment_gateway_name
 * @return mixed
 */
function getChargeCustomerMethodNameByPaymentGatewayName($payment_gateway_name): mixed
{
    return ModuleDataFacade::getChargeCustomerMethodNameByPaymentGatewayName($payment_gateway_name);
}
