<?

class dpromo_nle extends CModule
{

    private $eventManager;
    private $baseDir;

    function __construct()
    {
        $arModuleVersion = array();
        include(__DIR__ . "/version.php");

        $this->MODULE_ID = "company.inner";
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = 'Вспомогательные классы';
    }


    function DoInstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION;
        RegisterModule($this->MODULE_ID);
    }

    function DoUninstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION;
        UnRegisterModule($this->MODULE_ID);
    }
}