<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

$mcpsCoreAutoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($mcpsCoreAutoloadPath)) {
    require_once __DIR__ . '/vendor/autoload.php';
}

class mcps_popup extends Module
{

    use \MCPS\Popup\Helper\Configuration\ConfigurationFormTrait;

    public function __construct()
    {
        $this->name = static::class;
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Marek Ciarkowski (WebPlanner)';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Popup for PrestaShop');
        $this->description = $this->l('Displays pop-ups on selected pages of the site.');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        return
            parent::install() &&
            $this->registerHook('displayFooter') &&
            $this->registerHook('header');
    }

    public function uninstall()
    {
        Configuration::deleteByName(strtoupper($this->name));
        return parent::uninstall();
    }

    public function setConfigurationForm()
    {
        $pages = [
            'default', 'category-3', 'category-5', // ps 1.6.x
            'page-index', 'category-id-3', 'category-id-5', // ps 1.7.x
        ];

        $this->addConfigurationBoolean('debugMode', false, $this->l('Debug Mode'));
        $this->addConfigurationBoolean('useModuleCoreCss', true, $this->l('Use Module Css'));
        $this->addConfigurationBoolean('useModuleCoreJs', true, $this->l('Use Module Js'));
        $this->addConfigurationText('dateStart', date('Y-m-d H:i', strtotime('now')), $this->l('Display from'), $this->l('Starting date of display in a format compatible with php strtotime documentation.'));
        $this->addConfigurationText('dateEnd', date('Y-m-d H:i', strtotime('+1 week')), $this->l('Display to'), $this->l('Date of the end of the display in a format compatible with php strtotime documentation.'));
        $this->addConfigurationBoolean('visibility', true, $this->l('Visibility'), $this->l('Display on pages from the list or on all other pages except those listed.'));
        $this->addConfigurationText('pages', implode(',', $pages), $this->l('Pages'), $this->l('Pages (body class) on which a popup should appear. The separator of consecutive entries is a comma sign. "default" = homepage ex: category-3'));
        $this->addConfigurationText('title', [], $this->l('Title'), null, [], true);
        $this->addConfigurationTextArea('body', '', $this->l('Body'), null, [], true);
        $this->addConfigurationBoolean('displayReturnToSiteBtn', true, $this->l('Display return to site button'));
    }

    public function hookHeader()
    {
        $config = $this->getConfig();
        if ($config['useModuleCoreCss']) {
            $this->context->controller->addCSS($this->_path . '/views/css/front.css');
        }
    }

    public function hookDisplayFooter($params)
    {
        $config = $this->getConfig();
        if (strlen($config['dateStart']) && strtotime($config['dateStart']) && (strtotime($config['dateStart']) > strtotime('now'))) {
            return null;
        }
        if (strlen($config['dateEnd']) && strtotime($config['dateEnd']) && (strtotime($config['dateEnd']) < strtotime('now'))) {
            return null;
        }

        $visibility = $config['visibility'];
        $pagesClearString = filter_var($config['pages'], FILTER_SANITIZE_STRING);
        $pages = explode(',', $pagesClearString);
        $hasMatch = false;
        $matchIndex = null;
        $body_classes = array();
        $smarty = $this->context->smarty;
        // ps 1.6.x
        if (isset($smarty->tpl_vars) && isset($smarty->tpl_vars['body_classes']) && $smarty->tpl_vars['body_classes'] instanceof \Smarty_Variable) {
            $body_classes = (array)$smarty->tpl_vars['body_classes']->value;
        }
        // ps 1.7.x
        if (isset($smarty->tpl_vars) && isset($smarty->tpl_vars['page']) && $smarty->tpl_vars['page'] instanceof \Smarty_Variable && isset($smarty->tpl_vars['page']->value['body_classes'])) {
            $body_classes = (array)array_keys($smarty->tpl_vars['page']->value['body_classes']);
        }

        // Default value for index page if empty. Ps 1.6.x
        if (!count($body_classes)) {
            array_push($body_classes, 'default');
        }

        foreach ($pages as $page) {
            if (is_numeric($searchIndex = array_search(trim($page), $body_classes))) {
                $hasMatch = true;
                $matchIndex = $searchIndex;
                break;
            }
        }

        if (method_exists($this, 'fetch')) {
            $templateFile = 'module:' . $this->name . '/views/templates/front/popup.tpl'; // ps 1.7.x
        } else {
            $templateFile = 'views/templates/front/popup.tpl'; // ps 1.6.x
        }

        if (!$this->isCached($templateFile, $this->getCacheId($this->name))) {
            $this->smarty->assign('id_language', $this->context->language->id);
            $this->smarty->assign('config', $config);
        }

        if ($config['debugMode'] === true) {
            $this->smarty->assign('body_classes', $body_classes);
            $this->smarty->assign('matchIndex', $matchIndex);
        }

        if (($visibility === true && $hasMatch === true) || ($visibility === false && $hasMatch === false) || $config['debugMode'] === true) {
            if (method_exists($this, 'fetch')) {
                return $this->fetch($templateFile, $this->getCacheId($this->name)); // ps 1.7.x
            } else {
                return $this->display(__FILE__, $templateFile, $this->getCacheId($this->name)); // ps 1.6.x
            }
        }

        return null;
    }
}
