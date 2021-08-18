<?php

namespace Company\Inner\Migrations;

class InstallerRequest extends Base
{

    public function install()
    {
        $this->makeIblock();
        $this->createMail();
        $this->makeOrderProp();
        $this->makeOrderStatus();
    }

    public function delete()
    {
        $this->removeIblock();
        $this->removeMail();
        $this->removeOrderProp();
        $this->removeOrderStatus();
    }

    public function makeIblock(): void
    {
        $arFields = array(
            "ACTIVE" => 'Y',
            "IBLOCK_TYPE_ID" => 'information',
            "NAME" => 'Заявки установщиков',
            "CODE" => 'installerrequest',
            "LID" => 's1',
            "INDEX_ELEMENT" => 'N',
            "WORKFLOW" => 'N',
        );
        if ($ID = $this->createIblock($arFields)) {
            $this->makeProps($ID);
        }
    }

    /**
     * @param int $ID
     */
    public function makeProps(int $ID): void
    {
        $arFields = [
            [
                "NAME" => "ФИО",
                "CODE" => "FIO",
                "ACTIVE" => "Y",
                "SORT" => "500",
                "PROPERTY_TYPE" => "S",
                "IBLOCK_ID" => $ID,
            ],
            [
                "NAME" => "Номер телефона",
                "CODE" => "PHONE",
                "ACTIVE" => "Y",
                "SORT" => "500",
                "PROPERTY_TYPE" => "S",
                "IBLOCK_ID" => $ID,
            ],
            [
                "NAME" => "E-mail",
                "CODE" => "EMAIL",
                "ACTIVE" => "Y",
                "SORT" => "500",
                "PROPERTY_TYPE" => "S",
                "IBLOCK_ID" => $ID,
            ],
            [
                "NAME" => "Пароль",
                "CODE" => "PASSWORD",
                "ACTIVE" => "Y",
                "SORT" => "500",
                "PROPERTY_TYPE" => "S",
                "IBLOCK_ID" => $ID,
            ],
            [
                "NAME" => "ИНН",
                "CODE" => "INN",
                "ACTIVE" => "Y",
                "SORT" => "500",
                "PROPERTY_TYPE" => "S",
                "IBLOCK_ID" => $ID,
            ],
            [
                "NAME" => "ОГРНИП",
                "CODE" => "OGRNIP",
                "ACTIVE" => "Y",
                "SORT" => "500",
                "PROPERTY_TYPE" => "S",
                "IBLOCK_ID" => $ID,
            ],
            [
                "NAME" => "Серия и номер паспорта",
                "CODE" => "PNUMBER",
                "ACTIVE" => "Y",
                "SORT" => "500",
                "PROPERTY_TYPE" => "S",
                "IBLOCK_ID" => $ID,
            ],
            [
                "NAME" => "Дата выдачи паспорта",
                "CODE" => "PDATE",
                "ACTIVE" => "Y",
                "SORT" => "500",
                "PROPERTY_TYPE" => "S",
                "IBLOCK_ID" => $ID,
            ],
            [
                "NAME" => "Кем выдан паспорт",
                "CODE" => "PWHOM",
                "ACTIVE" => "Y",
                "SORT" => "500",
                "PROPERTY_TYPE" => "S",
                "IBLOCK_ID" => $ID,
            ],
            [
                "NAME" => "Адрес регистрации",
                "CODE" => "PADDRESS",
                "ACTIVE" => "Y",
                "SORT" => "500",
                "PROPERTY_TYPE" => "S",
                "IBLOCK_ID" => $ID,
            ],
            [
                "NAME" => "Почтовый адрес",
                "CODE" => "ADDRESS",
                "ACTIVE" => "Y",
                "SORT" => "500",
                "PROPERTY_TYPE" => "S",
                "IBLOCK_ID" => $ID,
            ],
        ];
        foreach ($arFields as $propFields) {
            $this->createProp($propFields);
        }
    }

    public function createMail()
    {
        $arCEventType = array(
            'LID' => LANGUAGE_ID,
            'EVENT_NAME' => 'NEW_USER_INST',
            'NAME' => 'Регистрация установщика',
            'DESCRIPTION' => '#EMAIL# E-mail получателя',
        );

        $arLids = [];
        $rs = \CSite::GetList($by, $order);
        while ($LID = $rs->fetch()['LID']) $arLids[] = $LID;

        $arCEventTemplates = [
            [
                'ACTIVE' => 'Y',
                'EVENT_NAME' => $arCEventType['EVENT_NAME'],
                'LID' => $arLids,
                'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#',
                'EMAIL_TO' => '#EMAIL#',
                'SUBJECT' => 'Ваша заявка на регистрацию принята и находится в обработке',
                'BODY_TYPE' => 'html',
                'MESSAGE' => 'Уважаемый, #NAME#!<br>
<br>
Ваша заявка на регистрацию принята и находится в обработке. <br>
<br>
В ближайшее время наши сотрудники свяжутся с Вами.<br>
<br>
Благодарим за сотрудничество!'
            ]
        ];

        $this->createEvent($arCEventType, $arCEventTemplates);
    }

    public function makeOrderProp()
    {
        $this->createOrderProp([
            "PERSON_TYPE_ID" => 1,
            "NAME" => "Заказ-наряд установщика",
            "TYPE" => "CHECKBOX",
            "REQUIED" => "Y",
            "DEFAULT_VALUE" => "F",
            "SORT" => 100,
            "CODE" => "IS_INSTALLER",
            "USER_PROPS" => "N",
            "IS_LOCATION" => "N",
            "IS_LOCATION4TAX" => "N",
            "PROPS_GROUP_ID" => 1,
            "SIZE1" => 0,
            "SIZE2" => 0,
            "DESCRIPTION" => "",
            "IS_EMAIL" => "N",
            "IS_PROFILE_NAME" => "N",
            "IS_PAYER" => "N"
        ]);
    }

    public function makeOrderStatus()
    {
        $this->createOrderStatus([
            "ID" => "W",
            "SORT" => 150,
            "LANG" => [["LID" => "ru", "NAME" => "В работе", "DESCRIPTION" => ""]],
            "PERMS" => [["GROUP_ID" => 7, "PERM_DELETE" => "Y", "PERM_CANCEL" => "Y", "PERM_DEDUCTION" => "Y", "PERM_DELIVERY" => "Y", "PERM_MARK" => "Y", "PERM_PAYMENT" => "Y", "PERM_STATUS" => "Y", "PERM_STATUS_FROM" => "Y", "PERM_UPDATE" => "Y", "PERM_VIEW" => "Y"]]
        ]);
    }

    public function removeIblock()
    {
        $arFields = [
            "IBLOCK_TYPE_ID" => 'information',
            "CODE" => 'installerrequest',
        ];
        $this->deleteIblock($arFields);
    }

    public function removeMail()
    {
        $this->deleteEvent('NEW_USER_INST');
    }

    public function removeOrderProp()
    {
        $this->deleteOrderProp('IS_INSTALLER');
    }

    public function removeOrderStatus()
    {
        $this->deleteOrderProp('W');
    }

}