<?php
/**
 * This Software is the property of Data Development and is protected
 * by copyright law - it is NOT Freeware.
 * http://www.shopmodule.com
 *
 * @copyright (C) D3 Data Development (Inh. Thomas Dartsch)
 * @author        D3 Data Development - <support@shopmodule.com>
 * @link          http://www.oxidmodule.com
 */

/**
 * Alle Anforderungen sind über $this->_aCheck konfigurierbar. Manche Anforderungen haben dazu noch weitergehende
 * Informationen. Die Struktur dieser Requirementbeschreibungen:
 *
 * array(
 *      'blExec'    => 1,           // obligatorisch: 0 = keine Prüfung, 1 = Püfung wird ausgeführt
 *      'aParams'   => array(...),  // optional, Inhalt ist von jeweiliger Prüfung abhängig
 * )
 *
 * "Desc1": Diese Struktur kann allein eine Bedingung beschreiben. Wenn mehrere dieser Bedingungen
 * nötig sind (z.B. bei unterschiedlichen Bibliotheksanforderungen), kann diese Struktur als
 * Arrayelemente auch mehrfach genannt werden (kaskadierbar). Grundsätzlich sind alle Requirements
 * kaskadierbar, jedoch ergibt dies nicht bei allen Sinn. :) Eine Kaskadierung sieht so aus:
 *
 * array(
 *      array(
 *          'blExec'    => 1,
 *          ...
 *      ),
 *      array(
 *          'blExec'    => 1,
 *          ...
 *      )
 * )
 *
 * Unbedingt zu vermeiden sind Änderungen in der Scriptlogik, da diese bei Updates nur schwer zu übernehmen sind.
 */

class requConfig
{
    public $sModName = 'D&sup3; 2-Faktor-Authentisierung';

    public $sModId   = 'd3totp';

    public $sModVersion = '1.0.0.0';

    /********************** check configuration section ************************/

    public $aCheck = array(
        // kleinste erlaubte PHP-Version
        'hasMinPhpVersion'       => array(
            'blExec'  => 0,
            'aParams' => array(
                'version' => '5.6.0'
            )
        ),

        // größte erlaubte PHP-Version
        'hasMaxPhpVersion'       => array(
            'blExec'  => 0,
            'aParams' => array(
                'version' => '7.1.200'
            )
        ),

        // PHP-Version zwischen 'from' und 'to'
        'hasFromToPhpVersion'    => array(
            'blExec'  => 1,
            'aParams' => array(
                'from' => '5.6.0',
                'to'   => '7.2.900',
            )
        ),

        // benötigt Zend Optimizer (PHP 5.2) bzw. Zend Guard Loader (> PHP 5.2)
        'hasZendLoaderOptimizer' => array(
            'blExec' => 0,
        ),

        // benötigt IonCubeLoader
        'hasIonCubeLoader'       => array(
            'blExec' => 0,
        ),

        // benötigt Zend Decoder oder IonCubeLoader
        'hasIonCubeOrZendLoader'       => array(
            'blExec' => 0,
        ),

        // benötigte PHP-Extension (kaskadierbar (siehe "Desc1"))
        'hasExtension'           => array(
            array(
                'blExec'  => 0,
                'aParams' => array(
                    'type' => 'OpenSSL',
                ),
            ),
            array(
                'blExec'  => 0,
                'aParams' => array(
                    'type' => 'soap'
                ),
            ),
        ),

        // benötigte cURL-Version
        'hasMinCurlVersion'           => array(
            'blExec'  => 0,
            'aParams' => array(
                'version' => '7.26.0',
            ),
        ),

        // benötigte OpenSSL-Version (Angabe in Versionsformat)
        'hasMinOpenSSLVersion'           => array(
            'blExec'  => 0,
            'aParams' => array(
                'version' => '1.0.1.5',
            ),
        ),

        // minimal benötigte Compilationversion (editionsgetrennt), wird (sofern möglich) Remote aktualisiert
        'hasMinShopVersion'      => array(
            'blExec'  => 1,
            'aParams' => array(
                'PE' => '6.0.0',
                'CE' => '6.0.0',
                'EE' => '6.0.0'
            ),
        ),

        // maximal verwendbare Compilationversion (editionsgetrennt), wird (sofern möglich) Remote aktualisiert
        'hasMaxShopVersion'      => array(
            'blExec'  => 1,
            'aParams' => array(
                'PE' => '6.1.0',
                'CE' => '6.1.0',
                'EE' => '6.1.0'
            ),
        ),

        // verfügbar für diese Shopeditionen, wird (sofern möglich) Remote aktualisiert
        'isShopEdition'          => array(
            'blExec'  => 1,
            'aParams' => array(
                array(
                    'PE',
                    'EE',
                    'CE',
                ),
            ),
        ),

        // benötigt Modul-Connector
        'hasModCfg'              => array(
            'blExec' => 1
        ),

        // benötigt mindestens diese Erweiterungen / Version lt. d3_cfg_mod (kaskadierbar (siehe "Desc1"))
        'hasMinModCfgVersion'    => array(
            array(
                'blExec'  => 0,
                'aParams' => array(
                    'id'      => 'd3modcfg_lib',
                    'name'    => 'Modul-Connector',
                    'version' => '5.1.0.0',
                ),
            ),
        ),

        // verwendbar bis zu diesen Erweiterungen / Version lt. d3_cfg_mod (kaskadierbar (siehe "Desc1"))
        'hasMaxModCfgVersion'    => array(
            array(
                'blExec'  => 0,
                'aParams' => array(
                    'id'      => 'd3modcfg_lib',
                    'name'    => 'Modul-Connector',
                    'version' => '5.1.0.0',
                ),
            ),
        ),

        // benötigt neuen Lizenzschlüssel
        'requireNewLicence'    => array(
            array(
                'blExec'  => 0,
                'aParams' => array(
                    'checkVersion' => true, // soll Versionsnummer des installierten Moduls gegengeprüft werden?
                    'remainingDigits' => 2, // zu prüfende Stellen für neue Lizenz
                ),
            ),
        ),
    );
}

/********* don't change content from here **********************/

date_default_timezone_set('Europe/Berlin');

/**
 * Class requcheck
 */
class requCheck
{
    public $sVersion = '5.0.0';

    protected $_db = false;

    public $dbHost;

    public $dbUser;

    public $dbPwd;

    public $dbName;

    /** @var requConfig */
    public $oConfig;

    /** @var requLayout */
    public $oLayout;

    protected $_sInFolderFileName = 'd3precheckinfolder.php';

    public $sVersionTag = '@@version@@';

    /********************** functional section ************************/

    public $blGlobalResult = true;

    /**
     *
     */
    public function __construct()
    {
        $this->oConfig = new requConfig();
        $this->oLayout = new requLayout($this, $this->oConfig);
        $this->oRemote = new requRemote();
    }

    /**
     * @param string $sName
     * @param array $aArguments
     */
    public function __call ($sName, $aArguments)
    {
        $this->oLayout->{$sName}($aArguments);
    }

    public function startCheck()
    {
        $this->oLayout->getHTMLHeader();

        $oCheckTransformation = new requTransformation($this);
        $this->oConfig->aCheck = $oCheckTransformation->transformCheckList($this->oConfig->aCheck);

        $this->_runThroughChecks($this->oConfig->aCheck);

        $this->oLayout->getHTMLFooter();
    }

    /**
     * traversable requirement check
     *
     * @param        $aCheckList
     * @param string $sForceCheckType
     */
    protected function _runThroughChecks($aCheckList, $sForceCheckType = '')
    {
        foreach ($aCheckList as $sCheckType => $aConf) {
            if (array_key_exists('blExec', $aConf)) {
                if ($aConf['blExec']) {
                    if (strlen($sForceCheckType)) {
                        $sCheckType = $sForceCheckType;
                    }
                    $this->displayCheck($sCheckType, $aConf);
                }
            } else {
                $this->_runThroughChecks($aConf, $sCheckType);
            }
        }
    }

    /**
     * @param      $sMethodName
     * @param null $aArguments
     *
     * @return array
     */
    public function checkInSubDirs($sMethodName, $aArguments = null)
    {
        $sFolder = '.';

        $aCheckScripts = $this->_walkThroughDirs($sFolder);
        $aReturn       = $this->_checkScripts($aCheckScripts, $sMethodName, $aArguments);

        return $aReturn;
    }

    /**
     * @param $sFolder
     *
     * @return array
     */
    protected function _walkThroughDirs($sFolder)
    {
        $aIgnoreDirItems = array('.', '..');
        $aCheckScripts = array();

        try {
            /** @var SplFileInfo $oFileInfo */
            $oIterator = new RecursiveDirectoryIterator($sFolder);
        
            foreach ($oIterator as $oFileInfo) {
                if (in_array($oFileInfo->getFileName(), $aIgnoreDirItems)) {
                    continue;
                }
                if ($oFileInfo->isDir()) {
                    $aCheckScripts = array_merge($aCheckScripts, $this->_walkThroughDirs($oFileInfo->getRealPath()));
                } elseif ($oFileInfo->isFile()) {
                    if (strtolower($oFileInfo->getFilename()) == $this->_sInFolderFileName) {
                        $aCheckScripts[] = str_replace('\\', '/', $oFileInfo->getRealPath());
                    }
                }
            }
        } catch (UnexpectedValueException $oEx) {
            sprintf($this->oLayout->translate('unableExecuteDirectoryIterator'), $oEx->getMessage());
            $this->addMessage(
                sprintf($this->oLayout->translate('unableExecuteDirectoryIterator'), $oEx->getMessage())
            );
        }

        return $aCheckScripts;
    }
    
    public function addMessage($sMessage)
    {
        $this->aMessages[md5($sMessage)] = $sMessage;
    }
    
    public function getMessages()
    {
        return $this->aMessages;
    }

    /**
     * @param $aScriptList
     * @param $sMethodName
     * @param $aArguments
     *
     * @return array
     */
    protected function _checkScripts($aScriptList, $sMethodName, $aArguments)
    {
        $aReturn = array();

        foreach ($aScriptList as $sScriptPath) {
            $sUrl                                       = $this->_getFolderCheckUrl(
                $sScriptPath,
                $sMethodName,
                $aArguments
            );

            $sVersionUrl = $this->_getFolderCheckUrl(
                $sScriptPath,
                'getVersion',
                array()
            );

            $sContent = serialize(null);
            $sVersion = serialize(null);

            if ($this->_hasCurl()) {
                $sContent = $this->_getContentByCurl($sUrl);
                $sVersion = $this->_getContentByCurl($sVersionUrl);
            } elseif ($this->_hasAllowUrlFopen()) {
                $sContent = file_get_contents($sUrl);
                $sVersion = file_get_contents($sVersionUrl);
            }

            $sBasePath = $this->getBasePath($sScriptPath);
            $aReturn[$sBasePath] = unserialize($sContent);
            $aReturn[$this->sVersionTag][$sBasePath] = unserialize($sVersion);
        }

        return $aReturn;
    }

    /**
     * @return bool
     */
    protected function _hasCurl()
    {
        if (extension_loaded('curl') && function_exists('curl_init')) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function _hasAllowUrlFopen()
    {
        if (ini_get('allow_url_fopen')) {
            return true;
        }

        return false;
    }

    /**
     * @param $sUrl
     *
     * @return bool|mixed
     */
    protected function _getContentByCurl($sUrl)
    {
        $iTimeOut = 5;
        $ch = curl_init();
        $sCurl_URL = preg_replace('@^((http|https)://)@', '', $sUrl);

        curl_setopt($ch, CURLOPT_URL, $sCurl_URL);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $iTimeOut);
        curl_setopt($ch, CURLOPT_TIMEOUT, $iTimeOut);
        curl_setopt($ch, CURLOPT_POST, 0);
        $sContent = curl_exec($ch);
        curl_close($ch);

        if (false == $sContent ||
            strstr(strtolower($sContent), strtolower('Request Entity Too Large')) ||
            strstr(strtolower($sContent), strtolower('not allow request data with POST requests'))
        ) {
            return false;
        }

        return $sContent;
    }

    /**
     * @param $sScriptPath
     * @param $sMethodName
     * @param $aArguments
     *
     * @return string
     */
    protected function _getFolderCheckUrl($sScriptPath, $sMethodName, $aArguments)
    {
        $sBaseDir = str_replace(
            array(basename($_SERVER['SCRIPT_FILENAME']), '\\'),
            array('', '/'),
            realpath($_SERVER['SCRIPT_FILENAME'])
        );
        $sUrlAdd  = str_replace($sBaseDir, '', $sScriptPath);
        $sBaseUrl = 'http://' . $_SERVER['HTTP_HOST'] . str_replace(
            basename($_SERVER['SCRIPT_NAME']),
            '',
            $_SERVER['SCRIPT_NAME']
        );

        $sUrl = $sBaseUrl . $sUrlAdd . '?fnc=' . $sMethodName . '&params=' . urlencode(serialize($aArguments));

        return $sUrl;
    }

    /**
     * @param null $sFolder
     *
     * @return mixed
     */
    public function getBasePath($sFolder = null)
    {
        if (!$sFolder) {
            $sFolder = $_SERVER['SCRIPT_FILENAME'];
        }

        $sScriptFileName = str_replace('\\', '/', realpath($_SERVER['SCRIPT_FILENAME']));
        $sSearch         = substr(str_replace(basename($sScriptFileName), '', $sScriptFileName), 0, -1);

        $sFolder = str_replace('\\', '/', realpath($sFolder));

        return str_replace(array(basename($sFolder), $sSearch), '', $sFolder);
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->sVersion;
    }

    /**
     * @param $mResult
     *
     * @return bool
     */
    protected function _hasFalseInResult($mResult)
    {
        if (is_array($mResult)) {
            foreach ($mResult as $blResult) {
                if (false === $blResult) {
                    $this->blGlobalResult = false;

                    return true;
                }
            }

            return false;
        }

        if (false === $mResult) {
            $this->blGlobalResult = false;

            return true;
        }

        return false;
    }

    /**
     * @param $mResult
     *
     * @return bool
     */
    protected function _hasNullInResult($mResult)
    {
        if (is_array($mResult)) {
            foreach ($mResult as $blResult) {
                if ($blResult === null) {
                    $this->blGlobalResult = false;

                    return true;
                }
            }

            return false;
        }

        if ($mResult === null) {
            $this->blGlobalResult = false;
            return true;
        }

        return false;
    }

    /**
     * @param $mResult
     *
     * @return bool
     */
    protected function _hasNoticeInResult($mResult)
    {
        if (is_array($mResult)) {
            foreach ($mResult as $blResult) {
                if ($blResult === 'notice') {
                    return true;
                }
            }

            return false;
        }

        if ($mResult === 'notice') {
            return true;
        }

        return false;
    }

    /********************** conversion function section ************************/

    /**
     * @param $mParam
     */
    public function aTos(&$mParam)
    {
        if (is_array($mParam)) {
            $mParam = implode($this->oLayout->translate('or'), $mParam);
        }
    }

    /**
     * @return string
     */
    public function getLang()
    {
        if (isset($_REQUEST['lang'])) {
            return strtolower($_REQUEST['lang']);
        }

        return 'de';
    }

