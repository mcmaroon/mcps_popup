<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

class mcps_popup extends Module
{

    use \MCPS\Helper\Configuration\ConfigurationFormTrait;

    const MODULE_DB_PREFIX = 'mcps_';

    public function __construct()
    {
        $this->name = basename(dirname(__FILE__));
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Marek Ciarkowski';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('popup for PrestaShop');
        $this->description = $this->l('Description of my module.');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.6.99.99');
    }

    public function install()
    {
        $this->setConfig($this->getDefaultConfig());

        return
            parent::install() &&
            $this->registerHook('displayFooter') &&
            $this->registerHook('header')
        ;
    }

    public function uninstall()
    {
        Configuration::deleteByName(\strtoupper($this->name));
        return parent::uninstall();
    }

    private function getDefaultConfig()
    {
        return array(
            'useModuleCoreCss' => true,
            'useModuleCoreJs' => true,
            'visibility' => true,
            'pages' => '',
            'title' => '',
            'body' => '',
        );
    }    

    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = $this->getSubmitAction();
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfig(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    public function hookHeader()
    {
        $config = $this->getConfig();
        if ($config['useModuleCoreJs']) {
            $this->context->controller->addJS($this->_path . '/views/js/front.js');
        }
        if ($config['useModuleCoreCss']) {
            $this->context->controller->addCSS($this->_path . '/views/css/front.css');
        }
    }

    public function hookDisplayFooter($params)
    {
        $config = $this->getConfig();
        $visibility = $config['visibility'];
        $pages = \preg_split('/(\r\n?|\n)/', $config['pages']);
        $hasMatch = false;
        $body_classes = array();
        $smarty = $this->context->smarty;
        if (isset($smarty->tpl_vars) && isset($smarty->tpl_vars['body_classes']) && $smarty->tpl_vars['body_classes'] instanceof \Smarty_Variable) {
            $body_classes = (array) $smarty->tpl_vars['body_classes']->value;
        } else {
            \array_push($body_classes, 'default');
        }

        if (\count($body_classes)) {
            foreach ($pages as $page) {
                if (\is_numeric(\array_search(\trim($page), $body_classes))) {
                    $hasMatch = true;
                    break;
                }
            }
        }

        if (!$this->isCached('views/templates/front/popup.tpl', $this->getCacheId())) {
            $this->smarty->assign('config', $config);
        }

        if (($visibility === true && $hasMatch === true) || ($visibility === false && $hasMatch === false)) {
            return $this->display(__FILE__, 'views/templates/front/popup.tpl', $this->getCacheId());
        }


        return null;
    }
}
