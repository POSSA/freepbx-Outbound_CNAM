<?php

namespace FreePBX\modules;

use BMO;
use PDO;
use Exception;

class Outcnam implements BMO
{
    public $FreePBX = null;

    public function __construct($freepbx = null)
    {
        if ($freepbx == null) {
            throw new Exception("Not given a FreePBX Object");
        }
        $this->FreePBX = $freepbx;
        $this->Database = $freepbx->Database;
    }

    /**
     * Installer run on fwconsole ma install
     *
     * @return void
     */
    public function install()
    {
    }

    /**
     * Uninstaller run on fwconsole ma uninstall
     *
     * @return void
     */
    public function uninstall()
    {
    }

    /**
     * Processes form submission and pre-page actions.
     *
     * @param string $page Display name
     * @return void
     */
    public function doConfigPageInit($page)
    {
        if ($_POST['action'] == 'edit') {
            $this->editConfig(1, $_POST);
        };
    }

    /**
     * Adds buttons to the bottom of pages per set conditions
     *
     * @param array $request $_REQUEST
     * @return void
     */
    public function getActionBar($request)
    {
        if ('outcnam' == $request['display']) {
            $buttons = [

                'reset' => [
                    'name' => 'reset',
                    'id' => 'reset',
                    'value' => _("Reset")
                ],
                'submit' => [
                    'name' => 'submit',
                    'id' => 'submit',
                    'value' => _("Submit")
                ]
            ];

            return $buttons;
        }
    }



    /**
     * Do we want to add to the dialplan?
     * https://wiki.freepbx.org/display/FOP/BMO+Hooks#BMOHooks-DialplanHooks
     *
     * @return bool or int 500 priority
     */
    public function myDialplanHooks()
    {
        return true;
    }

    /**
     * Dialplan generation
     *
     * @param object $ext The dialplan object we add to
     * @param string $engine This will always be asterisk
     * @param int $priority 500?
     * @return void
     */
    public function doDialplanHook(&$ext, $engine, $priority)
    {
        $configs = $this->getAllConfigurations();
        $context = "macro-dialout-trunk";
        $exten = "s";
        $webroot = $this->FreePBX->Config->get('AMPWEBROOT');
		// the dial macro will only exist if there is at least one outroute defined with a trunk
		// just checking for the existence of routes and trunks is not sufficient
        $routes = $this->FreePBX->Core->getAllRoutes();
		foreach ($routes as $route) {  
			if (!empty($this->FreePBX->Core->getRouteTrunksByID($route['route_id']))) {
				$dial_macro_exists = true;
			}
		}
		if ($dial_macro_exists) {
            $spice_position = -4;       // -4 is arbitrary
            foreach ($configs as $config) {
                if ($config['enable_cdr'] == 'CHECKED' || $config['enable_rpid'] == 'CHECKED') {
                    $ext->splice($context, $exten, 'customtrunk', new \ext_setvar('CIDSFSCHEME', base64_encode($config['scheme'])), "", $spice_position);
                    $ext->splice($context, $exten, 'customtrunk', new \ext_setvar('temp1', '${CALLERID(name)}'), "", $spice_position);
                    $ext->splice($context, $exten, 'customtrunk', new \ext_setvar('CALLERID(name)', ''), "", $spice_position);
                    $ext->splice($context, $exten, 'customtrunk', new \ext_setvar('temp2', '${CALLERID(number)}'), "", $spice_position);
                    $ext->splice($context, $exten, 'customtrunk', new \ext_setvar('CALLERID(number)', '${DIAL_NUMBER}'), "", $spice_position);
                    $ext->splice($context, $exten, 'customtrunk', new \ext_agi($webroot . '/admin/modules/superfecta/agi/superfecta.agi'), "", $spice_position);
                    $ext->splice($context, $exten, 'customtrunk', new \ext_setvar('CALLERID(name)', '${temp1}'), "", $spice_position);
                    $ext->splice($context, $exten, 'customtrunk', new \ext_setvar('CALLERID(number)', '${temp2}'), "", $spice_position);
                }
                if ($config['enable_cdr'] == 'CHECKED') {
                    $ext->splice($context, $exten, 'customtrunk', new \ext_setvar('CDR(userfield,r)', '${lookupcid}'), "", $spice_position);
                }
                if ($config['enable_rpid'] == 'CHECKED') {
                    $ext->splice($context, $exten, 'customtrunk', new \ext_setvar('CONNECTEDLINE(name,i)', '${lookupcid}'), "", $spice_position);
                }
            }
        }
    }