    /**
     * @return bool|resource
     */
    public function getDb()
    {
        if (!$this->_db) {
            if (file_exists('config.inc.php')) {
                require_once('config.inc.php');
                ini_set('error_reporting', E_ALL^E_NOTICE);
                $this->_db = mysqli_connect($this->dbHost, $this->dbUser, $this->dbPwd, $this->dbName);
            }
        }

        return $this->_db;
    }

    /**
     * @param     $version
     * @param int $iUnsetPart
     *
     * @return string
     */
    public function versionToInt($version, $iUnsetPart = 0)
    {
        $match = explode('.', $version);

        return sprintf(
            '%d%03d%03d%03d',
            $this->_getVersionDigit($match[0], $iUnsetPart),
            $this->_getVersionDigit($match[1], $iUnsetPart),
            $this->_getVersionDigit($match[2], $iUnsetPart),
            $this->_getVersionDigit($match[3], $iUnsetPart)
        );
    }

    /**
     * @param $sMatch
     * @param $iUnsetPart
     *
     * @return int
     */
    protected function _getVersionDigit($sMatch, $iUnsetPart)
    {
        return intval($sMatch !== null ? $sMatch : $iUnsetPart);
    }

    /********************** layout function section ************************/

    public function deleteme()
    {
        $sFolder = '.';

        $this->_checkDelFilesInDir($sFolder);
        $this->_delFile($_SERVER['SCRIPT_FILENAME']);

        if (is_file($_SERVER['SCRIPT_FILENAME'])) {
            exit($this->oLayout->translate('unableDeleteFile'));
        } else {
            exit($this->oLayout->translate('goodBye'));
        }
    }

    /**
     * @param $sFolder
     */
    protected function _checkDelFilesInDir($sFolder)
    {
        $aIgnoreDirItems = array('.', '..');

        /** @var SplFileInfo $oFileInfo */
        foreach (new RecursiveDirectoryIterator($sFolder) as $oFileInfo) {
            if (!in_array($oFileInfo->getFileName(), $aIgnoreDirItems) && $oFileInfo->isDir()) {
                $this->_checkDelFilesInDir($oFileInfo->getRealPath());
            } elseif ($oFileInfo->isFile()) {
                if (strtolower($oFileInfo->getFilename()) == $this->_sInFolderFileName) {
                    $this->_delFile(str_replace('\\', '/', $oFileInfo->getRealPath()));
                }
            }
        }
    }

    /**
     * @param $sPath
     */
    protected function _delFile($sPath)
    {
        unlink($sPath);
    }

    /**
     * @param $sCheckType
     * @param $aConfiguration
     */
    public function displayCheck($sCheckType, &$aConfiguration)
    {
        $sGenCheckType = preg_replace("@(\_[0-9]$)@", "", $sCheckType);
        $oTests = new requTests($this, $this->oConfig, $this->getDb(), $this->oRemote);
        if (method_exists($oTests, $sGenCheckType)) {
            $this->_checkResult($oTests, $sGenCheckType, $sCheckType, $aConfiguration);
        } else {
            $this->oLayout->getUncheckableItem($sCheckType, $aConfiguration);
            $this->blGlobalResult = false;
        }
    }

    /**
     * @param $oTests
     * @param $sGenCheckType
     * @param $sCheckType
     * @param $aConfiguration
     */
    protected function _checkResult($oTests, $sGenCheckType, $sCheckType, $aConfiguration)
    {
            $mResult = $oTests->{$sGenCheckType}($aConfiguration);
            $sElementId = (md5($sGenCheckType . serialize($aConfiguration)));

            if ($this->_hasNoticeInResult($mResult)) {
                $this->oLayout->getUnknownItem($mResult, $sElementId, $sCheckType, $aConfiguration);
            } elseif ($this->_hasNullInResult($mResult)) {
                $this->oLayout->getUnknownItem($mResult, $sElementId, $sCheckType, $aConfiguration);
            } elseif ($this->_hasFalseInResult($mResult)) {
                $this->oLayout->getNoSuccessItem($mResult, $sElementId, $sCheckType, $aConfiguration);
            } else {
                $this->oLayout->getSuccessItem($mResult, $sElementId, $sCheckType, $aConfiguration);
            }
    }

    public function showinfo()
    {
        phpinfo();
    }
}

/**
 * Class requLayout
 */
class requLayout
{
    public $oBase;
    public $oConfig;

    /**
     * @param requCheck  $oBase
     * @param requConfig $oConfig
     */
    public function __construct(requCheck $oBase, requConfig $oConfig)
    {
        $this->oBase = $oBase;
        $this->oConfig = $oConfig;
    }

    public function getHTMLHeader()
    {
        $sScriptName      = $_SERVER['SCRIPT_NAME'];
        $sTranslRequCheck = $this->translate('RequCheck');
        $sModName         = $this->oConfig->sModName;
        $sModVersion      = $this->oConfig->sModVersion;

        echo <<< EOT
            <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
            <html>
                <head>
                    <title>
                        $sTranslRequCheck "$sModName" $sModVersion
                    </title>
                    <meta http-equiv="Content-Type" content="text/html;charset=ISO8859-15">
                    <style type="text/css">
                        <!--
                        body {
                            background: #FFF url($sScriptName?fnc=getGifBg) repeat-x;
                            font: 13px Trebuchet MS,Tahoma,Verdana,Arial,Helvetica,sans-serif;
                        }
                        div.langswitch {
                            clear: both;
                            float: none;
                            height: 13px;
                            margin: 10px 0 25px;
                        }
                        .langswitch a {
                            text-decoration: none;
                            margin-right: 5px;
                            width: 18px;
                            height: 13px;
                            display: block;
                            float: left;
                        }
                        .btn_1 {
                            background: url($sScriptName?fnc=getPngButton) no-repeat scroll right 0;
                            height: 22px; padding: 0 3px 0 0; float: left; margin-bottom: 10px;
                        }
                        .btn_2 {
                            background: url($sScriptName?fnc=getPngButton) no-repeat;
                            height: 22px; color: white; font-weight: bold; line-height: 1;
                            display: block; padding: 4px 5px 0px; text-decoration: none;
                            font-family: Verdana; font-size: 12px;
                        }
                        #logo {position: absolute; top: 10px; right: 30px;}
                        .box_warning {
                            text-align: center; background-color: DarkRed; border: 1px solid black;
                            color: white; font-weight: normal; padding: 1px;
                        }
                        .box_ok {
                            text-align: center; background-color: DarkGreen; border: 1px solid black;
                            color: white; font-weight: normal; padding: 1px;
                        }
                        .box_warning a, .box_ok a {font-weight: bold; color: white;}
                        .squ_bullet {
                            float: left;
                            height: 10px;
                            width: 5px;
                            border: 1px solid black;
                            margin: 0 5px 0 50px;
                            display: inline-block;
                            font-size: 11px;
                            color: white;
                            padding: 0 3px;
                            line-height: 10px;
                            cursor: pointer;
                        }
                        .squ_toggle {
                            font-size: 15px; line-height: 0.5; cursor: pointer; float: left;
                            height: 10px; width: 9px; padding-left: 1px; border: 1px solid black;
                            margin: 0 5px 0 3px;  display: inline-block;
                        }
                        .squ_desc {
                            position: relative;
                            font-size: 11px;
                            line-height: 10px;
                            cursor: help;
                            height: 10px;
                            width: 5px;
                            padding: 0 3px;
                            border: 1px solid black;
                            margin: 0 5px 0 3px;
                            display: inline-block;
                        }
                        .squ_desc div {
                            font-size: 13px;
                            background-color: white;
                            border: 1px solid black;
                            box-shadow: 4px 3px 7px #c9c9c9;
                            display: none;
                            left: 0;
                            padding: 20px;
                            position: absolute;
                            top: -25px;
                            width: 400px;
                            z-index: 2500;
                        }
                        .squ_desc li {
                            line-height: normal;
                        }
                        .squ_desc:hover div,
                        .squ_desc div:hover {
                            display: block;
                            margin-left: 30px;
                        }
                        .squ_desc div.hoverhelper {
                            background: transparent none repeat scroll 0 0;
                            border: medium none;
                            box-shadow: none;
                            margin-left: 0;
                            width: 0;
                            padding: 0 0 150px 30px;
                        }
                        .squ_desc:hover div div {
                            display: inline-block;
                            position: unset;
                            border: none;
                            box-shadow: none;
                            padding: 0;
                            margin: 5px 0;
                            line-height: normal;
                        }
                        .squ_desc:hover div div.squ_bullet {
                            border: 1px solid black; display: inline-block; padding: 0; position: unset; width: 10px;
                            margin: 0 5px; box-shadow: none;
                        }
                        .desc_box {
                            width: 400px;
                            position: absolute;
                            left: 400px;
                        }
                        .note {
                            color: gray;
                            font-size: 10px;
                        }
                        .messages {
                            display: block; 
                            margin: 13px 0; 
                            text-align: center; 
                            background-color: orange; 
                            border: 1px solid black; 
                            color: black; 
                            font-weight: normal; 
                            padding: 1px;
                        }
                        -->
                    </style>
                </head>
                <body>
                    <a id="logo" href="http://www.oxidmodule.com/">
                        <img src="$sScriptName?fnc=getPngLogo">
                    </a>
                    <div class="langswitch">
                        <a href="$sScriptName?lang=de">
                            <img src="$sScriptName?fnc=getGifDe">
                        </a>
                        <a href="$sScriptName?lang=en">
                            <img src="$sScriptName?fnc=getGifEn">
                        </a>
                    </div>
EOT;
        echo "<h3>" . $this->translate('RequCheck') . ' "' . $this->oConfig->sModName . ' ' . $sModVersion . '"</h3>';
        echo '<p>' . $this->translate('ExecNotice') . '</p>' . PHP_EOL;

        return;
    }

    public function getHTMLFooter()
    {
        $sScriptName        = $_SERVER['SCRIPT_NAME'];
        $sTranslShopPhpInfo = $this->translate('showPhpInfo');
        $sTranslDependent   = $this->translate('dependentoffurther');
        
        if (count($this->oBase->getMessages())) {
            echo '<span class="messages"><ul>';
            foreach ($this->oBase->getMessages() as $sMessage) {
                echo '<li>'.$sMessage.'</li>';
            }
            echo '</ul></span>';
        }

        if ($this->oBase->blGlobalResult) {
            echo '<p class="box_ok"><b>' . $this->translate('globalSuccess') . '</b>' .
                $this->translate('deleteFile1') . $sScriptName . $this->translate('deleteFile2') . '</p>';
        } else {
            echo '<p class="box_warning"><b>' . $this->translate('globalNotSuccess') . '</b>' .
                $this->translate('deleteFile1') . $sScriptName . $this->translate('deleteFile2') . '</p>';
        }

        echo <<< EOT
            <sub>$sTranslDependent</sub><br>
            <p>
                <span class="btn_1">
                    <a href="#" class="btn_2"
                        onClick="document.getElementById('phpinfo').style.display =
                        document.getElementById('phpinfo').style.display == 'none' ? 'block' : 'none';">
                        $sTranslShopPhpInfo
                    </a>
                </span>
            </p>
            <iframe id="phpinfo" src="$sScriptName?fnc=showinfo" style="display:none; width: 100%; height: 700px;">
            </iframe>
              </body>
              </html>
EOT;

        return;
    }

    /**
     * @param $aResult
     *
     * @return bool
     */
    protected function hasRemoteVersionDiff($aResult)
    {
        $blDiff = false;

        if (is_array($aResult)
            && isset($aResult[$this->oBase->sVersionTag])
            && is_array($aResult[$this->oBase->sVersionTag])
        ) {
            foreach ($aResult[$this->oBase->sVersionTag] as $sRemoteVersion) {
                if (version_compare($sRemoteVersion, $this->oBase->getVersion(), '!=')) {
                    $blDiff = true;
                }
            }
        }

        return $blDiff;
    }

    /**
     * @param $aResult
     * @param $sElementId
     * @param $sCheckType
     * @param $aConfiguration
     */
    public function getNoSuccessItem($aResult, $sElementId, $sCheckType, $aConfiguration)
    {
        $sText = '';
        $sDesc = '';
        if ($this->hasRemoteVersionDiff($aResult)) {
            $sText = '!';
            $sDesc = strip_tags($this->translate('RemoteVersionDiff'));
        }

        echo '<div class="squ_bullet" style="background-color: red;" title="' .
            $this->translate('RequNotSucc') . $sDesc . '">'.$sText.'</div>' .
            $this->_addToggleScript($aResult, $sElementId) .
            $this->translate($sCheckType, $aConfiguration) .
            $this->_addDescBox($sCheckType.'_DESC', $aConfiguration) .
            '<br>' . PHP_EOL;

        $this->getSubDirItems($aResult, $sElementId);
    }

    /**
     * @param $aResult
     * @param $sElementId
     * @param $sCheckType
     * @param $aConfiguration
     */
    public function getSuccessItem($aResult, $sElementId, $sCheckType, $aConfiguration)
    {
        $sText = '';
        $sDesc = '';
        if ($this->hasRemoteVersionDiff($aResult)) {
            $sText = '!';
            $sDesc = strip_tags($this->translate('RemoteVersionDiff'));
        }

        echo '<div class="squ_bullet" style="background-color: green;" title="' .
            $this->translate('RequSucc') . $sDesc . '">'.$sText.'</div>' .
            $this->_addToggleScript($aResult, $sElementId) .
            $this->translate($sCheckType, $aConfiguration) .
            $this->_addDescBox($sCheckType.'_DESC', $aConfiguration) .
            '<br>' . PHP_EOL;

        $this->getSubDirItems($aResult, $sElementId);
    }

    /**
     * @param $aResult
     * @param $sElementId
     * @param $sCheckType
     * @param $aConfiguration
     */
    public function getUnknownItem($aResult, $sElementId, $sCheckType, $aConfiguration)
    {
        $sText = '';
        $sDesc = '';
        if ($this->hasRemoteVersionDiff($aResult)) {
            $sText = '!';
            $sDesc = strip_tags($this->translate('RemoteVersionDiff'));
        }

        echo '<div class="squ_bullet" style="background-color: orange;" title="' .
            $this->translate('RequUnknown') . $sDesc . '">'.$sText.'</div>' .
            $this->_addToggleScript($aResult, $sElementId) .
            $this->translate($sCheckType, $aConfiguration) .
            $this->_addDescBox($sCheckType.'_DESC', $aConfiguration) .
            '<br>' . PHP_EOL;

        $this->getSubDirItems($aResult, $sElementId);
    }

    /**
     * @param $sCheckType
     * @param $aConfiguration
     */
    public function getUncheckableItem($sCheckType, $aConfiguration)
    {
        echo '<div class="squ_bullet" style="background-color: orange;" title="' .
            $this->translate('RequNotCheckable') . '"></div>' .
            $this->translate($sCheckType, $aConfiguration) . ' (' . $this->translate('RequNotCheckable') . ')' .
            $this->_addDescBox($sCheckType.'_DESC', $aConfiguration) .
            '<br>' . PHP_EOL;
    }

