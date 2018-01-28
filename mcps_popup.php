<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

class mcps_popup extends Module
{

    use \MCPS\Helper\Configuration\ConfigurationFormTrait;

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

    public function setConfigurationForm()
    {
        $values = [
            [
                'id' => 'active_on',
                'value' => true,
                'label' => $this->l('Enabled')
            ],
            [
                'id' => 'active_off',
                'value' => false,
                'label' => $this->l('Disabled')
            ]
        ];
        $this->addConfigurationFormElement('switch', 'useModuleCoreCss', true, $this->l('Use Module Css'), null, $values);
        $this->addConfigurationFormElement('switch', 'useModuleCoreJs', true, $this->l('Use Module Js'), null, $values);
        $this->addConfigurationFormElement('text', 'dateStart', \date('Y-m-d', \strtotime('now')), $this->l('Display from'), $this->l('Starting date of display in a format compatible with php strtotime documentation.'));
        $this->addConfigurationFormElement('text', 'dateEnd', \date('Y-m-d H:i', \strtotime('+1 week')), $this->l('Display to'), $this->l('Data zakończenia wyświetlania w formacie zgodnym z dokumentacją php strtotime.'));
        $this->addConfigurationFormElement('switch', 'visibility', true, $this->l('Visibility'), $this->l('Display on these pages from the list or on all other pages except those listed.'), $values);
        $this->addConfigurationFormElement('textarea', 'pages', 'default', $this->l('Pages'), $this->l('Pages (body class) on which a popup should appear. The separator of subsequent entries is a new line character. "default" = homepage ex: category-3'));
        $this->addConfigurationFormElement('text', 'title', [], $this->l('Title'), null, [], true);
        $this->addConfigurationFormElement('textarea', 'body', '', $this->l('Body'), null, [], true);
        $this->addConfigurationFormElement('switch', 'displayReturnToSiteBtn', true, $this->l('Display return to site button'), null, $values);
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
        if (strlen($config['dateStart']) && \strtotime($config['dateStart']) && (\strtotime($config['dateStart']) > \strtotime('now'))) {
            return null;
        }
        if (strlen($config['dateEnd']) && \strtotime($config['dateEnd']) && (\strtotime($config['dateEnd']) < \strtotime('now'))) {
            return null;
        }

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
            $this->smarty->assign('id_language', $this->context->language->id);
            $this->smarty->assign('config', $config);
        }

        if (($visibility === true && $hasMatch === true) || ($visibility === false && $hasMatch === false)) {
            return $this->display(__FILE__, 'views/templates/front/popup.tpl', $this->getCacheId());
        }


        return null;
    }
}
