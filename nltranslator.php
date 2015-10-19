<?php
/**
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2015 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class Nltranslator extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'nltranslator';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Michael Dekker';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Fix Dutch translations');
        $this->description = $this->l('Use this module to fix translations for the Netherlands and Belgium.');
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        return parent::install();
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $output = '';
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitNltranslatorModule')) == true) {
            if (!Language::getIdByIso('nl')) {
                $output .= $this->displayError('Dutch translations have not been installed');
            } else {
                $output .= $this->postProcess();
            }
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        if (version_compare(_PS_VERSION_, '1.6', '>=')) {
            $output .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
        } else {
            $output .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure15.tpl');
        }

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitNltranslatorModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(
            array($this->getTaxesForm(),
                $this->getTabsForm(),
                $this->getCountriesForm(),
                $this->getPlaceholdersForm()
            )
        );
    }

    /**
     * Tax rules form.
     */
    protected function getTaxesForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Fix Dutch or Belgian tax rules'),
                'icon' => 'icon-money',
                ),
                'input' => array(
                    array(
                        'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                        'class' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? null : 't',
                        'is_bool' => true,
                        'label' => $this->l('I understand that tax rules for the Netherlands can be overwritten...'),
                        'name' => 'updateTaxRules',
                        'values' => array(
                            array(
                                'id' => 'updateTaxRules_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'updateTaxRules_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        )
                    ),
                    array(
                        'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                        'class' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? null : 't',
                        'is_bool' => true,
                        'label' => $this->l('I understand that tax rules for Belgium can be overwritten...'),
                        'name' => 'updateTaxRulesBelgium',
                        'values' => array(
                            array(
                                'id' => 'updateTaxRulesBelgium_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'updateTaxRulesBelgium_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        )
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Fix'),
                    'icon' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'process-icon-cogs' : '',
                    'class' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? null : 'button',
                ),
            ),
        );
    }

    /**
     * Tabs form.
     */
    protected function getTabsForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Fix Dutch tab names'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                        'class' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? null : 't',
                        'is_bool' => true,
                        'label' => $this->l('I understand that all current tab translations will be overwritten...'),
                        'name' => 'translateTabs',
                        'values' => array(
                            array(
                                'id' => 'translateTabs_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'translateTabs_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        )
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Fix'),
                    'icon' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'process-icon-cogs' : '',
                    'class' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? null : 'button',
                ),
            ),
        );
    }

    /**
     * Countries form.
     */
    protected function getCountriesForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Translate country names into Dutch'),
                    'icon' => 'icon-flag',
                ),
                'input' => array(
                    array(
                        'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                        'class' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? '' : 't',
                        'is_bool' => true,
                        'label' => $this->l('I would like to translate the country names into Dutch'),
                        'name' => 'translateCountries',
                        'values' => array(
                            array(
                                'id' => 'translateCountries_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'translateCountries_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        )
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Translate'),
                    'icon' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'process-icon-flag' : '',
                    'class' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? null : 'button',
                ),
            ),
        );
    }

    /**
     * Placeholders form.
     */
    protected function getPlaceholdersForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Translate placeholder images'),
                    'icon' => 'icon-picture-o',
                ),
                'input' => array(
                    array(
                        'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                        'class' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? '' : 't',
                        'is_bool' => true,
                        'label' => $this->l('I would like to install the Dutch placeholder images'),
                        'name' => 'translatePlaceholders',
                        'desc' => $this->l('Before: ').'No image available, '.
                            $this->l('after: ').'Geen afbeelding beschikbaar.',
                        'values' => array(
                            array(
                                'id' => 'translatePlaceholders_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'translatePlaceholders_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        )
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Install'),
                    'icon' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'process-icon-plus' : '',
                    'class' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? null : 'button',
                ),
            ),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $output = '';
        if (Tools::isSubmit('updateTaxRules') && Tools::getValue('updateTaxRules') == '1') {
            $output .= $this->installTaxes();
        }
        if (Tools::isSubmit('updateTaxRulesBelgium') && Tools::getValue('updateTaxRulesBelgium') == '1') {
            $output .= $this->installTaxes('be');
        }
        if (Tools::isSubmit('translateCountries')&& Tools::getValue('translateCountries') == '1') {
            $output .= $this->translateCountries();
        }
        if (Tools::isSubmit('translatePlaceholders')&& Tools::getValue('translatePlaceholders') == '1') {
            $output .= $this->translatePlaceholders();
        }
        if (Tools::isSubmit('translateTabs')&& Tools::getValue('translateTabs') == '1') {
            $output .= $this->translateTabs();
        }
        return $output;
    }

    /**
     * @return bool
     * @throws PrestaShopException
     */
    protected function installTaxes($iso_country = '')
    {
        $path = _PS_MODULE_DIR_.$this->name.'/localization/taxes'.$iso_country.'.xml';

        if (!($pack = @Tools::file_get_contents($path))) {
            return $this->displayError($this->l('Cannot load the localization pack'));
        }
        if (!$xml = @simplexml_load_string($pack)) {
            return $this->displayError($this->l('Cannot load the localization pack'));
        }
        libxml_clear_errors();

        if (isset($xml->taxes->tax)) {
            $assoc_taxes = array();
            foreach ($xml->taxes->tax as $taxData) {
                /** @var SimpleXMLElement $taxData */
                $attributes = $taxData->attributes();
                if (($id_tax = Tax::getTaxIdByName($attributes['name']))) {
                    $assoc_taxes[(int)$attributes['id']] = $id_tax;
                    continue;
                }
                $tax = new Tax();
                $tax->name[(int)Configuration::get('PS_LANG_DEFAULT')] = (string)$attributes['name'];
                $tax->rate = (float)$attributes['rate'];
                $tax->active = 1;

                if (($error = $tax->validateFields(false, true)) !== true ||
                    ($error = $tax->validateFieldsLang(false, true)) !== true) {
                    return $this->displayError($this->l('Invalid tax properties')).' '.$error;
                }

                if (!$tax->add()) {
                    return $this->displayError($this->l('An error occurred while importing the tax: ')).
                    (string)$attributes['name'];
                }

                $assoc_taxes[(int)$attributes['id']] = $tax->id;
            }

            foreach ($xml->taxes->taxRulesGroup as $group) {
                if ($group_id = TaxRulesGroup::getIdByName($group['name'])) {
                    TaxRule::deleteByGroupId($group_id);
                    $trg = new TaxRulesGroup($group_id);
                    $trg->update();
                } else {
                    $trg = new TaxRulesGroup();
                    $trg->name = $group['name'];
                    $trg->active = 1;
                    if (!$trg->save()) {
                        return $this->displayError($this->l('This tax rule cannot be saved'));
                    }
                }

                foreach ($group->taxRule as $rule) {
                    /** @var SimpleXMLElement $rule */
                    $rule_attributes = $rule->attributes();

                    // Validation
                    if (!isset($rule_attributes['iso_code_country'])) {
                        continue;
                    }

                    $id_country = (int)Country::getByIso(Tools::strtoupper($rule_attributes['iso_code_country']));
                    if (!$id_country) {
                        continue;
                    }

                    if (!isset($rule_attributes['id_tax']) ||
                        !array_key_exists((string)$rule_attributes['id_tax'], $assoc_taxes)) {
                        continue;
                    }

                    // Default values
                    $id_state = (int)isset($rule_attributes['iso_code_state'])
                        ? State::getIdByIso(Tools::strtoupper($rule_attributes['iso_code_state']))
                        : 0;
                    $id_county = 0;
                    $zipcode_from = 0;
                    $zipcode_to = 0;
                    $behavior = $rule_attributes['behavior'];

                    if (isset($rule_attributes['zipcode_from'])) {
                        $zipcode_from = $rule_attributes['zipcode_from'];
                        if (isset($rule_attributes['zipcode_to'])) {
                            $zipcode_to = $rule_attributes['zipcode_to'];
                        }
                    }

                    // Creation
                    $tr = new TaxRule();
                    $tr->id_tax_rules_group = $trg->id;
                    $tr->id_country = $id_country;
                    $tr->id_state = $id_state;
                    $tr->id_county = $id_county;
                    $tr->zipcode_from = $zipcode_from;
                    $tr->zipcode_to = $zipcode_to;
                    $tr->behavior = $behavior;
                    $tr->description = '';
                    $tr->id_tax = $assoc_taxes[(string)($rule_attributes['id_tax'])];
                    $tr->save();
                }
            }
        }
        return $this->displayConfirmation($this->l('Tax rules have been installed'));
    }

    /**
     * Translate tabs
     *
     * @return string
     */
    protected function translateTabs()
    {
        $filename = _PS_MODULE_DIR_.$this->name.DIRECTORY_SEPARATOR.'localization/tabs.php';
        $_TABS = array();
        $errors = array();
        clearstatcache();
        if (file_exists($filename)) {
            include_once($filename);
        }
        if (is_array($_TABS) && count($_TABS)) {
            foreach ($_TABS as $class_name => $translations) {
                $tab = Tab::getInstanceFromClassName($class_name);
                if (isset($tab->class_name) && !empty($tab->class_name)) {
                    $id_lang = Language::getIdByIso('nl', true);
                    $tab->name[(int)$id_lang] = $translations;
                    if (!isset($tab->name[Configuration::get('PS_LANG_DEFAULT')])) {
                        $tab->name[(int)Configuration::get('PS_LANG_DEFAULT')] = $translations;
                    }
                    if (!Validate::isGenericName($tab->name[(int)$id_lang])) {
                        $errors[] = sprintf($this->displayError('Tab "%s" is not valid'), $tab->name[(int)$id_lang]);
                    } else {
                        $tab->update();
                    }
                }
            }
        }
        return $this->displayConfirmation($this->l('Tabs have been translated'));
    }

    /**
     * Translate placeholders
     */
    protected function translatePlaceholders()
    {
        $this->deleteNoPictureImages('nl');
        return $this->copyNoPictureImage('nl');
    }

    protected function translateCountries()
    {
        $countries_xml = Tools::file_get_contents(_PS_MODULE_DIR_.$this->name.'/localization/country.xml');

        if (!$xml = @simplexml_load_string($countries_xml)) {
            return $this->displayError($this->l('Cannot load the countries pack'));
        }
        libxml_clear_errors();

        foreach ($xml as $xml_country) {
            $iso_country = (string)$xml_country->attributes()->{'id'};
            $country_name = (string)$xml_country->name;
            $country = new Country(Country::getByIso($iso_country));
            if (empty($country)) {
                break;
            }

            $country->name[(int)Language::getIdByIso('nl')] = $country_name;
            $country->update();
        }

        return $this->displayConfirmation($this->l('Country names have been translated'));
    }

    /**
     * Copy a no-product image
     *
     * @param string $language Language iso_code for no_picture image filename
     *
     * @return void|false
     */
    public function copyNoPictureImage($language)
    {
        $errors = '';
        $tmp_name = _PS_MODULE_DIR_.$this->name.DIRECTORY_SEPARATOR.'views/img/'.
            'placeholders'.DIRECTORY_SEPARATOR.$language.'.jpg';
        if (!ImageManager::resize($tmp_name, _PS_IMG_DIR_.'p/'.$language.'.jpg')) {
            $errors .= $this->displayError(
                'An error occurred while copying "No Picture" image to your product folder.'
            );
        }
        if (!ImageManager::resize($tmp_name, _PS_IMG_DIR_.'c/'.$language.'.jpg')) {
            $errors = $this->displayError(
                'An error occurred while copying "No picture" image to your category folder.'
            );
        }
        if (!ImageManager::resize($tmp_name, _PS_IMG_DIR_.'m/'.$language.'.jpg')) {
            $errors = $this->displayError(
                'An error occurred while copying "No picture" image to your manufacturer folder.'
            );
        } else {
            $images_types = ImageType::getImagesTypes('products');
            foreach ($images_types as $image_type) {
                if (!ImageManager::resize(
                    $tmp_name,
                    _PS_IMG_DIR_.'p/'.$language.'-default-'.Tools::stripslashes($image_type['name']).'.jpg',
                    $image_type['width'],
                    $image_type['height']
                )) {
                    $this->errors[] = $this->displayError(
                        'An error occurred while resizing "No picture" image to your product directory.'
                    );
                }
                if (!ImageManager::resize(
                    $tmp_name,
                    _PS_IMG_DIR_.'c/'.$language.'-default-'.Tools::stripslashes($image_type['name']).'.jpg',
                    $image_type['width'],
                    $image_type['height']
                )) {
                    $this->errors[] = $this->displayError(
                        'An error occurred while resizing "No picture" image to your category directory.'
                    );
                }
                if (!ImageManager::resize(
                    $tmp_name,
                    _PS_IMG_DIR_.'m/'.$language.'-default-'.Tools::stripslashes($image_type['name']).'.jpg',
                    $image_type['width'],
                    $image_type['height']
                )) {
                    $this->errors[] = $this->displayError(
                        'An error occurred while resizing "No picture" image to your manufacturer directory.'
                    );
                }
            }
        }
        if (empty($errors)) {
            return $this->displayConfirmation($this->l('Placeholder images have been installed'));
        }
        return $errors;
    }

    /**
     * deleteNoPictureImages will delete all default image created for the language
     *
     * @param string $language iso_code
     * @return bool true if no error
     */
    protected function deleteNoPictureImages($language)
    {
        $images_types = ImageType::getImagesTypes('products');
        $dirs = array(_PS_PROD_IMG_DIR_, _PS_CAT_IMG_DIR_, _PS_MANU_IMG_DIR_, _PS_SUPP_IMG_DIR_, _PS_MANU_IMG_DIR_);
        foreach ($dirs as $dir) {
            foreach ($images_types as $image_type) {
                if (file_exists($dir.$language.'-default-'.Tools::stripslashes($image_type['name']).'.jpg')) {
                    if (!unlink($dir.$language.'-default-'.Tools::stripslashes($image_type['name']).'.jpg')) {
                        $this->errors[] = $this->displayError('An error occurred during image deletion process.');
                    }
                }
            }
            if (file_exists($dir.$language.'.jpg')) {
                if (!unlink($dir.$language.'.jpg')) {
                    $this->errors[] = $this->displayError('An error occurred during image deletion process.');
                }
            }
        }
        return !count($this->errors) ? true : false;
    }
}