    /**
     * @param $aResult
     * @param $sElementId
     */
    public function getSubDirItems($aResult, $sElementId)
    {
        if (is_array($aResult) && count($aResult)) {
            echo '<div style="margin-left: 20px; display: none;" id="' . $sElementId . '">';
            foreach ($aResult as $sPath => $blResult) {
                if ($sPath != $this->oBase->sVersionTag) {
                    $sText = '';
                    $sDesc = '';
                    if (is_array($aResult[$this->oBase->sVersionTag]) && isset($aResult[$this->oBase->sVersionTag][$sPath])) {
                        $blDiff = version_compare($aResult[$this->oBase->sVersionTag][$sPath], $this->oBase->getVersion(), '!=');
                        $sText = $blDiff ? '!' : '';
                        $sDesc = $blDiff ? $this->translate('RemoteVersionDiff') : '';
                    }

                    if (false === $blResult) {
                        echo '<div class="squ_bullet" style="background-color: red;" title="' .
                            $this->translate('RequNotSucc') . strip_tags($sDesc) . '">'.
                            $sText.'</div>' . $sPath . $sDesc . '<br>';
                    } elseif (null === $blResult) {
                        echo '<div class="squ_bullet" style="background-color: orange;" title="' .
                            $this->translate('RequUnknown') . strip_tags($sDesc) . '">'.
                            $sText.'</div>' . $sPath . $sDesc . '<br>';
                    } else {
                        echo '<div class="squ_bullet" style="background-color: green;" title="' .
                            $this->translate('RequSucc') . strip_tags($sDesc) . '">'.
                            $sText.'</div>' . $sPath . $sDesc . '<br>';
                    }
                }
            }
            echo '</div>' . PHP_EOL;
        }
    }

