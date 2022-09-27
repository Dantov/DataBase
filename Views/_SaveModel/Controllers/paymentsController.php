<?php

use Views\_Globals\Models\User;
use Views\vendor\libs\classes\AppCodes;

if ( !isset($isEdit) ) $isEdit = 1; // редактирование
if ( !isset($modelID) )
    if ( trueIsset($id) ) $modelID = $id;

try {
    if (User::permission('MA_design'))
    {
        if ( !$isEdit ) // Добавим стоимость дизайна только для новой модели
            if ((int)$status === 35)
                if ( !$isCurrentStatusPresent )
                    if ( $payments->addDesignPrices('sketch', $author) === -1 ) $resp_arr['MA_design'] = AppCodes::getMessage(AppCodes::NOTHING_DONE)['message'];
    }
    if ( User::permission('paymentManager') && User::permission('artCouncil') ) // зачислили дизайнеру, за утвержденный дизайн
    {
        if ((int)$status === 89)
            if ( !$isCurrentStatusPresent && $payments->isStatusPresent(35) )
                if ($payments->addDesignPrices('designOK') === -1) $resp_arr['MA_design'] = "not adding price";
    }

    if ( User::permission('MA_modeller3D') )
    {
        if ($isEdit) {
            if ( (int)$status === 8 ) // В работе 3D //было - 'Готово 3D'
                // добавим Дизайнеру за сопровождение
                if ( !$isCurrentStatusPresent && $payments->isStatusPresent(89) && $payments->isStatusPresent(35) )
                    if ($payments->addDesignPrices('escort3D') === -1) $resp_arr['MA_modeller3D'] = "not adding price";
        }
        // инициируем вставку оценок моделироания только ели есть MA_modeller3D
        // и имя FIO моделлера == FIO юзера
        if ( $request->post('ma3Dgs') && trueIsset($modeller3d) )
            $payments->addModeller3DPrices($request->post('ma3Dgs'), $modeller3d);
    }

    if (User::permission('MA_techCoord'))
    {
        if ($isEdit) {
            if ((int)$status === 1) // На проверке
                if ( !$isCurrentStatusPresent )   // && $payments->isStatusPresent(47) 47 -'Готово 3D'
                    if ($payments->addTechPrices('onVerify') === -1) $resp_arr['MA_techCoord'] = "not adding price";

            if ((int)$status === 2) // Проверено
                if (!$isCurrentStatusPresent && $payments->isStatusPresent(101) && $payments->isStatusPresent(1) )
                    if ($payments->addTechPrices('signed') === -1) $resp_arr['MA_techCoord'] = "not adding price";
        }
    }
    if (User::permission('MA_techJew')) { // Технолог Юв (Валик)
        if ($isEdit) {
            if ((int)$status === 101) // Подписано технологом
                if ( !$isCurrentStatusPresent && $payments->isStatusPresent(89) && $payments->isStatusPresent(1)  )
                    if ($payments->addTechPrices('SignedTechJew') === -1) $resp_arr['MA_techJew'] = AppCodes::getMessage(AppCodes::NOTHING_DONE)['message'];
        }
    }

    if (User::permission('MA_3dSupport'))
    {
        if ($isEdit) {
            if ((int)$status === 3) // Поддержки Убрал $isCurrentStatusPresent. Может выставлять поддержки много раз
                if ( $payments->isStatusPresent(2) )
                    if ($payments->addPrint3DPrices('supports') === -1) $resp_arr['MA_3dSupport'] = "not adding price";
        }
    }
    if (User::permission('MA_3dPrinting'))
    {
        if ($isEdit) {
            // На будущее
//        if ( $handler->isStatusPresent(2) ) // Есть Подписано - зачисляем стоимость печати
//            $handler->addPrintingPrices( $_POST['printingPrices']??[] );

            if ((int)$status === 5 ) //Выращено
                if (!$isCurrentStatusPresent && $payments->isStatusPresent(2) )
                    if ($payments->addPrint3DPrices('printed') === -1) $resp_arr['MA_3dPrinting'] = "not adding price";
        }
    }
    if (User::permission('MA_modellerJew'))
    {
        if ($isEdit) {
            // инициируем вставку оценок модельера-доработчика
            if ( trueIsset($request->post('modellerJewPrice')) && trueIsset($jewelerName) )
                $payments->addModJewPrices('add', $request->post('modellerJewPrice'), $jewelerName );
        }
    }

    //if (User::permission('MA_modellerJew')) Возможно по UserAccess 8 участок ПДО
    if ((int)$status === 41) //На сбыте
        if ( !$isCurrentStatusPresent && $payments->isStatusPresent(89) && $payments->isStatusPresent(101) )
            if ($payments->addModJewPrices('signalDone') === -1) $resp_arr['MA_3dPrinting'] = AppCodes::getMessage(AppCodes::NOTHING_DONE)['message'];

} catch (\Exception $e) { throw new \Exception($e->getMessage()); }