<?php


namespace Company\Inner;


use Bitrix\Main\GroupTable;
use Bitrix\Main\UserGroupTable;
use CUserFieldEnum;

class User extends Singleton
{

    protected $id = null;

    public static function getInstance(?int $id = null)
    {
        global $USER;
        $id = $id ?: $USER->GetID();
        $entity = parent::getInstance();
        $entity->setId($id);
        return $entity;
    }

    /**
     * @return null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }


    public function isDealer(): bool
    {
        if ($this->getId()) {
            static $cache = [];
            if (!isset($cache[$this->getId()])) {
                $cache[$this->getId()] = !!(UserGroupTable::getRow([
                    'filter' => [
                        'USER_ID' => $this->getId(),
                        'GROUP.STRING_ID' => 'DEALER'
                    ],
                    'runtime' => [
                        'GROUP' => [
                            'data_type' => GroupTable::getEntity(),
                            'reference' => [
                                '=this.GROUP_ID' => 'ref.ID'
                            ]
                        ]
                    ]
                ]));
            }
            return $cache[$this->getId()];
        }
        return false;
    }

   public function isEDM(): bool
    {
        return !!$this->getData()['UF_EDM'];
    }

    public function getData(): array
    {
        if ($this->getId()) {
            static $cache = [];
            if (!isset($cache[$this->getId()])) {
                global $USER;
                if ($arUser = $USER->GetByID($this->getId())->fetch()) {
                    $cache[$this->getId()] = $arUser;
                } else {
                    $cache[$this->getId()] = [];
                }
            }
            return $cache[$this->getId()];
        }
        return [];
    }

    public function getLogin()
    {
        return $this->getData()['LOGIN'] ?? '';
    }

    public function getFilial(): array
    {
        if ($id = $this->getData()['UF_CURRENT_FILIAL']) {
            $rs = CUserFieldEnum::GetList(array(), array(
                "ID" => $id,
            ));
            if($arField = $rs->GetNext()) {
                return $arField;
            }
        }
        return [];
    }

    public function get1cCode(): string
    {
        return $this->getData()['UF_CODE'] ?? '';
    }


}