    /**
     * @param $aResult
     * @param $sElementId
     *
     * @return string
     */
    protected function _addToggleScript($aResult, $sElementId)
    {
        if (is_array($aResult) && count($aResult)) {
            $sScript = "<div class='squ_toggle' title='" .
                $this->translate('toggleswitch') .
                "' onClick='document.getElementById(\"" . $sElementId . "\").style.display =
                document.getElementById(\"" . $sElementId . "\").style.display == \"none\" ?
                \"block\" : \"none\"; this.innerHTML =
                document.getElementById(\"" . $sElementId . "\").style.display == \"none\" ?
                \"+\" : \"&minus;\";'>+</div>";
        } else {
            $sScript = "";
        }

        return $sScript;
    }

    /**
     * @param $sTextIdent
     * @param $aConfiguration
     *
     * @return string
     */
    protected function _addDescBox($sTextIdent, $aConfiguration)
    {
        $sContent = "<div class='squ_desc'>?".
                "<div class='hoverhelper'></div>".
                "<div>".$this->translate($sTextIdent, $aConfiguration)."</div>".
            "</div>";

        return $sContent;
    }

    /**
     * @param       $sIdent
     * @param array $aConfiguration
     *
     * @return mixed|string
     */
    public function translate($sIdent, $aConfiguration = array())
    {
        $sGenIdent = preg_replace("@(\_[0-9]$)@", "", $sIdent);
        $oTranslations = new requTranslations();
        $aTransl   = $oTranslations->getTranslations();

        if (isset($aConfiguration['aParams']) && is_array($aConfiguration['aParams'])) {
            array_walk($aConfiguration['aParams'], array($this->oBase, 'aTos'), $sIdent);
        }

        if (isset($aTransl[$this->oBase->getLang()][$sGenIdent])
            && ($sTranslation = $aTransl[$this->oBase->getLang()][$sGenIdent])
        ) {
            if (isset($aConfiguration['aParams'])) {
                return vsprintf($sTranslation, $aConfiguration['aParams']);
            } else {
                return $sTranslation;
            }
        } else {
            return $sGenIdent;
        }
    }

    public function getPngButton()
    {
        $sImg = "iVBORw0KGgoAAAANSUhEUgAABDgAAAAWCAYAAAAl+SzaAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAB".
            "MpJREFUeNrs3Y1O4zgUhuFY4hbb2ZthRjtczOz0Ght7cZwfQ5u2E4K0a55XiNDUXyWcT+ZwfGyHw+HQvZI6AACAT+J0OgW9AAAAtn".
            "A8Hh/JWYSnbkxuvAYeeg0AAAAAAPynuJevOB6P+ZKe6sYvLy96DgAA7M7z87NOAAAAm7iVq8gxRs5p5CTH03Tz758/uzAUc7x+Hy4".
            "pf71ex9fDj2leyxLG1vnNELpmdJPqo21a7afy+/MIj/AIj7zVhS/seWPD4zoAAIAtxJhW44+cy/jx/ftw/2kRxDEQSd0Uraah/RKV".
            "lLfK+/kDS0T7eieGZnTdA33QfeF+CpFHeIRHeORSF1Lw3I0Nd3UAAACbEhwprscfadnma05wpL7v8v0Sh4QiLimREqWEt7mSmK9xn".
            "LlrSBe6fdq02k9D1oxHeIRHeORCFz13Y8NtHQAAwNYER+zX44+q3Zzg6GOcbw6haqhmXG5MvuQPiw3q9mrTaj/xCI/wCI9c13juxo".
            "Y/0wEAANxNcPTxbvzxLsHRd7mEo8y+pJIFCWEupy2XMTcSxjKQUMqSl1mb/79urzbN9hOP8AiP8MgV3Zf2vLHhIR0AAMBWcr5iNf6".
            "o4owlwdGPCY68hiUsZbRh2DGsWkz7/mUaVl83oxu3R/xwm1b7KfEIj/AIj1zRDfc9d2PDTR0AAMA2hgqOtfijWqOybDKaExzj6pVp".
            "zWyYG04zdGn5vByohVC924ou7NSm3X7iER7hER55r/P3w9jw6NgAAADwp+SCjPX442oFR5URWeaY5pKPsmNpmI+SnctN5zKRVnR7t".
            "Wm1nwKP8AiP8MiKznM3NqzrAAAANic4zuf1+ONaBce576dQZAhMplPepvWzYdn6vSoBCUNJSCkPaUS3V5tm+4lHeIRHeORS97U9b2".
            "x4RAcAALA5wZEPRVmJP1K4ckxsPJ/H9SzjOvpuEc11INP805gtWQ6Ka0gXdmrTaD8NGTMe4REe4ZFrOs/d2HBLBwAAsJHzuV+PP6q".
            "JlKqCI3ZdvaZliVGm3MiYKZm3EJuvXera0aW0T5tG+2kKYHmER3iER2pdU8/Pc/+0sQEAAGALec/Q9fjjSgVH358v/zFZJNXy6ukY".
            "uFQqREZBK7q0U5tm+4lHeIRHeOSqLnnuxoa7YwMAAMAWzvF8M/64THDEOB+xEsYIJlV7d5R1tdNGHsMnlvW2I63opirrj7Zptp86H".
            "uERHuGRS92X9ryx4cGxAQAAYBv5mNi1+OP6HhzDMbEVad5JrKoxrdbfzlFa155urzYt9lPgER7hER658bt47saGVR0AAMA28ikqj8".
            "QfVQVH3705ceU1KEm5qmM+0y7N8crwOqY5a5Ja0sWd2jTaTykmHuERHuGRS52/H8aGuzoAAIBtxCGIWok/riU4Yl8EZVOwEpSUG9X".
            "62XmRS1w+oV5z24RurzaN9tO0QR6P8AiP8MgbnedubLitAwAA2EqfExo34o+LBMevX7+6b9/+KkFItYZlmI0tP1XBS3UE3LhNeju6".
            "vdq02k8dj/AIj/DIhW48W8NzNzbcHBsAAAC2MGypsRJ//P7n9/J/yOFwGO6fTie9BgAAPgvrVAAAwFZuzpgcj8fh+jQGHGm6AQAAs".
            "DcmUgAAwFYezFeEfwUYAAoCUXB0RZrTAAAAAElFTkSuQmCC";
        header("Content-type: image/png");
        echo base64_decode($sImg);
        exit;
    }

    public function getPngLogo()
    {
        $sImg = "iVBORw0KGgoAAAANSUhEUgAAADMAAAA0CAYAAAAnpACSAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAE".
            "IxJREFUeNq8Wgl4VNXZfu+dLZkkk5BA9kACYQlB2aIga6myuIEtFX+kLW1BJVT/akVrRds+rVqRX2lLRSsal5/nUaCgtmhi8BeaUh".
            "Al7EYTIWyGQPZlMsnM3Lnn/865dzJ3biaLVnsfDpk59yzf++3fOSMxxvANPlZqUdQs1FRqXmq+Ac7NpbaI2jxqQ6nZqDVR+z9qr1H".
            "71DxB+nfBPHYYSHUCK8fATl+HUZtK7Wpqo1SGeZ0BQCEYFolQETSrhDJ6d4rax9Q+pFa18SQ8HX6aHAcszUUS9T3U0IU1710ASqid".
            "dwNuBciMARbSDjcQtDQnnnj7HNYuGvY1gqHnW9RWBBi+f7kT+LwVKG8AjlDj38+0AR1EiJ1kk0XEZFAbO4gQJwOj44F0+m6TsYvWK".
            "KKWQOQUvVwFPHCAxNBlZDs1psk30wXsv4XWi8VvqefXXwcYWg6FRPy8racBzsXjjQxtXim4sra5bKCG6X3QCLOR4lxBwGakS1g+Ch".
            "hORN5FcttWpSumpCunZADEH5L2iATa71bAaUUW9XzxVcEs4yCq2zD9qaMML1QQXQGdaMmwYW8PM41RQxwvmgu0+yU8Qap7uUMbN59".
            "UykUKXF4P0J5hgD4gi5qTjuW6DQkDHehDfMNvLnbgvp/vV7GdpKEGdA5aTMRiAIAQDj6HJHR7rgyHBc+T/a16jaQzNFbYB0FDXa0H".
            "C0a+QSrrD82J1qj3G73NQJ6buTT+eppdf+cehuauCCCCLDeDkcwAWM8xjA+T8JcKxp3FKu4oFGLUOZJEpyJNIqKfpP4F/kBI9bLIW".
            "UxIErPLvozNFLb5sOmRgwwbj6kaMbIUrjJMJzIIRooAUOpNzwzdAcMX+hfvlFC6UEaCQ8K0N4FGrzbVQuM+InuZNBjruOcLzpL7Ab".
            "LSr2LT0lIVG8tpBZnpxDOtwfgX4X2snz5z4y8swaYRfWM2+fhkaS/3gotyqE/RVGnrAgHkz6daQ0D6A8Mlsvm24gDerSYgNhaZiP4".
            "avvq4giFCgkdJzZa+MAv442zCQxby9hmgK4C7c+MxZCBgZtJam24tVvBmlS4RhoERaH6nRhpvkg4FKfipKXrj32nPjy+p+NsZ9d4W".
            "L16noPvTu/OBuSSt/z1BaUCNoPN2c7phfhJoqbLfk1qVconwEUw3DEm3L1Xq3W0x9Ix0TDLnHTpwzZ5W5MsYP1gCqTSsxN5jFK+KT".
            "gbweiW1ChXzhksoXmh7lkx11Vvz8fxsCsaXOsVKI/sD84t3z6pYu5fkaZdCXJUkLSbQd56aWCQz5ZLJ57Juwn3csFV9jaDDCeoEfX".
            "+owIbceGmT3qseqmOri44oWjam8vmWoMt4iYJk/Pb5WPdpi/h+vC8wszwKHrpzt08zRE4Ql4LEwqL39httGJckD8in8yleUhs7sfV".
            "sG8MFN0PJ2QB2nAp0A/RrXmzrU+VK2YOTrWlXJEmr7y2w4C0ac90wGQ8UWPmwe1+pCCg/GmvZnO7EOovmPZ19gSn8w2E/LraomlRU".
            "g6fSmc0lMi1NRopTmjCAeB+UFN80YfQgiUeGoSvzLXMrm9nsxw76sOWYQvmbirxEy3i3j5Vtr1Jqbx1lXbBhtq3wd9Nsi2JteIfmP".
            "Per/b53HCKuWZafa2dYuduLD74XtYHo2UKdDeY4c2t1K9s2qsgj7E8I3Kw11AZFEYdXRlOKIZEp4tzsHT4Rdny6vrtINdq8DA76nE".
            "BjaygtSaIi4Hu5FhSfU9FFBs6Xeusm+xKyka1PH/LjUJ2KN25w8PlLlhd3bR8WL2Goy4qV4yxxtxd72/laL893pNK0R4/Vq6t/VOL".
            "F0Ysqdi6OwndyLb+i/t+ZwXxw127vnBdIMkJXI5kAETwxRcZHy5x8Y/L+8NyyywdKNZBNEfm2kTJON6nYVulHTryMKekyTjWrpGYS".
            "8pOtKK9jcJPX4uBHJkhYP8M2nta4Z8Nh/4r9NQG8OM+BeIf0Z+qjZAluPceglBJ5LV62nIeIHxZ3IaDncwUZRMtSJ1dBXn74g2AyP".
            "H72Re5LHtS2q707bNLtFRNstGnUe/RtAe96eJ8PWS4Z01IlJJIEslwWzsEMPWequ39P16Wq5gAmJVuQ6LTgIAFqpkienyhhIsWRZW".
            "Oskzv87FD28x1IiqHsOd+GkYNkwYxOkmJFYwDnyda2fKrgXLMalkJxs65cGUMZtHw9fS0J2sxtB2oDqG0OaFLpLcOhtaani9WOBrs".
            "mEEH5SZoRE5ApRNiHfHOeBA6mdOTpOVH/omGbHz/Q9Wqak+HaDF5sSeRAJMTSmLdPKeWLcq3/s+E6x5of7PTg4fqApgnBPCyo7rJJ".
            "9fkQkvLm4z48OSuqkIORdYBPFx33adT2E6XzNS92LLhgLXmnd6sV7D6naA6k3IsrX2xHXlE7xtDfJX/zTP/7aeWVtddEbeK2aJd5c".
            "cZQ5w5QUAzgRL1wZe9clWKBhTNS1uOZVU9tJC2ARgzUtNZrn/hBWrWQawOnLLexk2FnpU+P9KrWVL0xHSB9jqINsuPlcP9O72Ta6I".
            "GrHXz28rLzCmW8ZOjkEWrIS23/xIuFW9txsFYpXJpnX9alcAdBCkBuKI8YMzpRrFeV5ZIwhNRMeB/VkD2YswlVDX0moLXNCg5fFgy".
            "5lq805RSpV1eXakozQh5MW4QhJ0HmBsonng9iibNrHo6e5E4S+4l6xRDhma4aDH85Is4xttwx3i4pKhMSaupUcdktInGHhazYaWEh".
            "5jHdylUWYm7QWQXfiX6GDy8KrZjFt5q274ISmsQMC+iDxV/i2NQ0UTy9T3Pag2AoNqDdKzbJqyHCLrUEQioRlC6BO3rJL9IVeuzbK".
            "rzYd8GHzFgJmXGCEVHcEfkUfT+oBimo4RIS/dDX1hi274Koz/K5Axj+aYMSLo1IyRZNLEgT/uKk8e2JOiVY3ow7SfrPWITii/ClkA".
            "pZtXHKt4dZyVMBu075hI3fMsqeQK6X1C8oDUOKFFbMRfBMNKShQ0xwceoyq5uVUKTv45mcIsB8ZOzjbjR4znW+lajmQUAyJQYEMI3".
            "AUHfxmvfdAU5ffLQMa7SkxQyiodFDYDyqyWP1TxN/39wpbIY7R8R+wYmQ+phIxEhESZJTEHnW+CrZKWvSJuY3dhhUwpjpUN+0DMGI".
            "E7F2SbzxikyABaU66bNGJZwRPQrSCBWqTm9rl+CIg+9gc3sD4VxgekWJUJYbbZMQL7JoLQ8KPpfd3bXu0MpGv67v4SUCp2/BCB6ks".
            "WtyqlW84XbmJ5A6eXNLT3t1G5HCj6UkYwkSQdXoq0870pA5GCWaK7MaiFCHsO4Jg0klXJonazKudY4MftONLhflXNccqfUb0iCdEi".
            "L427kOpMeJYHuwneyCM2bEIC2UT820pdPcubsqO00luYEh3bWP2rPaoC82jSMqB+PmXuVMAzOkMSaVI0/GOWrXLLjZCGZ6lk2YTks".
            "Xw1kuGWbkHmcbw9oZMVydVq/bx30f6bWdZwxM0EhgZleQO7/YpIiz25DxM5PNs8jaRovEOwThPv5/3XDOpUAf0Z+4Oz5VFEgvw7Cd".
            "iHYNQsbjqgiI32+I1Dz4UeBcPT0Gs7MdfMyr1w53YA595mVEdVNASJWeG3dUdA7gnEANxa4wV60iMVqg6+CSqbwy2TpLGxDpiEjrv".
            "zpD6Pwhs29QNOv/1t5q0nmeoAU0I3GRY1g3LwF3XhXLpbL4klv1pMVS8kiAp2TYxHYFGfZC8oDLNvyjLfycofusTYrgBGAoxTU3nq".
            "w5plYO5vDkdLsehCTzyZwYzA147BBbjxgjDpzH8BsLfD5miBX/PTMOIxKtGE2fx6fakRpneYberW/wqJeaPAGUVXfiDIWBRfkxmDb".
            "UMZyEt+mON5vQyYshrviqGgIhzEENnTWHqZehwCKveGWKoO0MB1PGCXGS3/fwRU14eEuLt5BbFnrZ404kWTPs55aMc4LaOPrcoo8r".
            "XfxGY+WDM1y42OrDsYteECjUk/smIHzfNa8dcaP0kw5DVduLvZj/Gg2aNGdOjlDjUr7oZ8mxFszKtqOkwoNgmDZG7/GpNsRoLqPRD".
            "GZLeTsWXxnbvPGA+4nPyYhvGBklJMklQCUvPr7QiaM1XRgcQw6EjGXr7ckjaNr9JVWdhT/ZWq/t91VvImhabJSM8WnCBMqs+sHR2n".
            "uuiXu85AQVdxZTZUa6MGuYgxP4qtn4+fPI2/XYdqwdflKNFkocm1u9WDIhFh2Ur2TGyGij6Gwho+FG/8xNSYXkhje9Wu7Gqh31+jF".
            "vX1Ge9X3MQPZ3x4w4Ks/lYl6dBouz12dmRz3u4pt7TekIcW1iukB+JOKC5BaPX/B2B7RaovGmPCc2Lx7CjYnfnUzmHpxaweEa79Sf".
            "72rEP6o6Q0cprD+6+5Aa0baiIE4cQRlPZ87EOeR/fndczMxXPmwVV1lBjsmkBukihcN8vYWv91RupN1jKY7MaqE0o5pc9p7TnaRuX".
            "uw82aHZRlCVVaPn6hFA+pYKacyEoVEYM0QwusR81PTcfTPjZ76yv8WwicaV1TvqqG6hOtvSvxZwT+4iPa5u8uOzOj/aOgIhB8TVSt".
            "bT9+50KZzT3QeO/YmMnFXhVBe3ij/xGGM+neGlkbK2uBG/L2nQ6lvzxVAk8RuPXoMMUAz1u3lymJs1EGrsY4aBkhR+tyOCG9VWOdH".
            "YuzqLskspjzsx88F5gKZd//C1gxDH3XBADVV0YOFltKqGru/CxhjuMSVT9A5O6C7F1fCC0Fh4ITzCh0V+vRX9VyoH8mAQSKRbgJJY".
            "u/yHjd9NoRw9SDALJ5gZozALVw9jqmGu9LqBm3I/4x1ON1NgcJyGdflDdK2aOQh5yfb3j9d61/d3pfHsD69y4Z7rEvkhsYGDhvMAY".
            "3ltrtG736H3iyUjk4xSCkoNxvMIA1hfAFdkReGZRcnCxr1KeKSIBOYUqdt31t+cjGtyozUJhXE/Aje7uWzipvlkxaiW5kOTsLXR82".
            "SGCOfZxnuFWbyEeKS6wbeTHyoO5LLpLdLHNcWFw5Cf6dAlFEG/zX2RiOhxCYWBXVIhHAgv6fb8LBtpLutTlXW+x/nhiBLAgMDw5+n".
            "4KPnRsp/lYPrIGHHvFvn2DF/t2m+gjVxwOuWGx9fmYmyK49mqOt8veiO4v0uWx0iU979LElo+fZAmIfVrJraPGorvN2loNPbdNxx5".
            "KY4n/3nac3dfxA7kxugZCoJLX1qWgUdvTtESTkWNcIJi0vkw2zGU0oz19GbmrEXRwPxgWiL23puDnCT7w6WfuX/Z7y3Ql/i5Cc+vC".
            "mta/Mt+vOUCdp9s1wKaBaHAJvXyK4w+k0jDxIBWoU7KceLF72diYmb0Xu61XtjftC070U6GLyMlzhqGe3Sy/d/6VdMqX4A9V/xJO/".
            "60pwF7PneD+fXfYMkSvvTDdBA0dSKp1E9IGsunJCIuSv7liwean+QXWLQfvikw4oiZ2l2kCetP13vx+qEWvHygUTvQ0AnrBiYhdDF".
            "rVCk9/0uItWJpQYIAcUV6NI/qfxTS+FdTJT+rs1m+eTDBx6ar353tXnXpR2c94O3QeQ9qWv3ooBjVTIkmJ8ZG4FxUzbqiLUgmABMy".
            "ojBleAymZDsxJNZayu9wqO3+bfHl1iQq5PgtwX8ajPFJ039IN4faWP36Llb/WaOs5yc+PcNt1a/6+I94PuBnCF8HAf8vwADS7GaT0".
            "D4fMwAAAABJRU5ErkJggg==";
        header("Content-type: image/png");
        echo base64_decode($sImg);
        exit;
    }

    public function getGifBg()
    {
        $sImg = "R0lGODlhCgAyANUAANHo+pfK85rM8/X6/vb6/v///5jL85bJ8+Hv/KbS9dzt+87m+qTR9fH4/er1/b7e+MTh+P3+/63V9u/3/".
            "dfq+rnc97fa96DP9Nns+53N9LLY9tTp+sHg+Mzl+cfi+OPx/Pv9/7DX9p/O9Oz2/bTZ9uXy/KLQ9Pj7/ujz/bzd9/7+//r8//P5/s".
            "nj+ZvM897u+6nT9avU9qvU9QAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAAAAAAALAAAAAAKADIAAAb".
            "FwINwSAwYj0iDcskUOJ9Ql3RKzVivWJF2y714v2CTeExmmM/ohHrNhrnf8Jh8PpdJ7vh8aM/va/6AgSSDhIUWh4iJFYuMjSmPkJEP".
            "k5SVHJeYmRCbnJ0en6ChLaOkpR2nqKkLq6ytAK+wsRuztLUUt7i5GLu8vQq/wMEvw8TFCMfIyR/LzM0lz9DRKNPU1Q7X2Nkj29zdE".
            "9/g4Q3j5OUs5+jpA+vs7QTv8PEn8/T1K/f4+SD7/P0R/wADqhhIsGCBgwgTBgEAOw==";
        header("Content-type: image/Gif");
        echo base64_decode($sImg);
        exit;
    }

    public function getGifDe()
    {
        $sImg = "R0lGODlhEgANAIQZAAAAABAFBhEGBhIGBhQHBxUHCCYNDZQqH5QrI9c4M+M4M9w9M+g/MuNDM/BFM99tI+t3H+CyDerIB+zIB".
            "uzKBurLCPfcAPfgAPjlAP///////////////////////////ywAAAAAEgANAAAFVaARCGRpmoExAGzrvsBAwHRLFHVdIEfv/8ADou".
            "EoGo9IR2PBaDqfUMYioahar1hF4gHper9gyKOCKZvPaExFcmm73/CLZGKp2+94yyRCmfj/gIAUESEAOw==";
        header("Content-type: image/Gif");
        echo base64_decode($sImg);
        exit;
    }

    public function getGifEn()
    {
        $sImg = "R0lGODlhEgANAOfRANzd6P9LQP7//93e6ba32v8HB/J4ef//+/85Of8fFVddwP8aFq+13P8aFPr////f3f8XE/n//62s3fQuL".
            "AIDj6ys3uHZ5P8uLOjp793f6dbX6uvBxsyasurCx/9fXcadtS88r+Da5EZHr+Hi7A0NlUVGqcjR9MKaunh5x/8REQAAkv9IP/9BPn".
            "h6wi4/td3c5uLl7P8PD7vO9aGSw7bM9uDh6UpLsf8hFv/f3PPx9/Dx9DFCuMDE4cHF4/sAANPU3ufp8JSDvuVocf8ODvz8+/xRTPQ".
            "gG+PM0ZSWzs/R476+4ujp8v8/PurO0uPZ3//u5fQCAOPj6nFxxf8UE8rM4P/w5YGM18PH4/79/ExUuP3//4CAxqmo3KaZxv7+/Rcs".
            "tO3v89XW6fS8waOj2snM7Nra7Ccon+no9v03OFJZvuK2xBEipP89Ov8dE+be4u3u8/w3OOVocv8sKv8EBOjo9+/u+Kap15SFvgwRl".
            "ba327uXteHh7tvc5yo3q9XX5SQ4uU5MrtjW5qaVxvDS2f8DA+Tj6vr6/j1FtVlgvL+euvHw9v+rqe7u+XKJ1ebn7p2x7CUmnvb2+d".
            "PW8P8cEc/P4efn8/38/5Ws66mYx/ccGNfY5vh0d927zSUlov96ev88OgAAjmmA09rb5v+xsPF5eMnR8i0upuuAgvEyLx0rq97f6cu".
            "nwEBIuO/Aw/9/fuTm6vn5+vTEyM7P5rq63BESlf+Fgv8fF8SWsOfp7+2rrvX1+La23RgmqLe43PPV2vdydhcnqIWQ2BEgoube4wAS".
            "n82atOHj6uTT2f97etKjuf9dWsSduZyb08fJ4fn5+/z8/f+ZAP///////////////////////////////////////////////////".
            "/////////////////////////////////////////////////////////////////////////////////////////////////////".
            "///////////////////////////////////ywAAAAAEgANAAAI/gB/YHCkig+AAVGGuSmUAYCoEbpgGXsFY8kWM7T6JEI14QMIEpB".
            "aVNIBRBOSOtEqmAK27EabYqk6jYl2hpksKnjSmIgmqdEqWw2ShSITrZQCFXb8IDpkRdGBCLGmpBDj4ECOYKyyXHFyyoCQX8hceShQ".
            "QFktT5viGCDV5AgUaHDjypXrI9exIgHYwEHA5MIcQnJYIEDzKcCKVi+63BE0CMeTUTGGLKrywBemIDMCJVnz5ZIWATI4LYCghoaAa".
            "I/+EGNAqQQXQ4xQuDiRYBKHHVLoWJIAaFaNZkSU2KAgTI+RTLd4gRKxC0uZPQPAvAnTiwCPEB02WOiRh4CGZ15wAgUEADs=";
        header("Content-type: image/Gif");
        echo base64_decode($sImg);
        exit;
    }
}

/**
 * Class requTranslations
 */
class requTranslations
{
    /**
     * @return array
     */
    public function getTranslations()
    {
        return array(
            'de' => array(
                'RequCheck'              => 'Mindestanforderungspr&uuml;fung',
                'ExecNotice'             => 'F&uuml;hren Sie diese Pr&uuml;fung immer aus dem Stammverzeichnis '.
                    'Ihres Shops aus. Nur dann k&ouml;nnen die Pr&uuml;fungen erfolgreich durchgef&uuml;hrt werden.',
                'RequSucc'               => 'Bedingung erf&uuml;llt',
                'RequNotSucc'            => 'Bedingung nicht erf&uuml;llt',
                'RequUnknown'            => 'Bedingung unklar, siehe Hinweise im Hilfetext',
                'RequNotCheckable'       => 'Bedingung nicht pr&uuml;fbar',
                'hasMinPhpVersion'       => 'mindestens PHP Version %1$s',
                'hasMinPhpVersion_DESC'  => '<div>Das Modul erfordert eine PHP-Version die nicht kleiner ist '.
                    'als %1$s.</div>'.
                    '<div><div class="squ_bullet" style="background-color: green;"></div> Die passende PHP-Version '.
                    'ist auf Ihrem Server aktiv.</div>'.
                    '<div><div class="squ_bullet" style="background-color: red;"></div> Das Modul kann in '.
                    'PHP-Versionen kleiner als %1$s nicht ausgef&uuml;hrt werden. Fragen Sie Ihren Serverprovider '.
                    'nach der Anpassung der PHP-Installation oder kontaktieren Sie uns f&uuml;r eine alternative '.
                    'Modulversion.</div>'.
                    '<div>&Uuml;ber den [+]-Button k&ouml;nnen Sie Ergebnisse zu den getesteten Verzeichnissen '.
                    'abrufen. Je nach Servereinstellung k&ouml;nnen die Ergebnisse abweichen. Nur die rot markierten '.
                    'Verzeichnisse erfordern eine Anpassung.</div>'.
                    '<div>Details zu Ihrer Serverinstallation sehen Sie durch Klick auf den Button "PHPInfo anzeigen". '.
                    'Bei Fragen kontaktieren Sie uns bitte &uuml;ber <a href="mailto:support@shopmodule.com">'.
                    'support@shopmodule.com</a>.</div>',
                'hasMaxPhpVersion'       => 'maximal PHP Version %1$s',
                'hasMaxPhpVersion_DESC'  => '<div>Das Modul erfordert eine PHP-Version die nicht h&ouml;her ist '.
                    'als %1$s.</div>'.
                    '<div><div class="squ_bullet" style="background-color: green;"></div> Die passende PHP-Version '.
                    'ist auf Ihrem Server aktiv.</div>'.
                    '<div><div class="squ_bullet" style="background-color: red;"></div> Das Modul kann in '.
                    'PHP-Versionen h&ouml;her als %1$s nicht ausgef&uuml;hrt werden. Fragen Sie Ihren Serverprovider '.
                    'nach der Anpassung der PHP-Installation oder kontaktieren Sie uns f&uuml;r eine alternative '.
                    'Modulversion.</div>'.
                    '<div>&Uuml;ber den [+]-Button k&ouml;nnen Sie Ergebnisse zu den getesteten Verzeichnissen '.
                    'abrufen. Je nach Servereinstellung k&ouml;nnen die Ergebnisse abweichen. Nur die rot markierten '.
                    'Verzeichnisse erfordern eine Anpassung.</div>'.
                    '<div>Details zu Ihrer Serverinstallation sehen Sie durch Klick auf den Button "PHPInfo anzeigen". '.
                    'Bei Fragen kontaktieren Sie uns bitte &uuml;ber <a href="mailto:support@shopmodule.com">'.
                    'support@shopmodule.com</a>.</div>',
                'hasFromToPhpVersion'    => 'Server verwendet PHP Version zwischen %1$s und %2$s',
                'hasFromToPhpVersion_DESC' => '<div>Das Modul erfordert eine PHP-Version zwischen %1$s und %2$s.</div>'.
                    '<div><div class="squ_bullet" style="background-color: green;"></div> Die passende PHP-Version '.
                    'ist auf Ihrem Server aktiv.</div>'.
                    '<div><div class="squ_bullet" style="background-color: red;"></div> Das Modul kann '.
                    'au&szlig;erhalb der PHP-Versionen nicht ausgef&uuml;hrt werden. Fragen Sie Ihren Serverprovider '.
                    'nach der Anpassung der PHP-Installation oder kontaktieren Sie uns f&uuml;r eine alternative '.
                    'Modulversion.</div>'.
                    '<div>&Uuml;ber den [+]-Button k&ouml;nnen Sie Ergebnisse zu den getesteten Verzeichnissen '.
                    'abrufen. Je nach Servereinstellung k&ouml;nnen die Ergebnisse abweichen. Nur die rot markierten '.
                    'Verzeichnisse erfordern eine Anpassung.</div>'.
                    '<div>Details zu Ihrer Serverinstallation sehen Sie durch Klick auf den Button "PHPInfo anzeigen". '.
                    'Bei Fragen kontaktieren Sie uns bitte &uuml;ber <a href="mailto:support@shopmodule.com">'.
                    'support@shopmodule.com</a>.</div>',
                'hasExtension'           => '%1$s-Erweiterung verf&uuml;gbar',
                'hasExtension_DESC'      => '<div>Das Modul erfordert die %1$s-Servererweiterung.</div>'.
                    '<div><div class="squ_bullet" style="background-color: green;"></div> Die %1$s-Erweiterung ist '.
                    'auf Ihrem Server vorhanden.</div>'.
                    '<div><div class="squ_bullet" style="background-color: red;"></div> Das Modul kann ohne die '.
                    '%1$s-Erweiterung nicht ausgef&uuml;hrt werden. Fragen Sie bei Ihrem Serverprovider nach der '.
                    'Installation dieser Erweiterung.</div>'.
                    '<div>&Uuml;ber den [+]-Button k&ouml;nnen Sie Ergebnisse zu den getesteten Verzeichnissen '.
                    'abrufen. Je nach Servereinstellung k&ouml;nnen die Ergebnisse abweichen. Nur die rot markierten '.
                    'Verzeichnisse erfordern eine Anpassung.</div>'.
                    '<div>Details zu Ihrer Serverinstallation sehen Sie durch Klick auf den Button "PHPInfo anzeigen". '.
                    'Bei Fragen kontaktieren Sie uns bitte &uuml;ber <a href="mailto:support@shopmodule.com">'.
                    'support@shopmodule.com</a>.</div>',
                'hasMinCurlVersion'      => 'mindestens cURL Version %1$s',
                'hasMinCurlVersion_DESC' => '<div>Das Modul ben&ouml;tigt cURL ab der Version %1$s.</div>'.
                    '<div><div class="squ_bullet" style="background-color: green;"></div> cURL ist in '.
                    'passender Version installiert.</div>'.
                    '<div><div class="squ_bullet" style="background-color: red;"></div> cURL ist nicht oder in einer '.
                    '&auml;lteren Version installiert. Fragen Sie Ihren Serverprovider nach einer passenden '.
                    'cURL-Version.</div>'.
                    '<div>&Uuml;ber den [+]-Button k&ouml;nnen Sie Ergebnisse zu den getesteten Verzeichnissen '.
                    'abrufen. Je nach Servereinstellung k&ouml;nnen die Ergebnisse abweichen. Nur die rot markierten '.
                    'Verzeichnisse erfordern eine Anpassung.</div>'.
                    '<div>Details zu Ihrer Serverinstallation sehen Sie durch Klick auf den Button "PHPInfo anzeigen". '.
                    'Bei Fragen kontaktieren Sie uns bitte &uuml;ber <a href="mailto:support@shopmodule.com">'.
                    'support@shopmodule.com</a>.</div>',
                'hasMinOpenSSLVersion'   => 'mindestens OpenSSL Version %1$s',
                'hasMinOpenSSLVersion_DESC' => '<div>Das Modul ben&ouml;tigt OpenSSL ab der Version %1$s.</div>'.
                    '<div><div class="squ_bullet" style="background-color: green;"></div> OpenSSL ist in '.
                    'passender Version installiert.</div>'.
                    '<div><div class="squ_bullet" style="background-color: red;"></div> OpenSSL ist nicht oder in '.
                    'einer &auml;lteren Version installiert. Fragen Sie Ihren Serverprovider nach einer passenden '.
                    'OpenSSL-Version.</div>'.
                    '<div>&Uuml;ber den [+]-Button k&ouml;nnen Sie Ergebnisse zu den getesteten Verzeichnissen '.
                    'abrufen. Je nach Servereinstellung k&ouml;nnen die Ergebnisse abweichen. Nur die rot markierten '.
                    'Verzeichnisse erfordern eine Anpassung.</div>'.
                    '<div>Details zu Ihrer Serverinstallation sehen Sie durch Klick auf den Button "PHPInfo anzeigen". '.
                    'Bei Fragen kontaktieren Sie uns bitte &uuml;ber <a href="mailto:support@shopmodule.com">'.
                    'support@shopmodule.com</a>.</div>',
                'hasMinShopVersion'      => 'mindestens Shop Version %1$s',
                'hasMinShopVersion_DESC' => '<div>Das Modul ist ab Shopversion %1$s freigegeben.</div>'.
                    '<div><div class="squ_bullet" style="background-color: green;"></div> Die Shopsoftware ist in '.
                    'passender Version installiert.</div>'.
                    '<div><div class="squ_bullet" style="background-color: red;"></div> Das Modul kann in dieser '.
                    'Version der Shopsoftware nicht installiert werden. Fragen Sie nach einer fr&uuml;heren '.
                    'Modulversion, die f&uuml;r Ihre Shopversion getestet wurde.</div>'.
                    '<div>Bei Fragen kontaktieren Sie uns bitte &uuml;ber <a href="mailto:support@shopmodule.com">'.
                    'support@shopmodule.com</a>.</div>',
                'hasMaxShopVersion'      => 'maximal Shop Version %1$s',
                'hasMaxShopVersion_DESC' => '<div>Das Modul ist bis zur Shopversion %1$s freigegeben.</div>'.
                    '<div><div class="squ_bullet" style="background-color: green;"></div> Die Shopsoftware ist in '.
                    'passender Version installiert.</div>'.
                    '<div><div class="squ_bullet" style="background-color: orange;"></div> Wir k&ouml;nnen nicht '.
                    'garantieren, dass das Modul in Ihrer Shopversion funktioniert. Fragen Sie nach einer aktuelleren '.
                    'Modulversion, die f&uuml;r Ihren Shop passt.</div>'.
                    '<div>Bei Fragen kontaktieren Sie uns bitte &uuml;ber <a href="mailto:support@shopmodule.com">'.
                    'support@shopmodule.com</a>.</div>',
                'hasMinModCfgVersion'    => '%2$s (ModCfg-Eintrag "%1$s") mindestens in Version %3$s',
                'hasMinModCfgVersion_DESC' => '<div>Das Modul ben&ouml;tigt die Zusatzsoftware "%2$s" mindestens in '.
                    'Version %3$s </div>'.
                    '<div><div class="squ_bullet" style="background-color: green;"></div> Die Software ist in '.
                    'passender Version installiert.</div>'.
                    '<div><div class="squ_bullet" style="background-color: red;"></div> Die Zusatzsoftware ist '.
                    'm&ouml;glicherweise gar nicht oder in falscher Version installiert. Bitte installieren Sie die '.
                    'Zusatzsoftware, bevor Sie diese Installation fortsetzen.</div>'.
                    '<div>Bei Fragen kontaktieren Sie uns bitte &uuml;ber <a href="mailto:support@shopmodule.com">'.
                    'support@shopmodule.com</a>.</div>',
                'hasMaxModCfgVersion'    => '%2$s (ModCfg-Eintrag "%1$s") maximal in Version %3$s',
                'hasMaxModCfgVersion_DESC' => '<div>Das Modul ben&ouml;tigt die Zusatzsoftware "%2$s" h&ouml;chstens '.
                    'in Version %3$s </div>'.
                    '<div><div class="squ_bullet" style="background-color: green;"></div> Die Software ist in '.
                    'passender Version installiert.</div>'.
                    '<div><div class="squ_bullet" style="background-color: red;"></div> Die Zusatzsoftware ist '.
                    'm&ouml;glicherweise gar nicht oder in falscher Version installiert. Bitte installieren Sie die '.
                    'Zusatzsoftware, bevor Sie diese Installation fortsetzen.</div>'.
                    '<div>Bei Fragen kontaktieren Sie uns bitte &uuml;ber <a href="mailto:support@shopmodule.com">'.
                    'support@shopmodule.com</a>.</div>',
                'requireNewLicence'      => 'bisheriger Lizenzschl&uuml;ssel kann verwendet werden',
                'requireNewLicence_DESC' => '<div>Diese Pr&uuml;fung versucht zu ermitteln, ob Sie f&uuml;r den '.
                    'Einsatz dieses Moduls einen aktuellen Lizenzschl&uuml;ssel ben&ouml;tigen:</div>'.
                    '<div><div class="squ_bullet" style="background-color: green;"></div> Sie haben f&uuml;r dieses '.
                    'Modul einen Lizenzschl&uuml;ssel hinterlegt, der wahrscheinlich auch f&uuml;r die neue '.
                    'Modulversion geeignet ist.</div>'.
                    '<div><div class="squ_bullet" style="background-color: orange;"></div> Sie ben&ouml;tigen '.
                    'f&uuml;r dieses Modul wahrscheinlich einen neuen Lizenzschl&uuml;ssel. Haben Sie diesen schon '.
                    'vorliegen, f&uuml;hren Sie die Installation aus und tragen den Lizenzschl&uuml;ssel dann im '.
                    'Adminbereich Ihres Shops ein. Ansonsten k&ouml;nnen Sie den Lizenzschl&uuml;ssel in unserem Shop '.
                    '<a href="http://www.oxidmodule.com" target="oxidmodule.com">www.oxidmodule.com</a> erwerben oder '.
                    'sich ebenfalls im Adminbereich Ihres Shops einen kostenfreien Test-Lizenzschl&uuml;ssel '.
                    'erstellen.</div>'.
                    '<div>F&uuml;r Details wenden Sie sich bitte an <a href="mailto:buchhaltung@shopmodule.com">'.
                    'buchhaltung@shopmodule.com</a>.</div>',
                'hasModCfg'              => '<a href="http://www.oxidmodule.com/Connector" target="Connector">Modul-'.
                    'Connector</a> installiert',
                'hasModCfg_DESC' => '<div>Das Modul erfordert zwingend den D<sup>3</sup> Modul-Connector.</div>'.
                    '<div><div class="squ_bullet" style="background-color: green;"></div> Der Modul-Connector ist '.
                    'installiert.</div>'.
                    '<div><div class="squ_bullet" style="background-color: red;"></div> Das Modul kann ohne den Modul-'.
                    'Connector nicht ausgef&uuml;hrt werden. Bitte laden Sie sich diesen kostenfrei aus unserem Shop '.
                    'unter <a href="http://www.oxidmodule.com/connector/" target="connector">www.oxidmodule.com/'.
                    'connector/</a> und installieren diesen vorab.</div>'.
                    '<div>Bei Fragen kontaktieren Sie uns bitte &uuml;ber <a href="mailto:support@shopmodule.com">'.
                    'support@shopmodule.com</a>.</div>',
                'isShopEdition'          => 'ist Shopedition %1$s',
                'isShopEdition_DESC' => '<div>Das Modul erfordert eine dieser Shopeditionen: %1$s</div>'.
                    '<div><div class="squ_bullet" style="background-color: green;"></div> Der Shop ist in der '.
                    'passenden Edition installiert.</div>'.
                    '<div><div class="squ_bullet" style="background-color: red;"></div> Das Modul kann in Ihrer '.
                    'Shopedition nicht ausgef&uuml;hrt werden. Bitte fragen Sie nach einer Modulversion f&uuml;r Ihre '.
                    'Shopedition.</div>'.
                    '<div>Bei Fragen kontaktieren Sie uns bitte &uuml;ber <a href="mailto:support@shopmodule.com">'.
                    'support@shopmodule.com</a>.</div>',
                'hasZendLoaderOptimizer' => 'Zend Optimizer (PHP 5.2) oder Zend Guard Loader (PHP 5.3, 5.4, 5.5, 5.6) '.
                    'installiert (auf passendes Zend-Installationspaket achten!)',
                'hasZendLoaderOptimizer_DESC' => '<div>Das Modul erfordert (je nach PHP-Version) den Zend Optimizer '.
                    'bzw. den Zend Guard Loader. Achten Sie darauf, ein f&uuml;r den verf&uuml;gbaren Decoder '.
                    'erstelltes Installationspaket zu verwenden.</div>'.
                    '<div><div class="squ_bullet" style="background-color: green;"></div> Der passende Decoder ist '.
                    'auf Ihrem Server installiert.</div>'.
                    '<div><div class="squ_bullet" style="background-color: orange;"></div> Der passende Decoder ist '.
                    'auf Ihrem Server installiert. Es ist eine zus&auml;tzliche Erweiterungen (Zend OPcache) installiert, '.
                    'die im Zusammenspiel mit dem Decoder Fehler verursachen kann.</div>'.
                    '<div><div class="squ_bullet" style="background-color: red;"></div> Das Modul kann ohne den '.
                    'passenden Decoder nicht ausgef&uuml;hrt werden. Fragen Sie Ihren Serverprovider nach der '.
                    'Installation des passenden Zend-Decoders.</div>'.
                    '<div>&Uuml;ber den [+]-Button k&ouml;nnen Sie Ergebnisse zu den getesteten Verzeichnissen '.
                    'abrufen. Je nach Servereinstellung k&ouml;nnen die Ergebnisse abweichen. Nur die rot markierten '.
                    'Verzeichnisse erfordern eine Anpassung.</div>'.
                    '<div>Details zu Ihrer Serverinstallation sehen Sie durch Klick auf den Button "PHPInfo anzeigen". '.
                    'Bei Fragen kontaktieren Sie uns bitte &uuml;ber <a href="mailto:support@shopmodule.com">'.
                    'support@shopmodule.com</a>.</div>',
                'hasIonCubeLoader'       => 'ionCube Loader installiert (auf passendes ionCube-Installationspaket achten!)',
                'hasIonCubeLoader_DESC'  => '<div>Das Modul erfordert den ionCube Loader. Achten Sie darauf, ein '.
                    'f&uuml;r den verf&uuml;gbaren Decoder erstelltes Installationspaket zu verwenden.</div>'.
                    '<div><div class="squ_bullet" style="background-color: green;"></div> Der passende Decoder ist '.
                    'auf Ihrem Server installiert.</div>'.
                    '<div><div class="squ_bullet" style="background-color: red;"></div> Das Modul kann ohne den '.
                    'passenden Decoder nicht ausgef&uuml;hrt werden. Fragen Sie Ihren Serverprovider nach der '.
                    'Installation des ionCube Loaders.</div>'.
                    '<div>&Uuml;ber den [+]-Button k&ouml;nnen Sie Ergebnisse zu den getesteten Verzeichnissen '.
                    'abrufen. Je nach Servereinstellung k&ouml;nnen die Ergebnisse abweichen. Nur die rot markierten '.
                    'Verzeichnisse erfordern eine Anpassung.</div>'.
                    '<div>Details zu Ihrer Serverinstallation sehen Sie durch Klick auf den Button "PHPInfo anzeigen". '.
                    'Bei Fragen kontaktieren Sie uns bitte &uuml;ber <a href="mailto:support@shopmodule.com">'.
                    'support@shopmodule.com</a>.</div>',
                'hasIonCubeOrZendLoader'       => 'ionCube Loader oder Zend Optimizer / Guard Loader installiert '.
                    '<span class="note">(%1$s)</span>',
                'hasIonCubeOrZendLoader_DESC'  => '<div>Das Modul erfordert den ionCube Loader oder den Zend '.
                    'Optimizer / Guard Loader. Achten Sie darauf, ein f&uuml;r die verf&uuml;gbaren Decoder erstelltes '.
                    'Installationspaket zu verwenden (%1$s).</div>'.
                    '<div><div class="squ_bullet" style="background-color: green;"></div> Ein passender Decoder ist '.
                    'auf Ihrem Server installiert.</div>'.
                    '<div><div class="squ_bullet" style="background-color: orange;"></div> Ein passender Decoder ist '.
                    'auf Ihrem Server installiert. Es ist jedoch ein Abbruchgrund festgestellt worden, der zu Fehlern '.
                    'f&uuml;hren kann. Details entnehmen Sie bitte den folgenden Hinweisen.</div>'.
                    '<div><div class="squ_bullet" style="background-color: red;"></div> Das Modul kann ohne einen '.
                    'passenden Decoder nicht ausgef&uuml;hrt werden. Fragen Sie Ihren Serverprovider nach der '.
                    'Installation des ionCube Loaders oder des Zend Optimizers / Guard Loaders.</div>'.
                    '%2$s'.
                    '<div>&Uuml;ber den [+]-Button k&ouml;nnen Sie Ergebnisse zu den getesteten Verzeichnissen '.
                    'abrufen. Je nach Servereinstellung k&ouml;nnen die Ergebnisse abweichen. Nur die rot markierten '.
                    'Verzeichnisse erfordern eine Anpassung.</div>'.
                    '<div>Details zu Ihrer Serverinstallation sehen Sie durch Klick auf den Button "PHPInfo anzeigen". '.
                    'Bei Fragen kontaktieren Sie uns bitte &uuml;ber <a href="mailto:support@shopmodule.com">'.
                    'support@shopmodule.com</a>.</div>',
                'RemoteVersionDiff'      => ' <span class="note">(Remotescript hat abweichende Version oder ist nicht '.
                    'pr&uuml;fbar, Ergebnis mglw. nicht sicher)</span>',
                'globalSuccess'          => 'Die technische Pr&uuml;fung war erfolgreich. Sie k&ouml;nnen das Modul '.
                    'installieren.*<br><br>',
                'globalNotSuccess'       => 'Die technische Pr&uuml;fung war nicht erfolgreich. Bitte kontrollieren '.
                    'Sie die rot oder orange markierten Bedingungen.<br><br>',
                'deleteFile1'            => 'L&ouml;schen Sie diese Datei nach der Verwendung bitte unbedingt wieder von '.
                    'Ihrem Server! Klicken Sie <a href="',
                'deleteFile2'            => '?fnc=deleteme">hier</a>, um diese Datei zu l&ouml;schen.',
                'showPhpInfo'            => 'PHPinfo anzeigen',
                'dependentoffurther'     => '* abh&auml;ngig von ungepr&uuml;ften Voraussetzungen',
                'oneandonedescription'   => '** gepr&uuml;ft wurde das Ausf&uuml;hrungsverzeichnis, '.
                    'providerabh&auml;ngig m&uuml;ssen Unterverzeichnisse separat gepr&uuml;ft werden (z.B. bei 1&1)',
                'or'                     => ' oder ',
                'toggleswitch'           => 'Klick f&uuml;r Details zur Pr&uuml;fung',
                'unableDeleteFile'       => 'Datei konnte nicht gel&ouml;scht werden. Bitte l&ouml;schen Sie diese '.
                    'manuell.',
                'goodBye'                => 'Auf Wiedersehen.',
                'unableExecuteDirectoryIterator' => 'Es k&ouml;nnen nicht alle Unterverzeichnisse auf weitere Pr&uuml;fungen '.
                    'kontrolliert werden. (%1$s)',
                'availableDecoder'       => 'verf&uuml;gbar: %1$s - auf passendes Installationspaket achten!',
                'noDecoderAvailable'     => 'kein verwendbarer Decoder verf&uuml;gbar',
                'availableDecoder_hasZendLoaderOptimizer' => 'Zend Guard Loader / Optimizer',
                'notAvailableDecoder_hasZendLoaderOptimizer' => '<li>Der Zend Guard Loader / Optimizer ist nicht '.
                    'installiert.</li>',
                'decoderIssue_hasZendLoaderOptimizer' => '<li>Der Zend-Decoder ist '.
                    'auf Ihrem Server installiert. Es ist jedoch eine zus&auml;tzliche Erweiterungen (Zend OPcache) '.
                    'installiert, die im Zusammenspiel mit dem Zend-Decoder Fehler verursachen kann.</li>',
                'availableDecoder_hasIonCubeLoader' => 'ionCube Loader',
                'notAvailableDecoder_hasIonCubeLoader' => '<li>Der ionCube Loader ist nicht installiert.</li>',
                'decoderIssue_hasIonCubeLoader' => '<li>Es wurde ein nicht definierter Abbruchgrund bei der '.
                    'Verwendung des ionCube-Decoders festgestellt.</li>',
            ),
            'en' => array(
                'RequCheck'              => 'Requirement check',
                'ExecNotice'             => 'Execute this check script in the root directory of your shop. In this '.
                    'case only checks can executed succesfully.',
                'RequSucc'               => 'condition is fulfilled',
                'RequNotSucc'            => 'condition isn\'t fulfilled',
                'RequUnknown'            => 'condition unclear, see notes in help text',
                'RequNotCheckable'       => 'condition isn\'t checkable',
                'hasMinPhpVersion'       => 'at least PHP version %1$s',
                'hasMinPhpVersion_DESC'  => '<div>The module requires a PHP version at least %1$s</div>'.
                    '<div><div class="squ_bullet" style="background-color: green;"></div> The appropriate version of PHP '.
                    'is activated on your server.</div>'.
                    '<div><div class="squ_bullet" style="background-color: red;"></div> The module can not be executed within '.
                    'the actived PHP version. Ask your server provider for for the adaption of your PHP installation or '.
                    'contact us for another module version.</div>'.
                    '<div>The [+] button show details for all tested directories. Depending on the server settings, '.
                    'the results may vary. Only the red marked directories requires adaptation.</div>'.
                    '<div>Details about your server installation you can see by clicking on the button "show PHPinfo". '.
                    'If you have any questions, please contact us at <a href="mailto:support@shopmodule.com">'.
                    'support@shopmodule.com</a>.</div>',
                'hasMaxPhpVersion'       => 'not more than PHP version %1$s',
                'hasMaxPhpVersion_DESC'  => '<div>The module requires a PHP version not more than %1$s</div>'.
                    '<div><div class="squ_bullet" style="background-color: green;"></div> The appropriate version of PHP '.
                    'is activated on your server.</div>'.
                    '<div><div class="squ_bullet" style="background-color: red;"></div> The module can not be executed within '.
                    'the actived PHP version. Ask your server provider for for the adaption of your PHP installation or '.
                    'contact us for another module version.</div>'.
                    '<div>The [+] button show details for all tested directories. Depending on the server settings, '.
                    'the results may vary. Only the red marked directories requires adaptation.</div>'.
                    '<div>Details about your server installation you can see by clicking on the button "show PHPinfo". '.
                    'If you have any questions, please contact us at <a href="mailto:support@shopmodule.com">'.
                    'support@shopmodule.com</a>.</div>',
                'hasFromToPhpVersion'    => 'server use PHP version between %1$s and %2$s',
                'hasFromToPhpVersion_DESC'=> '<div>The module requires a PHP version between %1$s and %2$s</div>'.
                    '<div><div class="squ_bullet" style="background-color: green;"></div> The appropriate version of PHP '.
                    'is activated on your server.</div>'.
                    '<div><div class="squ_bullet" style="background-color: red;"></div> The module can not be executed within '.
                    'the actived PHP version. Ask your server provider for for the adaption of your PHP installation or '.
                    'contact us for another module version.</div>'.
                    '<div>The [+] button show details for all tested directories. Depending on the server settings, '.
                    'the results may vary. Only the red marked directories requires adaptation.</div>'.
                    '<div>Details about your server installation you can see by clicking on the button "show PHPinfo". '.
                    'If you have any questions, please contact us at <a href="mailto:support@shopmodule.com">'.
                    'support@shopmodule.com</a>.</div>',
                'hasExtension'           => '%1$s extension is available',
                'hasExtension_DESC'      => '<div>The module requires the %1$s server extension.</div>'.
                    '<div><div class="squ_bullet" style="background-color: green;"></div> The %1$s server extension is '.
                    'available on your server.</div>'.
                    '<div><div class="squ_bullet" style="background-color: red;"></div> The module can not be executed '.
                    'without the %1$s extension. Ask your server provider for installing this extension.</div>'.
                    '<div>The [+] button show details for all tested directories. Depending on the server settings, '.
                    'the results may vary. Only the red marked directories requires adaptation.</div>'.
                    '<div>Details about your server installation you can see by clicking on the button "show PHPinfo". '.
                    'If you have any questions, please contact us at <a href="mailto:support@shopmodule.com">'.
                    'support@shopmodule.com</a>.</div>',
                'hasMinCurlVersion'      => 'at least cURL version %1$s',
                'hasMinCurlVersion_DESC' => '<div>The module requires at least cURL version %1$s.</div>'.
                    '<div><div class="squ_bullet" style="background-color: green;"></div> cURL is available '.
                    'in a compatible version.</div>'.
                    '<div><div class="squ_bullet" style="background-color: red;"></div> cURL is not installed or '.
                    'installed in an older version. Ask your server provider for an appropriate version.</div>'.
                    '<div>The [+] button show details for all tested directories. Depending on the server settings, '.
                    'the results may vary. Only the red marked directories requires adaptation.</div>'.
                    '<div>Details about your server installation you can see by clicking on the button "show PHPinfo". '.
                    'If you have any questions, please contact us at <a href="mailto:support@shopmodule.com">'.
                    'support@shopmodule.com</a>.</div>',
                'hasMinOpenSSLVersion'   => 'at least OpenSSL version %1$s',
                'hasMinOpenSSLVersion_DESC' => '<div>The module requires at least OpenSSL version %1$s.</div>'.
                    '<div><div class="squ_bullet" style="background-color: green;"></div> OpenSSL is availabe '.
                    'in a compatible version.</div>'.
                    '<div><div class="squ_bullet" style="background-color: red;"></div> OpenSSL is not installed or '.
                    'installed in an older version. Ask your server provider for an appropriate version.</div>'.
                    '<div>The [+] button show details for all tested directories. Depending on the server settings, '.
                    'the results may vary. Only the red marked directories requires adaptation.</div>'.
                    '<div>Details about your server installation you can see by clicking on the button "show PHPinfo". '.
                    'If you have any questions, please contact us at <a href="mailto:support@shopmodule.com">'.
                    'support@shopmodule.com</a>.</div>',
                'hasMinShopVersion'      => 'at least shop version %1$s',
                'hasMinShopVersion_DESC' => '<div>The module is released to shop version %1$s</div>'.
                    '<div><div class="squ_bullet" style="background-color: green;"></div> The shop software is installed '.
                    'in a compatible version.</div>'.
                    '<div><div class="squ_bullet" style="background-color: red;"></div> We can not guarantee, '.
                    'that this module works properly in your shop version. Please ask for a matching module version.</div>'.
                    '<div>If you have any questions, please contact us at <a href="mailto:support@shopmodule.com">'.
                    'support@shopmodule.com</a>.</div>',
                'hasMaxShopVersion'      => 'not more than shop version %1$s',
                'hasMaxShopVersion_DESC' => '<div>The module is released to shop version %1$s</div>'.
                    '<div><div class="squ_bullet" style="background-color: green;"></div> The shop software is installed '.
                    'in a compatible version.</div>'.
                    '<div><div class="squ_bullet" style="background-color: orange;"></div> We can not guarantee, '.
                    'that this module works properly in your shop version. Please ask for a matching module version.</div>'.
                    '<div>If you have any questions, please contact us at <a href="mailto:support@shopmodule.com">'.
                    'support@shopmodule.com</a>.</div>',
                'hasMinModCfgVersion'    => '%2$s (ModCfg item "%1$s") at least in version %3$s',
                'hasMinModCfgVersion_DESC' => '<div>The module requires additional software "%2$s" at least '.
                    'in version %3$s</div>'.
                    '<div><div class="squ_bullet" style="background-color: green;"></div> The software is installed '.
                    'in a compatible version.</div>'.
                    '<div><div class="squ_bullet" style="background-color: red;"></div> The additional software is '.
                    'not installed or in wrong version available. Please install the additional software before '.
                    'proceeding this installation.</div>'.
                    '<div>If you have any questions, please contact us at <a href="mailto:support@shopmodule.com">'.
                    'support@shopmodule.com</a>.</div>',
                'hasMaxModCfgVersion'    => '%2$s (ModCfg item "%1$s") not more than in version %3$s',
                'hasMaxModCfgVersion_DESC' => '<div>The module requires additional software "%2$s" not more than '.
                    'in version %3$s</div>'.
                    '<div><div class="squ_bullet" style="background-color: green;"></div> The software is installed '.
                    'in a compatible version.</div>'.
                    '<div><div class="squ_bullet" style="background-color: red;"></div> The additional software is '.
                    'be installed or in wrong version available. Please install the additional software before '.
                    'proceeding this installation.</div>'.
                    '<div>If you have any questions, please contact us at <a href="mailto:support@shopmodule.com">'.
                    'support@shopmodule.com</a>.</div>',
                'requireNewLicence'      => 'former licence key can be used',
                'requireNewLicence_DESC' => '<div>This test tries to determine whether you need a new licence key '.
                    'for the use of this module</div>'.
                    '<div><div class="squ_bullet" style="background-color: green;"></div> You have stored a license key '.
                    'for this module, which is probably also compatible for the new module version.</div>'.
                    '<div><div class="squ_bullet" style="background-color: red;"></div> You need likely a new license '.
                    'key for this module. Do you have already one, run the installation and then apply the license '.
                    'key in the admin panel of your shop. Otherwise, you can purchase it in our shop '.
                    '<a href="http://www.oxidmodule.com" target="oxidmodule.com">oxidmodule.com</a> or also create a '.
                    'free trial license key in the admin panel of your shop.</div>'.
                    '<div>If you have any questions, please contact us at <a href="mailto:buchhaltung@shopmodule.com">'.
                    'buchhaltung@shopmodule.com</a>.</div>',
                'hasModCfg'              => '<a href="http://www.oxidmodule.com/Connector" target="Connector">Module '.
                    'Connector</a> installed',
                'hasModCfg_DESC'         => '<div>The module requires necessarily the D<sup>3</sup> Module Connector.</div>'.
                    '<div><div class="squ_bullet" style="background-color: green;"></div> The Module Connector is '.
                    'installed.</div>'.
                    '<div><div class="squ_bullet" style="background-color: red;"></div> The module can not be executed '.
                    'without the Module Connector. Please download this free of charge from our shop '.
                    '<a href="http://www.oxidmodule.com/connector/" target="connector">www.oxidmodule.com/'.
                    'connector/</a> and install it beforehand.</div>'.
                    '<div>If you have any questions, please contact us at <a href="mailto:support@shopmodule.com">'.
                    'support@shopmodule.com</a>.</div>',
                'isShopEdition'          => 'shop edition is %1$s',
                'isShopEdition_DESC'     => '<div>The module requires one of these shop editions: %1$s</div>'.
                    '<div><div class="squ_bullet" style="background-color: green;"></div> The shop is installed '.
                    'in the appropriate edition.</div>'.
                    '<div><div class="squ_bullet" style="background-color: red;"></div> The module can not be executed '.
                    'in your shop edition. Please ask for a module version for your shop edition.</div>'.
                    '<div>If you have any questions, please contact us at <a href="mailto:support@shopmodule.com">'.
                    'support@shopmodule.com</a>.</div>',
                'hasZendLoaderOptimizer' => 'Zend Optimizer (PHP 5.2) or Zend Guard Loader (PHP 5.3, 5.4, 5.5, 5.6) '.
                    'installed (pay attention to the compatible Zend installation package!)',
                'hasZendLoaderOptimizer_DESC' => '<div>The module requires (depending on the PHP version) the Zend Guard Optimizer '.
                    'or the Zend Guard Loader.</div>'.
                    '<div><div class="squ_bullet" style="background-color: green;"></div> The appropriate decoder is '.
                    'installed on your server.</div>'.
                    '<div><div class="squ_bullet" style="background-color: orange;"></div> The decoder is '.
                    'installed on your server. There is an additional installed extension (Zend OPcache), '.
                    'which can cause errors in combination with the decoder.</div>'.
                    '<div><div class="squ_bullet" style="background-color: red;"></div> The decoder isn\'t '.
                    'installed on your server. The module can\'t installed or executed. Please contact your server provider.</div>'.
                    '<div>The [+] button show details for all tested directories. Depending on the server settings, '.
                    'the results may vary. Only the red marked directories requires adaptation.</div>'.
                    '<div>Details about your server installation you can see by clicking on the button "show PHPinfo". '.
                    'If you have any questions, please contact us at <a href="mailto:support@shopmodule.com">'.
                    'support@shopmodule.com</a>.</div>',
                'hasIonCubeLoader'       => 'ionCube Loader installed (pay attention to the compatible ionCube installation package!)',
                'hasIonCubeLoader_DESC'   => '<div>The module requires the ionCube Loader.</div>'.
                    '<div><div class="squ_bullet" style="background-color: green;"></div> The appropriate decoder is '.
                    'installed on your server.</div>'.
                    '<div><div class="squ_bullet" style="background-color: red;"></div> The decoder isn\'t '.
                    'installed on your server. The module can\'t installed or executed. Please contact your server provider.</div>'.
                    '<div>The [+] button show details for all tested directories. Depending on the server settings, '.
                    'the results may vary. Only the red marked directories requires adaptation.</div>'.
                    '<div>Details about your server installation you can see by clicking on the button "show PHPinfo". '.
                    'If you have any questions, please contact us at <a href="mailto:support@shopmodule.com">'.
                    'support@shopmodule.com</a>.</div>',
                'hasIonCubeOrZendLoader'       => 'ionCube Loader or Zend Optimizer / Guard Loader installed '.
                    '<span class="note">(%1$s)</span>',
                'hasIonCubeOrZendLoader_DESC'   => '<div>The module requires the ionCube Loader or the Zend '.
                    'Optimizer / Guard Loader. Pay attention to use a compatible installation package (%1$s).</div>'.
                    '<div><div class="squ_bullet" style="background-color: green;"></div> One of the appropriate '.
                    'decoders is installed on your server.</div>'.
                    '<div><div class="squ_bullet" style="background-color: orange;"></div> One of the appropriate '.
                    'decoder is installed on your server. An undefined abort reason has been found, which can cause '.
                    'to errors. For details, please refer to the following notes.</div>'.
                    '<div><div class="squ_bullet" style="background-color: red;"></div> None of the decoders is '.
                    'installed on your server. The module can\'t installed or executed. Please contact your server provider.</div>'.
                    '%2$s'.
                    '<div>The [+] button show details for all tested directories. Depending on the server settings, '.
                    'the results may vary. Only the red marked directories requires adaptation.</div>'.
                    '<div>Details about your server installation you can see by clicking on the button "show PHPinfo". '.
                    'If you have any questions, please contact us at <a href="mailto:support@shopmodule.com">'.
                    'support@shopmodule.com</a>.</div>',
                'RemoteVersionDiff'      => ' <span class="note">(Remote script has different version or isn\'t '.
                    'callable, result may not be safe)</span>',
                'globalSuccess'          => 'The technical test was successful. Your server is ready for installing '.
                    'the module.*<br><br>',
                'globalNotSuccess'       => 'The technical test wasn\'t successfull. Please check the red or orange marked '.
                    'conditions.<br><br>',
                'deleteFile1'            => 'Please delete this file after use on your server! Click <a href="',
                'deleteFile2'            => '?fnc=deleteme">here</a>, to delete this file.',
                'showPhpInfo'            => 'show PHPinfo',
                'dependentoffurther'     => '* dependent of further unchecked conditions',
                'oneandonedescription'   => '** this check use execution directory only, provider dependend '.
                    'subdirectories have to check separately (e.g. at 1&1)',
                'or'                     => ' or ',
                'toggleswitch'           => 'click for details',
                'unableDeleteFile'       => 'Unable to delete file. Please delete it manually.',
                'goodBye'                => 'Good Bye.',
                'unableExecuteDirectoryIterator' => 'Unable to check subdirectories for further checks. (%1$s)',
                'availableDecoder'       => 'available: %1$s - pay attention to the compatible installation package!',
                'noDecoderAvailable'     => 'no usable decoder available',
                'availableDecoder_hasZendLoaderOptimizer' => 'Zend Guard Loader / Optimizer',
                'notAvailableDecoder_hasZendLoaderOptimizer' => '<li>Zend Guard Loader / Optimizer isn\'t '.
                    'available.</li>',
                'decoderIssue_hasZendLoaderOptimizer' => '<li>The Zend decoder is '.
                    'installed on your server. There is an additional installed extension (Zend OPcache), '.
                    'which can cause errors in combination with the decoder.</li>',
                'availableDecoder_hasIonCubeLoader' => 'ionCube Loader',
                'notAvailableDecoder_hasIonCubeLoader' => '<li>ionCube Loader isn\'t available.</li>',
                'decoderIssue_hasIonCubeLoader' => '<li>An undefined abort reason has been found when using the '.
                    'ionCube decoder.</li>',
            ),
        );
    }
}

/**
 * Class requRemote
 */
class requRemote
{
    public $blUseRemote = true;

    public $oModuleData;

    /**
     * @param $sModId
     * @param $sModVersion
     * @param $sShopEdition
     *
     * @return bool|array
     */
    public function getShopEdition($sModId, $sModVersion, $sShopEdition)
    {
        $sUrl = "moduleversion/";
        $sUrl .= 'modid/' . urlencode($sModId) . '/';
        $sUrl .= 'forcemodversion/' . urlencode($sModVersion) . '/';
        $sUrl .= 'edition/' . urlencode($sShopEdition) . '/';

        /** @var stdClass $oModuleData */
        $oModuleData = $this->_getRemoteServerData($sUrl);

        if ($oModuleData->status == 'OK' && isset($oModuleData->moduleversion->compatible_release)) {
            return explode(',', $oModuleData->moduleversion->compatible_release->shopedition);
        }

        return false;
    }

    /**
     * @param $sModId
     * @param $sModVersion
     * @param $sShopEdition
     *
     * @return bool|string
     */
    public function getMinShopVersion($sModId, $sModVersion, $sShopEdition)
    {
        $sUrl = "moduleversion/";
        $sUrl .= 'modid/' . urlencode($sModId) . '/';
        $sUrl .= 'forcemodversion/' . urlencode($sModVersion) . '/';
        $sUrl .= 'edition/' . urlencode($sShopEdition) . '/';

        /** @var stdClass $oModuleData */
        $oModuleData = $this->_getRemoteServerData($sUrl);

        if ($oModuleData->status == 'OK' && isset($oModuleData->moduleversion->compatible_release)) {
            return $this->shortenVersion($oModuleData->moduleversion->compatible_release->fromshopversion);
        }

        return false;
    }

    /**
     * @param $sModId
     * @param $sModVersion
     * @param $sShopEdition
     *
     * @return bool|string
     */
    public function getMaxShopVersion($sModId, $sModVersion, $sShopEdition)
    {
        $sUrl = "moduleversion/";
        $sUrl .= 'modid/' . urlencode($sModId) . '/';
        $sUrl .= 'forcemodversion/' . urlencode($sModVersion) . '/';
        $sUrl .= 'edition/' . urlencode($sShopEdition) . '/';

        /** @var stdClass $oModuleData */
        $oModuleData = $this->_getRemoteServerData($sUrl);

        if ($oModuleData->status == 'OK' && isset($oModuleData->moduleversion->compatible_release)) {
            return $this->shortenVersion($oModuleData->moduleversion->compatible_release->toshopversion);
        }

        return false;
    }

    /**
     * @param $sUrl
     *
     * @return stdClass
     */
    protected function _getRemoteServerData($sUrl)
    {
        if (isset($this->oModuleData[$sUrl])) {
            return $this->oModuleData[$sUrl];
        }

        $oFailureData         = new stdClass();
        $oFailureData->status = 'NOK';

        if (false === $this->blUseRemote) {
            return $oFailureData;
        }
        $sHost = "http://update.oxidmodule.com";
        $sData = $this->curlConnect($sHost . '/serialized/' . $sUrl);
        $oData = unserialize($sData);

        if (false == $oData) {
            return $oFailureData;
        }
        $this->oModuleData[$sUrl] = $oData;

        return $this->oModuleData[$sUrl];
    }

    /**
     * @param $sFilePath
     *
     * @return string
     */
    public function curlConnect($sFilePath)
    {
        $sContent = '';

        if (($ch = $this->_hasCurl())) {
            $sCurl_URL = preg_replace('@^((http|https)://)@', '', $sFilePath);
            curl_setopt($ch, CURLOPT_URL, $sCurl_URL);
            if ($_SERVER['HTTP_USER_AGENT']) {
                curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
            }
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
            curl_setopt($ch, CURLOPT_POST, 0);
            $sContent = curl_exec($ch);
        }

        return $sContent;
    }

    /**
     * @return null|resource
     */
    protected function _hasCurl()
    {
        if (extension_loaded('curl')
            && function_exists('curl_init')
            && function_exists('curl_exec')
        ) {
            return curl_init();
        }

        return null;
    }

    /**
     * @param $sVersion
     *
     * @return string
     */
    public function shortenVersion($sVersion)
    {
        $aVersion = explode('.', $sVersion);

        unset($aVersion[3]);

        return implode('.', $aVersion);
    }
}

/**
 * Class requTests
 * contains test functions
 */
class requTests
{
    public $oBase;
    public $oDb;
    public $oConfig;
    public $blGlobalResult = false;

    /**
     * @param requCheck  $oCheckInstance
     * @param requConfig $oConfig
     * @param            $oDb
     * @param requRemote $oRemote
     */
    public function __construct(requCheck $oCheckInstance, requConfig $oConfig, $oDb, requRemote $oRemote)
    {
        $this->oBase = $oCheckInstance;
        $this->oConfig = $oConfig;
        $this->oDb = $oDb;
        $this->oRemote = $oRemote;
    }

    /**
     * @return requCheck
     */
    public function getBase()
    {
        return $this->oBase;
    }

    public function getDb()
    {
        return $this->oDb;
    }

    /**
     * @return string
     */
    public function getBasePath()
    {
        return $this->getBase()->getBasePath();
    }

    /**
     * @param bool $blResult
     */
    public function setGlobalResult($blResult)
    {
        $this->getBase()->blGlobalResult = $blResult;
    }

    /**
     * @param      $sMethodName
     * @param null $aArguments
     *
     * @return array
     */
    public function checkInSubDirs($sMethodName, $aArguments = null)
    {
        return $this->getBase()->checkInSubDirs($sMethodName, $aArguments);
    }

    /**
     * @param $aConfiguration
     *
     * @return array
     */
    public function hasMinPhpVersion(&$aConfiguration)
    {
        $aResult = array($this->getBasePath() => false);

        if (version_compare(phpversion(), $aConfiguration['aParams']['version'], '>=')) {
            $aResult[$this->getBasePath()] = true;
        }

        $aResult = array_merge($aResult, $this->checkInSubDirs(__FUNCTION__, $aConfiguration['aParams']));

        return $aResult;
    }

    /**
     * @param $aConfiguration
     *
     * @return array
     */
    public function hasFromToPhpVersion(&$aConfiguration)
    {
        $aResult = array($this->getBasePath() => false);

        if ((version_compare(phpversion(), $aConfiguration['aParams']['from'], '>=')) &&
            (version_compare(phpversion(), $aConfiguration['aParams']['to'], '<'))
        ) {
            $aResult[$this->getBasePath()] = true;
        }

        $aResult = array_merge($aResult, $this->checkInSubDirs(__FUNCTION__, $aConfiguration['aParams']));

        return $aResult;
    }

    /**
     * @param $aConfiguration
     *
     * @return array
     */
    public function hasMaxPhpVersion(&$aConfiguration)
    {
        $aResult = array($this->getBasePath() => false);

        if (version_compare(phpversion(), $aConfiguration['aParams']['version'], '<=')) {
            $aResult[$this->getBasePath()] = true;
        }

        $aResult = array_merge($aResult, $this->checkInSubDirs(__FUNCTION__, $aConfiguration['aParams']));

        return $aResult;
    }

    /**
     * @param $aConfiguration
     *
     * @return array
     */
    public function hasExtension(&$aConfiguration)
    {
        $aResult = array($this->getBasePath() => false);

        if (extension_loaded($aConfiguration['aParams']['type'])) {
            $aResult[$this->getBasePath()] = true;
        }

        $aResult = array_merge($aResult, $this->checkInSubDirs(__FUNCTION__, $aConfiguration['aParams']));

        return $aResult;
    }

    /**
     * @param $aConfiguration
     * @return array
     */
    public function hasMinCurlVersion(&$aConfiguration)
    {
        $aCurlVersion = curl_version();
        $aResult = array(
            $this->getBasePath() => version_compare($aCurlVersion['version'], $aConfiguration['aParams']['version'], '>=')
        );

        $aResult = array_merge($aResult, $this->checkInSubDirs(__FUNCTION__, $aConfiguration['aParams']));

        return $aResult;
    }

    /**
     * @param $aConfiguration
     * @return array
     */
    public function hasMinOpenSSLVersion(&$aConfiguration)
    {
        $aResult = array(
            $this->getBasePath() => version_compare($this->get_openssl_version_number(true), $aConfiguration['aParams']['version'], '>=')
        );

        $aResult = array_merge($aResult, $this->checkInSubDirs(__FUNCTION__, $aConfiguration['aParams']));

        return $aResult;
    }

    /**
     * @param bool $patch_as_number
     * @param null $openssl_version_number
     * @return bool|string
     */
    protected function get_openssl_version_number($patch_as_number=false,$openssl_version_number=null) {
        if (is_null($openssl_version_number)) $openssl_version_number = OPENSSL_VERSION_NUMBER;
        $openssl_numeric_identifier = str_pad((string)dechex($openssl_version_number),8,'0',STR_PAD_LEFT);

        $openssl_version_parsed = array();
        $preg = '/(?<major>[[:xdigit:]])(?<minor>[[:xdigit:]][[:xdigit:]])(?<fix>[[:xdigit:]][[:xdigit:]])';
        $preg.= '(?<patch>[[:xdigit:]][[:xdigit:]])(?<type>[[:xdigit:]])/';
        preg_match_all($preg, $openssl_numeric_identifier, $openssl_version_parsed);
        $openssl_version = false;
        if (!empty($openssl_version_parsed)) {
            $alphabet = array(1=>'a',2=>'b',3=>'c',4=>'d',5=>'e',6=>'f',7=>'g',8=>'h',9=>'i',10=>'j',11=>'k',
                12=>'l',13=>'m',14=>'n',15=>'o',16=>'p',17=>'q',18=>'r',19=>'s',20=>'t',21=>'u',
                22=>'v',23=>'w',24=>'x',25=>'y',26=>'z');
            $openssl_version = intval($openssl_version_parsed['major'][0]).'.';
            $openssl_version.= intval($openssl_version_parsed['minor'][0]).'.';
            $openssl_version.= intval($openssl_version_parsed['fix'][0]);
            $patchlevel_dec = hexdec($openssl_version_parsed['patch'][0]);
            if (!$patch_as_number && array_key_exists($patchlevel_dec, $alphabet)) {
                $openssl_version.= $alphabet[$patchlevel_dec]; // ideal for text comparison
            }
            else {
                $openssl_version.= '.'.$patchlevel_dec; // ideal for version_compare
            }
        }
        return $openssl_version;
    }

    /**
     * @param $aConfiguration
     *
     * @return bool
     */
    public function hasMinShopVersion(&$aConfiguration)
    {
        if ($this->getDb()) {
            require_once '../vendor/oxid-esales/oxideshop-ce/source/Core/ShopVersion.php';
            $shopVersion = OxidEsales\EshopCommunity\Core\ShopVersion::getVersion();

            $oEditionResult = $this->_getShopEdition();
            $sEdition       = strtoupper($oEditionResult->oxedition);

            $mMinRemoteVersion = $this->oRemote->getMinShopVersion(
                $this->oConfig->sModId,
                $this->oConfig->sModVersion,
                $sEdition
            );

            if ($mMinRemoteVersion) {
                $aConfiguration['aParams'] = array('version' => $mMinRemoteVersion);
            } else {
                $aConfiguration['aParams'] = array('version' => $aConfiguration['aParams'][$sEdition]);
            }

            if (version_compare($shopVersion, $aConfiguration['aParams']['version'], '>=')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $aConfiguration
     *
     * @return bool
     */
    public function hasMaxShopVersion(&$aConfiguration)
    {
        if ($this->getDb()) {
            require_once '../vendor/oxid-esales/oxideshop-ce/source/Core/ShopVersion.php';
            $shopVersion = OxidEsales\EshopCommunity\Core\ShopVersion::getVersion();

            $oEditionResult = $this->_getShopEdition();
            $sEdition       = strtoupper($oEditionResult->oxedition);

            $mMaxRemoteVersion = $this->oRemote->getMaxShopVersion(
                $this->oConfig->sModId,
                $this->oConfig->sModVersion,
                $sEdition
            );

            if ($mMaxRemoteVersion) {
                $aConfiguration['aParams'] = array('version' => $mMaxRemoteVersion);
            } else {
                $aConfiguration['aParams'] = array('version' => $aConfiguration['aParams'][$sEdition]);
            }

            if (version_compare($shopVersion, $aConfiguration['aParams']['version'], '<=')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $aConfiguration
     *
     * @return bool
     */
    public function isShopEdition(&$aConfiguration)
    {
        if ($this->getDb()) {
            $oResult = $this->_getShopEdition();

            $mRemoteShopEditions = $this->oRemote->getShopEdition(
                $this->oConfig->sModId,
                $this->oConfig->sModVersion,
                $oResult->oxedition
            );

            if (is_array($mRemoteShopEditions)) {
                $aConfiguration['aParams'][0] = $mRemoteShopEditions;
            }

            if (in_array(strtoupper($oResult->oxedition), $aConfiguration['aParams'][0])) {
                $aConfiguration['aParams'][0] = strtoupper($oResult->oxedition);
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool|object|stdClass
     */
    protected function _getShopEdition()
    {
        if ($this->getDb()) {
            $sField  = 'oxedition';
            $sSelect = "SELECT " . $sField . " FROM oxshops WHERE 1 LIMIT 1";
            $rResult = mysqli_query($this->getDb(), $sSelect);
            $oResult = mysqli_fetch_object($rResult);

            return $oResult;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function hasModCfg()
    {
        if ($this->getDb()) {
            $sModId  = 'd3modcfg_lib';
            $sSelect = "SELECT 1 as result FROM d3_cfg_mod WHERE oxmodid = '" . $sModId . "' LIMIT 1";
            $rResult = mysqli_query($this->getDb(), $sSelect);
            if (is_object($rResult)) {
                $oResult = mysqli_fetch_object($rResult);

                if ((bool) $oResult->result == true) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param $aConfiguration
     *
     * @return bool|int
     */
    public function hasMinModCfgVersion(&$aConfiguration)
    {
        if ($this->getDb()) {
            $sSelect = "SELECT IF ".
                "(INET_ATON(oxversion) >= INET_ATON('" . $aConfiguration['aParams']['version'] . "'), 1, 0) AS result ".
                "FROM d3_cfg_mod ".
                "WHERE
                    oxmodid = '" . $aConfiguration['aParams']['id'] . "' AND
                    oxversion != 'basic'
                    ORDER BY oxversion ASC LIMIT 1";

            $rResult = mysqli_query($this->getDb(), $sSelect);
            $oResult = mysqli_fetch_object($rResult);
            $blReturn = (bool)$oResult->result;

            if (false == $blReturn) {
                $this->setGlobalResult(false);
            }

            return $blReturn;
        }

        $this->setGlobalResult(false);

        return false;
    }

    /**
     * @param $aConfiguration
     *
     * @return bool|int
     */
    public function hasMaxModCfgVersion(&$aConfiguration)
    {
        if ($this->getDb()) {
            $sSelect = "SELECT
                IF (INET_ATON(oxversion) <= INET_ATON('" . $aConfiguration['aParams']['version'] . "'), 1, 0) AS result
                FROM d3_cfg_mod WHERE
                oxmodid = '" . $aConfiguration['aParams']['id'] . "' AND
                oxversion != 'basic'
                ORDER BY oxversion ASC LIMIT 1";

            $rResult = mysqli_query($this->getDb(), $sSelect);
            $oResult = mysqli_fetch_object($rResult);
            $blResult = (bool)$oResult->result;

            if (false == $blResult) {
                $this->setGlobalResult(false);
            }

            return $blResult;
        }

        $this->setGlobalResult(false);

        return false;
    }

    /**
     * @param $aConfiguration
     *
     * @return bool
     */
    public function requireNewLicence(&$aConfiguration)
    {
        if ($this->getDb()) {
            $sSelect = "SELECT
                oxversion as oxversion
                FROM d3_cfg_mod WHERE
                oxmodid = '" . $this->oConfig->sModId . "'
                ORDER BY oxversion ASC LIMIT 1";

            $rResult = mysqli_query($this->getDb(), $sSelect);
            $oResult = mysqli_fetch_object($rResult);

            if (isset($oResult)
                && is_object($oResult)
                && isset($oResult->oxversion)
                && isset($aConfiguration['aParams']['checkVersion'])
            ) {
                $sInstalledVersion = $this->_getConvertedVersion(
                    $oResult->oxversion,
                    $aConfiguration['aParams']['remainingDigits']
                );
                $sNewVersion = $this->_getConvertedVersion(
                    $this->oConfig->sModVersion,
                    $aConfiguration['aParams']['remainingDigits']
                );
                if (version_compare($sInstalledVersion, $sNewVersion, '>=')) {
                    return true;
                }
            }
        }

        return 'notice';
    }

    /**
     * cut not used version digits
     * @param string $sVersion
     * @param int $iRemainingDigits
     *
     * @return string
     */
    protected function _getConvertedVersion($sVersion, $iRemainingDigits)
    {
        $aInstalledVersion = explode('.', $sVersion);
        return implode('.', array_slice($aInstalledVersion, 0, $iRemainingDigits));
    }

    /**
     * @return array
     */
    public function hasZendLoaderOptimizer(&$aConfiguration, $blCheckBasePathOnly = false)
    {
        $aResult = array($this->getBasePath() => false);

        if ((version_compare(phpversion(), '5.2.0', '>=')
            && version_compare(phpversion(), '5.2.900', '<')
            && function_exists('zend_optimizer_version')
        ) || (
            version_compare(phpversion(), '5.3.0', '>=')
            && version_compare(phpversion(), '5.6.900', '<')
            && function_exists('zend_loader_version')
        )) {
            if (function_exists('opcache_get_status')
                && ($aOpCacheStatus = opcache_get_status())
                && $aOpCacheStatus['opcache_enabled']
            ) {
                $aResult[$this->getBasePath()] = null;
            } else {
                $aResult[$this->getBasePath()] = true;
            }
        }

        if ($blCheckBasePathOnly == false) {
            $aResult = array_merge($aResult, $this->checkInSubDirs(__FUNCTION__));
        }

        return $aResult;
    }

    /**
     * @return array
     */
    public function hasIonCubeLoader(&$aConfiguration, $blCheckBasePathOnly = false)
    {
        $aResult = array($this->getBasePath() => false);

        if (function_exists('ioncube_loader_version')) {
            $aResult[$this->getBasePath()] = true;
        }

        if ($blCheckBasePathOnly == false) {
            $aResult = array_merge($aResult, $this->checkInSubDirs(__FUNCTION__));
        }

        return $aResult;
    }

    /**
     * @return array
     */
    public function hasIonCubeOrZendLoader(&$aConfiguration)
    {
        $oLayout = $this->getBase()->oLayout;

        $aDecoderTexts = array();
        $aDecoderErrorTexts = array();
        foreach (array('hasZendLoaderOptimizer', 'hasIonCubeLoader') as $sDecoderCheck) {
            $aReturn = call_user_func_array(array($this, $sDecoderCheck), array($aConfiguration, true));
            if ($aReturn[$this->getBasePath()]) {
                $aDecoderTexts[$sDecoderCheck] = $oLayout->translate('availableDecoder_'.$sDecoderCheck);
            } elseif ($aReturn[$this->getBasePath()] === null) {
                $aDecoderErrorTexts[$sDecoderCheck] = $oLayout->translate('decoderIssue_'.$sDecoderCheck);
            } else {
                $aDecoderErrorTexts[$sDecoderCheck] = $oLayout->translate('notAvailableDecoder_'.$sDecoderCheck);
            }
        }

        $sDecoderText = count($aDecoderTexts) ?
            sprintf($oLayout->translate('availableDecoder'), implode(' + ', $aDecoderTexts)) :
            $oLayout->translate('noDecoderAvailable');
        $aConfiguration['aParams'][1] = $sDecoderText;

        $sDecoderErrorText = count($aDecoderErrorTexts) ?
            '<ul>'.implode('', $aDecoderErrorTexts).'</ul>' :
            '';
        $aConfiguration['aParams'][2] = $sDecoderErrorText;

        $aZendLoaderResults = $this->hasZendLoaderOptimizer($aConfiguration);
        $aIonCubeLoaderResults = $this->hasIonCubeLoader($aConfiguration);

        $aResult = array();
        foreach (array_keys($aZendLoaderResults) as $sPath) {
            // transfer meta data
            if (strstr($sPath, '@@')) {
                $aResult[$sPath] = $aZendLoaderResults[$sPath];
            } elseif ($aIonCubeLoaderResults[$sPath] || $aZendLoaderResults[$sPath]) {
                $aResult[$sPath] = true;
            } elseif ($aIonCubeLoaderResults[$sPath] === null || $aZendLoaderResults[$sPath] === null) {
                $aResult[$sPath] = null;
            } else {
                $aResult[$sPath] = false;
            }
        }

        return $aResult;
    }
}

/**
 * Class requTransformation
 */
class requTransformation
{
    public $oCheck;

    /**
     * @param requCheck $oCheck
     */
    public function __construct(requCheck $oCheck)
    {
        $this->oCheck = $oCheck;
    }

    /**
     * @param $aCheckList
     */
    public function transformCheckList($aCheckList)
    {
        $this->_removeDeprecatedLibs($aCheckList['hasMinModCfgVersion']);
        $this->_removeDeprecatedLibs($aCheckList['hasMaxModCfgVersion']);

        return $aCheckList;
    }

    /**
     * @param array $aCheck
     */
    protected function _removeDeprecatedLibs(&$aCheck)
    {
        $blDelOldLibs = false;
        $sCheckVersion = 0;

        if (is_array($aCheck)) {
            $sSelect = "SELECT oxversion as result ".
                "FROM d3_cfg_mod ".
                "WHERE oxmodid = 'd3modcfg_lib' LIMIT 1";
            $rResult = mysqli_query($this->oCheck->getDb(), $sSelect);

            if (is_object($rResult)) {
                $oResult = mysqli_fetch_object($rResult);
                if ($oResult->result) {
                    $sCheckVersion = $oResult->result;
                }
            }

            foreach ($aCheck as $aModCfgCheck) {
                if (isset($aModCfgCheck['aParams']['id']) &&
                    strtolower($aModCfgCheck['aParams']['id']) == 'd3modcfg_lib' &&
                    version_compare($sCheckVersion, '4.0.0.0', '>=')
                ) {
                    $blDelOldLibs = true;
                }
            }

            reset($aCheck);

            if ($blDelOldLibs) {
                $aOldLibs = array('d3install_lib', 'd3log_lib', 'd3clrtmp_lib');
                foreach ($aCheck as $sKey => $aModCfgCheck) {
                    if (isset($aModCfgCheck['aParams']['id']) &&
                        in_array(strtolower($aModCfgCheck['aParams']['id']), $aOldLibs)
                    ) {
                        unset($aCheck[$sKey]);
                    }
                }
            }
        }
    }
}

/**
 * @param $mVar
 */
function dumpvar($mVar)
{
    echo "<pre>";
    print_r($mVar);
    echo "</pre>";
}

$oRequCheck = new requcheck;
if (isset($_REQUEST['fnc']) && $_REQUEST['fnc']) {
    $oRequCheck->{$_REQUEST['fnc']}();
} else {
    $oRequCheck->startCheck();
}
