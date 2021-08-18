<?php


namespace Company\Inner\Migrations;


use Bitrix\Main\Loader;
use CEventMessage;
use CEventType;
use CIBlock;
use CSaleOrderProps;
use CSaleStatus;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class Base extends SymfonyCommand
{

    /** @var SymfonyStyle */
    protected $io;

    public function createEvent(array $arEventType, array $arTemplates = [])
    {
        $et = new CEventType;
        if (!$event = CEventType::GetList(['EVENT_NAME' => $arEventType['EVENT_NAME']])->fetch()) {
            $res = $et->Add($arEventType);
            if (!$res) {
                if ($this->io) $this->io->error('Cant create event ' . $arEventType['EVENT_NAME'] . '. Error: ' . $et->LAST_ERROR);
            } else {
                if ($this->io) $this->io->success('Event created ' . $res);
                $em = new CEventMessage;
                foreach ($arTemplates as $template) {
                    $res_em = $em->Add($template);
                    if (!$res_em) {
                        if ($this->io) $this->io->error('Cant create event template. Error: ' . $em->LAST_ERROR);
                    } else {
                        if ($this->io) $this->io->success('Template created ' . print_r($res_em, true));
                    }
                }
            }
        } else {
            if ($this->io) $this->io->note('Event ' . $event['EVENT_NAME'] . ' already exists');
        }
    }

    public function createIblock($arFields): int
    {
        $ib = new CIBlock;
        $ID = 0;
        if (!($arIblock = CIBlock::GetList([], ['TYPE' => $arFields['IBLOCK_TYPE_ID'], 'CODE' => $arFields['CODE'], 'CHECK_PERMISSIONS' => 'N'])->fetch())) {
            $ID = $ib->Add($arFields);
            if ($ID) {
                CIBlock::SetPermission($ID, [2 => "R"]);
                if ($this->io) $this->io->success('Iblock created ' . $arFields['CODE'] . '. Id: ' . $ID);
            } else {
                if ($this->io) $this->io->error('Cant create iblock ' . $arFields['CODE'] . '. Error: ' . $ib->LAST_ERROR);
            }
        } else {
            if ($this->io) $this->io->note('Iblock ' . $arFields['CODE'] . ' already exists');
            $ID = $arIblock['ID'];
        }
        return $ID;
    }

    /**
     * @param array $arField
     * @param array $arValues
     * @return int
     */
    public function createProp(array $arField, $arValues = []): int
    {
        if ($arValues) {
            $arField["VALUES"] = [];
            foreach ($arValues as $item) {
                $arField["VALUES"][] = array(
                    "VALUE" => $item,
                    "DEF" => "N",
                    "SORT" => "500"
                );
            }
        }
        if (!$existProp = \CIBlockProperty::GetList([], [
            "NAME" => $arField['NAME'],
            "SORT" => $arField['SORT'],
            "CODE" => $arField['CODE'],
            "PROPERTY_TYPE" => $arField['PROPERTY_TYPE'],
            "IBLOCK_ID" => $arField['IBLOCK_ID']
        ])->GetNext()) {
            $ibp = new \CIBlockProperty;
            $propID = $ibp->Add($arField);
            if ($propID) {
                if ($this->io) $this->io->success('Property ' . $arField['CODE'] . ' created. Id: ' . $propID);
                return $propID;
            } else {
                if ($this->io) $this->io->error('Cant create property ' . $arField['CODE'] . '. Error: ' . $ibp->LAST_ERROR);
                return 0;
            }
        } else {
            if ($this->io) $this->io->note('Property ' . $arField['CODE'] . ' already exists');
            return $existProp['ID'];
        }
    }

    public function createOrderProp(array $arFields)
    {
        Loader::includeModule('sale');
        if (!$propID = CSaleOrderProps::GetList([], ['CODE' => $arFields['CODE']])->fetch()['ID']) {
            $propID = CSaleOrderProps::Add($arFields);
            if ($propID > 0) {
                if ($this->io) $this->io->success('Order property ' . $arFields['CODE'] . ' created. Id: ' . $propID);
            } else {
                if ($this->io) $this->io->error('Cant create order property ' . $arFields['CODE'] . '.');
            }
        }
        return $propID > 0 ? (int) $propID : 0;
    }

    public function createOrderStatus(array $arFields)
    {
        Loader::includeModule('sale');
        if (!CSaleStatus::GetList([], ['ID' => $arFields['ID']])->fetch()) {
            $propID = CSaleStatus::Add($arFields);
            var_dump($propID);
            if ($propID > 0) {
                if ($this->io) $this->io->success('Order status ' . $arFields['ID'] . ' created.');
                return $arFields['ID'];
            } else {
                if ($this->io) $this->io->error('Cant create order status ' . $arFields['ID'] . '.');
            }
        }
        return false;
    }

    public function deleteIblock($arFields)
    {
        if ($arIblock = CIBlock::GetList([], ['TYPE' => $arFields['IBLOCK_TYPE_ID'], 'CODE' => $arFields['CODE'], 'CHECK_PERMISSIONS' => 'N'])->fetch()) {
            if ($res = CIBlock::Delete($arIblock['ID'])) {
                if ($this->io) $this->io->success('Iblock ' . $arFields['CODE'] . ' deleted');
            } else {
                if ($this->io) $this->io->note('Cant delete iblock ' . $arFields['CODE']);
            }
            return $res;
        }
        if ($this->io) $this->io->note('Cant delete iblock ' . $arFields['CODE'] . '. Iblock wasn\'t found ');
        return false;
    }

    /**
     * @param $event string|array
     */
    public function deleteEvent($event): bool
    {
        if (is_string($event) || is_array($event)) {
            CEventType::Delete($event)->affectedRowsCount;
            $rsEM = CEventMessage::GetList($by="site_id", $order="desc", ["TYPE_ID" => $event]);
            while ($arEM = $rsEM->Fetch())
            {
                CEventMessage::Delete($arEM['ID']);
            }
        } else {
            if ($this->io) $this->io->error('Input argument must be a string or an array');
            return false;
        }
        return true;
    }

    public function deleteOrderProp(string $code)
    {
        Loader::includeModule('sale');
        if ($propID = CSaleOrderProps::GetList([], ['CODE' => $code])->fetch()['ID']) {
            CSaleOrderProps::Delete($propID);
        }
    }

    public function deleteOrderStatus(string $ID)
    {
        Loader::includeModule('sale');
        return CSaleStatus::Delete($ID);
    }

}