    /**
     * Get Configuration by id
     *
     * @param int $id
     * @return array
     */
    public function getConfiguration($id)
    {
        $sql = 'SELECT * FROM outcnam_config WHERE outcnam_index = :id';
        $sth = $this->Database->prepare($sql);
        $sth->execute(['id' => $id]);
        $results = $sth->fetch(PDO::FETCH_ASSOC);
        return is_array($results) ? $results : ['outcnam_index' => $id, 'enable_cdr' => '', 'enable_rpid' => '', 'scheme' => ''];
    }

    /**
     * Get all configurations
     * @return array
     */
    public function getAllConfigurations()
    {
        $sql = 'SELECT * FROM outcnam_config';
        $sth = $this->Database->prepare($sql);
        $sth->execute();
        $results = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }

    /**
     * Edit Configuration
     *
     * @param int $id
     * @param array $data
     */
    public function editConfig($id, $data)
    {
        $sql = 'INSERT INTO outcnam_config (outcnam_index, scheme, enable_cdr, enable_rpid) VALUES (:id, :scheme, :enable_cdr, :enable_rpid) ON DUPLICATE KEY UPDATE scheme = :scheme, enable_cdr = :enable_cdr, enable_rpid = :enable_rpid';

        $sth = $this->Database->prepare($sql);
        $ret = $sth->execute([
            ':enable_cdr' => $data['enable_cdr'],
            ':enable_rpid' => $data['enable_rpid'],
            ':scheme' => $data['scheme'],
            ':id' => $id
        ]);

        needreload();
        return $ret;
    }

    /**
     * Check Online version vs. Local version
     * @return bool
     */
    public function onlineVersionCheck()
    {
        $onlineXMLFile = 'https://raw.github.com/POSSA/freepbx-Outbound_CNAM/master/module.xml';
        $localXMLFile = __DIR__ . '/module.xml';
        $onlineXML = file_get_contents($onlineXMLFile);
        $localXML = file_get_contents($localXMLFile);
        $onlineXML = simplexml_load_string($onlineXML);
        $localXML = simplexml_load_string($localXML);
        $onlineVersion = (string)$onlineXML->version;
        $localVersion = (string)$localXML->version;

        return version_compare($onlineVersion, $localVersion, '>');
    }

    /**
     * This returns html to the main page
     *
     * @return string html
     */
    public function showPage()
    {
        $vars = $this->getConfiguration(1);
        $vars['updateNotice'] = $this->onlineVersionCheck() ? "<br>A <b>new version of this module is available</b> from the <a target='_blank' href='http://pbxossa.org'>PBX Open Source Software Alliance</a><br>" : '';
        $schemes = $this->FreePBX->Superfecta->getAllSchemes();
        $schemeOptions = ['<option value="ALL|ALL">ALL</option>'];
        foreach ($schemes as $scheme) {
            $selected = $vars['scheme'] == $scheme['scheme'] ? 'SELECTED' : '';
            $schemeOptions[] = sprintf('<option value="%s" %s>%s</option>', $scheme['scheme'], $selected, $scheme['name']);
        }
        $vars['schemeOptions'] = implode(PHP_EOL, $schemeOptions);

        return load_view(__DIR__ . '/views/main.php', $vars);
    }
}
